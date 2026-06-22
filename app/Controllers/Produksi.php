<?php

namespace App\Controllers;

use App\Models\BatchProduksiModel;
use App\Models\ResepModel;
use App\Models\ResepDetailModel;
use App\Models\StokGudangModel;
use App\Models\BahanBakuModel;
use App\Libraries\AuditLogger;
use App\Libraries\WhatsAppService;

class Produksi extends BaseController
{
    protected BatchProduksiModel $batchModel;
    protected ResepModel $resepModel;
    protected ResepDetailModel $resepDetailModel;
    protected StokGudangModel $stokModel;

    public function __construct()
    {
        $this->batchModel = new BatchProduksiModel();
        $this->resepModel = new ResepModel();
        $this->resepDetailModel = new ResepDetailModel();
        $this->stokModel = new StokGudangModel();
    }

    /**
     * List today's production batches and active kanban view.
     */
    public function index()
    {
        $todayBatches = $this->batchModel->getHariIni();

        // Prepare Kanban board groups
        $kanban = [
            'persiapan'    => [],
            'sedang_masak' => [],
            'selesai'      => [],
            'dibatalkan'   => [],
        ];

        foreach ($todayBatches as $batch) {
            $status = $batch['status'] ?? 'persiapan';
            if (array_key_exists($status, $kanban)) {
                $kanban[$status][] = $batch;
            }
        }

        return view('produksi/index', [
            'title'        => 'Produksi Hari Ini',
            'todayBatches' => $todayBatches,
            'kanban'       => $kanban,
        ]);
    }

    /**
     * Form to create new batch (select recipe, target porsi, production team).
     */
    public function create()
    {
        $resepList = $this->resepModel->where('is_active', 1)->orderBy('nama_menu', 'ASC')->findAll();

        return view('produksi/create', [
            'title'     => 'Mulai Batch Produksi Baru',
            'resepList' => $resepList,
        ]);
    }

    /**
     * Create batch with state 'persiapan'.
     */
    public function store()
    {
        $rules = [
            'resep_id'     => 'required|integer',
            'target_porsi' => 'required|integer|greater_than[0]',
            'tim_produksi' => 'permit_empty|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $resepId = $this->request->getPost('resep_id');
        $resep = $this->resepModel->find($resepId);
        if (!$resep) {
            return redirect()->back()->withInput()->with('error', 'Resep tidak ditemukan.');
        }

        $data = [
            'nomor_batch'      => $this->batchModel->generateNomorBatch(),
            'tanggal_produksi' => date('Y-m-d'),
            'resep_id'         => $resepId,
            'target_porsi'     => (int) $this->request->getPost('target_porsi'),
            'porsi_selesai'    => 0,
            'status'           => 'persiapan',
            'tim_produksi'     => $this->request->getPost('tim_produksi'),
            'catatan'          => $this->request->getPost('catatan'),
            'dibuat_oleh'      => session()->get('user_id') ?: 1,
        ];

        if ($this->batchModel->insert($data)) {
            AuditLogger::log('CREATE', 'Produksi', $this->batchModel->getInsertID(),
                "Batch {$data['nomor_batch']} dibuat.", [], $data);
            return redirect()->to('/produksi')->with('success', 'Batch produksi berhasil dibuat dengan status persiapan.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal membuat batch produksi.');
        }
    }

    /**
     * View batch details, target porsi, and estimated ingredient consumption based on recipe BOM!
     */
    public function show($id)
    {
        // Fetch batch details with joined relations
        $batchRaw = $this->batchModel->db->table('batch_produksi bp')
            ->select('bp.*, r.nama_menu, r.kode AS resep_kode, r.kategori AS resep_kategori, u.name AS dibuat_oleh_name')
            ->join('resep r', 'r.id = bp.resep_id', 'left')
            ->join('users u', 'u.id = bp.dibuat_oleh', 'left')
            ->where('bp.id', $id)
            ->get()
            ->getResultArray();

        $batch = $batchRaw[0] ?? null;
        if (!$batch) {
            return redirect()->to('/produksi')->with('error', 'Batch produksi tidak ditemukan.');
        }

        // Get BOM list
        $bomDetails = $this->resepDetailModel->getByResep($batch['resep_id']);

        // Calculate estimates and check stock
        $bahanEstimasi = [];
        foreach ($bomDetails as $detail) {
            $estimasi = (float) $detail['qty_per_porsi'] * (int) $batch['target_porsi'];
            $stokSaatIni = (float) $this->stokModel->getStokSaatIni($detail['bahan_baku_id']);

            $bahanEstimasi[] = [
                'bahan_baku_id' => $detail['bahan_baku_id'],
                'kode_bahan'    => $detail['kode_bahan'],
                'nama_bahan'    => $detail['nama_bahan'],
                'qty_per_porsi' => (float) $detail['qty_per_porsi'],
                'satuan'        => $detail['satuan'],
                'estimasi'      => $estimasi,
                'stok_saat_ini' => $stokSaatIni,
                'cukup'         => ($stokSaatIni >= $estimasi),
            ];
        }

        return view('produksi/show', [
            'title'         => 'Detail Batch Produksi – ' . $batch['nomor_batch'],
            'batch'         => $batch,
            'bahanEstimasi' => $bahanEstimasi,
        ]);
    }

    /**
     * Update status (persiapan -> sedang_masak -> selesai -> dibatalkan).
     * Crucial: When status changes to 'sedang_masak', automatically deduct required ingredients
     * from 'stok_gudang' based on the recipe BOM (resep_detail) and create stock movement record.
     */
    public function updateStatus($id)
    {
        $batch = $this->batchModel->find($id);
        if (!$batch) {
            return redirect()->back()->with('error', 'Batch produksi tidak ditemukan.');
        }

        $newStatus = $this->request->getPost('status');
        $validStatuses = ['persiapan', 'sedang_masak', 'selesai', 'dibatalkan'];

        if (!in_list($newStatus, implode(',', $validStatuses))) {
            return redirect()->back()->with('error', 'Status tidak valid.');
        }

        $currentStatus = $batch['status'];

        // If status is not changing, do nothing
        if ($currentStatus === $newStatus) {
            return redirect()->back()->with('info', 'Status batch tidak berubah.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $updateData = ['status' => $newStatus];

        // 1. Transition to 'sedang_masak' -> Deduct ingredients automatically
        if ($newStatus === 'sedang_masak') {
            $updateData['mulai_masak'] = date('Y-m-d H:i:s');

            // Only deduct if we were in 'persiapan' state (prevent double deduction)
            if ($currentStatus === 'persiapan') {
                $bom = $this->resepDetailModel->getByResep($batch['resep_id']);

                foreach ($bom as $item) {
                    $qtyDeduct = (float) $item['qty_per_porsi'] * (int) $batch['target_porsi'];
                    $currentStok = (float) $this->stokModel->getStokSaatIni($item['bahan_baku_id']);
                    $newStok = $currentStok - $qtyDeduct;

                    $movement = [
                        'bahan_baku_id' => $item['bahan_baku_id'],
                        'stok_saat_ini' => $newStok,
                        'stok_masuk'    => 0.00,
                        'stok_keluar'   => $qtyDeduct,
                        'tanggal'       => date('Y-m-d'),
                        'keterangan'    => 'Dipotong otomatis untuk Produksi Batch: ' . $batch['nomor_batch'],
                        'created_by'    => session()->get('user_id') ?: 1,
                    ];

                    $this->stokModel->insert($movement);
                }
            }
        }

        // 2. Transition to 'selesai' -> Record completed portions and end time
        if ($newStatus === 'selesai') {
            $updateData['selesai_masak'] = date('Y-m-d H:i:s');
            
            // Read finished portions, fallback to target if not provided
            $porsiSelesai = $this->request->getPost('porsi_selesai');
            $updateData['porsi_selesai'] = ($porsiSelesai !== null && $porsiSelesai !== '') 
                ? (int) $porsiSelesai 
                : (int) $batch['target_porsi'];
        }

        // 3. Transition to 'dibatalkan' -> If previously cooked/deducted, optionally return stock or note it.
        // For consistency, we will just change status to dibatalkan.
        if ($newStatus === 'dibatalkan') {
            $updateData['catatan'] = $batch['catatan'] . "\n[Batal pada " . date('Y-m-d H:i:s') . ']';
        }

        $this->batchModel->update($id, $updateData);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Gagal memperbarui status produksi.');
        }

        // Audit log status change
        AuditLogger::log('UPDATE', 'Produksi', $id,
            "Status batch {$batch['nomor_batch']} diubah dari {$currentStatus} ke {$newStatus}.",
            $batch, $updateData);

        // WhatsApp notification when batch is done
        if ($newStatus === 'selesai') {
            $wa      = new WhatsAppService();
            $resep   = $this->resepModel->find($batch['resep_id']);
            $batchNotif = array_merge($batch, [
                'nama_menu'    => $resep['nama_menu'] ?? '-',
                'porsi_selesai'=> $updateData['porsi_selesai'] ?? $batch['target_porsi'],
            ]);

            // Notify all distribusi/admin users
            $userModel = new \App\Models\UserModel();
            $notify = $userModel->whereIn('role', ['admin', 'distribusi'])->where('is_active', 1)->findAll();
            foreach ($notify as $u) {
                if (!empty($u['phone'])) {
                    $wa->sendBatchSelesai($batchNotif, $u['phone']);
                }
            }
        }

        return redirect()->to('/produksi/show/' . $id)
            ->with('success', 'Status batch produksi berhasil diubah menjadi ' . $newStatus . '.');
    }
}
