<?php

namespace App\Controllers;

use App\Models\BahanBakuModel;
use App\Models\StokGudangModel;
use App\Models\SupplierModel;

class Stok extends BaseController
{
    protected BahanBakuModel $bahanModel;
    protected StokGudangModel $stokModel;
    protected SupplierModel $supplierModel;

    public function __construct()
    {
        $this->bahanModel = new BahanBakuModel();
        $this->stokModel = new StokGudangModel();
        $this->supplierModel = new SupplierModel();
    }

    /**
     * Display inventory list, filters (search, kategori, status_kritis), summaries (total item, kritis, total value).
     */
    public function index()
    {
        $search = $this->request->getGet('search');
        $kategori = $this->request->getGet('kategori');
        $statusKritis = $this->request->getGet('status_kritis');

        $rawBahan = $this->bahanModel->getWithStok();
        $stokList = [];
        $totalItem = 0;
        $kritisCount = 0;
        $nilaiTotal = 0;

        foreach ($rawBahan as $item) {
            $stok = (float) $item['stok_saat_ini'];
            $min = (float) $item['stok_minimum'];
            $harga = (float) $item['harga_per_satuan'];
            $status = ($stok < $min) ? 'kritis' : 'normal';

            $mapped = [
                'id' => $item['id'],
                'kode' => $item['kode'],
                'nama_bahan' => $item['nama'],
                'nama' => $item['nama'],
                'kategori' => $item['kategori'],
                'stok_saat_ini' => $stok,
                'satuan' => $item['satuan'],
                'min_stok' => $min,
                'stok_minimum' => $min,
                'status' => $status,
                'nama_supplier' => $item['supplier_name'] ?? '-',
                'harga_per_unit' => $harga,
                'harga_per_satuan' => $harga,
            ];

            $totalItem++;
            if ($status === 'kritis') {
                $kritisCount++;
            }
            $nilaiTotal += ($stok * $harga);

            // Server-side filters (supports requests with get query params)
            if ($search !== null && $search !== '') {
                if (stripos($item['nama'], $search) === false && stripos($item['kode'], $search) === false) {
                    continue;
                }
            }
            if ($kategori !== null && $kategori !== '') {
                if ($item['kategori'] !== $kategori) {
                    continue;
                }
            }
            if ($statusKritis === '1' && $status !== 'kritis') {
                continue;
            }

            $stokList[] = $mapped;
        }

        return view('stok/index', [
            'title' => 'Stok Gudang',
            'stokList' => $stokList,
            'summary' => [
                'total_item' => $totalItem,
                'kritis' => $kritisCount,
                'nilai_total' => $nilaiTotal,
            ],
            'filters' => [
                'search' => $search,
                'kategori' => $kategori,
                'status_kritis' => $statusKritis,
            ]
        ]);
    }

    /**
     * Form to record stock movement (select bahan_baku, qty, tipe: masuk/keluar, keterangan).
     */
    public function create()
    {
        $rawBahan = $this->bahanModel->where('is_active', 1)->findAll();
        $bahanList = [];
        foreach ($rawBahan as $b) {
            $bahanList[] = [
                'id' => $b['id'],
                'kode' => $b['kode'],
                'nama_bahan' => $b['nama'],
                'satuan' => $b['satuan']
            ];
        }

        $rawSupplier = $this->supplierModel->findAll();
        $supplierList = [];
        foreach ($rawSupplier as $s) {
            $supplierList[] = [
                'id' => $s['id'],
                'nama' => $s['name']
            ];
        }

        return view('stok/create', [
            'title' => 'Tambah Pergerakan Stok',
            'bahanList' => $bahanList,
            'supplierList' => $supplierList,
        ]);
    }

    /**
     * Process movement. It must add/subtract stock from stok_gudang table AND update the current balance.
     */
    public function store()
    {
        $rules = [
            'jenis'    => 'required|in_list[masuk,keluar]',
            'bahan_id' => 'required|integer',
            'qty'      => 'required|numeric|greater_than[0]',
            'tanggal'  => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $jenis = $this->request->getPost('jenis');
        $bahanBakuId = $this->request->getPost('bahan_id');
        $qty = (float) $this->request->getPost('qty');
        $tanggal = $this->request->getPost('tanggal');
        $noRef = $this->request->getPost('no_referensi');
        $supplierId = $this->request->getPost('supplier_id');
        $keteranganInput = $this->request->getPost('keterangan');

        $bahan = $this->bahanModel->find($bahanBakuId);
        if (!$bahan) {
            return redirect()->back()->withInput()->with('error', 'Bahan baku tidak ditemukan.');
        }

        // Calculate movement qty
        $stokMasuk = 0;
        $stokKeluar = 0;
        if ($jenis === 'masuk') {
            $stokMasuk = $qty;
        } else {
            $stokKeluar = $qty;
        }

        // Get current stock before this transaction
        $currentStok = $this->stokModel->getStokSaatIni($bahanBakuId);
        $newStokSaatIni = $currentStok + $stokMasuk - $stokKeluar;

        // Check if there is enough stock for outflow
        if ($jenis === 'keluar' && $newStokSaatIni < 0) {
            return redirect()->back()->withInput()->with('error', 'Stok tidak mencukupi untuk melakukan pengeluaran. Stok saat ini: ' . $currentStok . ' ' . $bahan['satuan']);
        }

        // Format keterangan
        $keterangan = '';
        if (!empty($noRef)) {
            $keterangan .= '[Ref: ' . $noRef . '] ';
        }
        $keterangan .= $keteranganInput;

        $movementData = [
            'bahan_baku_id' => $bahanBakuId,
            'stok_saat_ini' => $newStokSaatIni,
            'stok_masuk'    => $stokMasuk,
            'stok_keluar'   => $stokKeluar,
            'tanggal'       => $tanggal,
            'keterangan'    => $keterangan,
            'created_by'    => session()->get('user_id') ?: 1,
        ];

        // Also if it is a stock in and supplier is selected, update supplier_id in bahan_baku
        if ($jenis === 'masuk' && !empty($supplierId)) {
            $this->bahanModel->update($bahanBakuId, ['supplier_id' => $supplierId]);
        }

        if ($this->stokModel->insert($movementData)) {
            // Check if stock has dropped below minimum threshold
            if ($newStokSaatIni < $bahan['stok_minimum']) {
                $userModel = new \App\Models\UserModel();
                
                // Find active admins and warehouse staff with a phone number
                $recipients = $userModel->whereIn('role', ['admin', 'gudang'])
                                       ->where('is_active', 1)
                                       ->groupStart()
                                           ->where('phone !=', '')
                                           ->where('phone IS NOT NULL')
                                       ->groupEnd()
                                       ->findAll();

                $message = "🚨 *PERINGATAN STOK KRITIS (Dapur MBG)* 🚨\n\n"
                         . "Bahan baku berikut telah mencapai batas minimum:\n"
                         . "• *Bahan:* " . $bahan['nama'] . " (" . $bahan['kode'] . ")\n"
                         . "• *Stok Saat Ini:* " . $newStokSaatIni . " " . $bahan['satuan'] . "\n"
                         . "• *Stok Minimum:* " . $bahan['stok_minimum'] . " " . $bahan['satuan'] . "\n\n"
                         . "Mohon segera lakukan pengadaan bahan baku.";

                $sent = false;
                foreach ($recipients as $recipient) {
                    \App\Libraries\WaGateway::send($recipient['phone'], $message);
                    $sent = true;
                }

                // Send to fallback recipient if no active staff has a phone number
                if (!$sent) {
                    $config = config('WaGateway');
                    if (!empty($config->fallbackRecipient)) {
                        \App\Libraries\WaGateway::send($config->fallbackRecipient, $message);
                    }
                }
            }

            return redirect()->to('/stok')->with('success', 'Pergerakan stok berhasil dicatat.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal mencatat pergerakan stok.');
        }
    }

    /**
     * Form to edit recipe / ingredient details
     */
    public function edit($id)
    {
        $bahan = $this->bahanModel->find($id);
        if (!$bahan) {
            return redirect()->to('/stok')->with('error', 'Bahan baku tidak ditemukan.');
        }

        $suppliers = $this->supplierModel->findAll();

        return view('stok/edit_bahan', [
            'title' => 'Edit Bahan Baku',
            'bahan' => $bahan,
            'suppliers' => $suppliers,
        ]);
    }

    /**
     * Save edited ingredient details
     */
    public function update($id)
    {
        $bahan = $this->bahanModel->find($id);
        if (!$bahan) {
            return redirect()->to('/stok')->with('error', 'Bahan baku tidak ditemukan.');
        }

        $rules = [
            'kode'             => "required|max_length[20]",
            'nama'             => 'required|max_length[150]',
            'kategori'         => 'required',
            'satuan'           => 'required|max_length[20]',
            'harga_per_satuan' => 'required|numeric',
            'stok_minimum'     => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'kode'             => $this->request->getPost('kode'),
            'nama'             => $this->request->getPost('nama'),
            'kategori'         => $this->request->getPost('kategori'),
            'satuan'           => $this->request->getPost('satuan'),
            'harga_per_satuan' => $this->request->getPost('harga_per_satuan'),
            'stok_minimum'     => $this->request->getPost('stok_minimum'),
            'supplier_id'      => $this->request->getPost('supplier_id') ?: null,
            'is_active'        => $this->request->getPost('is_active') !== null ? 1 : 0,
        ];

        if ($this->bahanModel->update($id, $data)) {
            return redirect()->to('/stok')->with('success', 'Bahan baku berhasil diperbarui.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui bahan baku.');
        }
    }

    /**
     * Delete ingredient (soft delete)
     */
    public function delete($id)
    {
        if ($this->bahanModel->delete($id)) {
            return redirect()->to('/stok')->with('success', 'Bahan baku berhasil dinonaktifkan/dihapus.');
        } else {
            return redirect()->to('/stok')->with('error', 'Gagal menghapus bahan baku.');
        }
    }

    /**
     * Show history of movements for a specific ingredient (joins with users who created it).
     */
    public function kartuStok($id)
    {
        $bahan = $this->bahanModel->find($id);
        if (!$bahan) {
            return redirect()->to('/stok')->with('error', 'Bahan baku tidak ditemukan.');
        }

        $history = $this->stokModel->getKartuStok($id);

        return view('stok/kartu_stok', [
            'title' => 'Kartu Stok – ' . $bahan['nama'],
            'bahan' => $bahan,
            'history' => $history,
        ]);
    }

    /**
     * Redirect eye link to kartuStok
     */
    public function show($id)
    {
        return $this->kartuStok($id);
    }
}
