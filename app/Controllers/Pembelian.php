<?php

namespace App\Controllers;

use App\Models\PurchaseOrderModel;
use App\Models\PoDetailModel;
use App\Models\SupplierModel;
use App\Models\BahanBakuModel;
use App\Libraries\AuditLogger;
use App\Libraries\WhatsAppService;

class Pembelian extends BaseController
{
    protected PurchaseOrderModel $poModel;
    protected PoDetailModel $poDetailModel;
    protected SupplierModel $supplierModel;
    protected BahanBakuModel $bahanModel;

    public function __construct()
    {
        $this->poModel = new PurchaseOrderModel();
        $this->poDetailModel = new PoDetailModel();
        $this->supplierModel = new SupplierModel();
        $this->bahanModel = new BahanBakuModel();
    }

    /**
     * List POs, filter by status.
     */
    public function index()
    {
        $statusFilter = $this->request->getGet('status');

        $db = \Config\Database::connect();
        $builder = $db->table('purchase_orders po')
            ->select('po.*, s.name AS supplier_name, s.contact_person, u.name AS dibuat_oleh_name, ua.name AS disetujui_oleh_name')
            ->join('supplier s', 's.id = po.supplier_id', 'left')
            ->join('users u', 'u.id = po.dibuat_oleh', 'left')
            ->join('users ua', 'ua.id = po.disetujui_oleh', 'left')
            ->orderBy('po.created_at', 'DESC');

        if ($statusFilter !== null && $statusFilter !== '') {
            $builder->where('po.status', $statusFilter);
        }

        $pos = $builder->get()->getResultArray();

        return view('pembelian/index', [
            'title'        => 'Purchase Orders',
            'pos'          => $pos,
            'statusFilter' => $statusFilter,
        ]);
    }

    /**
     * Form to create PO (select supplier, required date, item list inputs).
     */
    public function create()
    {
        $suppliers = $this->supplierModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll();
        $bahanBaku = $this->bahanModel->where('is_active', 1)->orderBy('nama', 'ASC')->findAll();

        return view('pembelian/create', [
            'title'     => 'Buat Purchase Order Baru',
            'suppliers' => $suppliers,
            'bahanBaku' => $bahanBaku,
            'nomorPO'   => $this->poModel->generateNomorPO(),
        ]);
    }

    /**
     * Create PO header and items in 'po_detail' table.
     */
    public function store()
    {
        $rules = [
            'supplier_id'        => 'required|integer',
            'tanggal_po'         => 'required|valid_date',
            'tanggal_dibutuhkan' => 'permit_empty|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $bahanBakuIds = $this->request->getPost('bahan_baku_ids');
        $qtys         = $this->request->getPost('qtys');
        $hargaSatuans = $this->request->getPost('harga_satuans');
        $catatans     = $this->request->getPost('catatans');

        $totalNilai = 0.00;
        $itemsToInsert = [];

        if (is_array($bahanBakuIds)) {
            for ($i = 0; $i < count($bahanBakuIds); $i++) {
                if (empty($bahanBakuIds[$i])) {
                    continue;
                }
                $bahanId = $bahanBakuIds[$i];
                $qty = (float) ($qtys[$i] ?? 0);
                if ($qty <= 0) {
                    continue;
                }

                $bahan = $this->bahanModel->find($bahanId);
                if (!$bahan) {
                    continue;
                }

                $harga = (float) ($hargaSatuans[$i] ?? $bahan['harga_per_satuan']);
                $subtotal = $qty * $harga;
                $totalNilai += $subtotal;

                $itemsToInsert[] = [
                    'bahan_baku_id' => $bahanId,
                    'qty'           => $qty,
                    'satuan'        => $bahan['satuan'],
                    'harga_satuan'  => $harga,
                    'subtotal'      => $subtotal,
                    'qty_diterima'  => 0.00,
                    'catatan'       => $catatans[$i] ?? '',
                ];
            }
        }

        if (empty($itemsToInsert)) {
            return redirect()->back()->withInput()->with('error', 'Purchase Order harus memiliki minimal 1 item barang dengan kuantitas yang valid.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $submitType = $this->request->getPost('submit_type'); // 'draft' or 'diajukan'
        $status = ($submitType === 'draft') ? 'draft' : 'diajukan';

        $poData = [
            'nomor_po'           => $this->poModel->generateNomorPO(),
            'supplier_id'        => (int) $this->request->getPost('supplier_id'),
            'tanggal_po'         => $this->request->getPost('tanggal_po'),
            'tanggal_dibutuhkan' => $this->request->getPost('tanggal_dibutuhkan') ?: null,
            'status'             => $status,
            'total_nilai'        => $totalNilai,
            'catatan'            => $this->request->getPost('catatan'),
            'dibuat_oleh'        => session()->get('user_id') ?: 1,
        ];

        $this->poModel->insert($poData);
        $poId = $this->poModel->getInsertID();

        foreach ($itemsToInsert as $item) {
            $item['po_id'] = $poId;
            $this->poDetailModel->insert($item);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan Purchase Order.');
        }

        AuditLogger::log('CREATE', 'Pembelian', $poId, "PO {$poData['nomor_po']} dibuat dengan status {$status}.", [], $poData);

        // WhatsApp notification for new submitted PO
        if ($status === 'diajukan') {
            $userModel = new \App\Models\UserModel();
            $admins = $userModel->where('role', 'admin')->where('is_active', 1)->findAll();
            $wa = new WhatsAppService();
            $supplier = $this->supplierModel->find((int) $this->request->getPost('supplier_id'));
            $poNotif  = array_merge($poData, ['supplier_name' => $supplier['name'] ?? '-']);

            foreach ($admins as $admin) {
                if (!empty($admin['phone'])) {
                    $wa->sendPoApproved($poNotif, $admin['phone']);
                }
            }
            if (empty($admins)) {
                $fallback = config('WaGateway')->fallbackRecipient;
                if ($fallback) $wa->send($fallback, "PO baru {$poData['nomor_po']} menunggu persetujuan.");
            }
        }

        return redirect()->to('/pembelian')->with('success', 'Purchase Order berhasil disimpan dengan status ' . $status . '.');
    }

    /**
     * View PO details, item list, and approval logs.
     */
    public function show($id)
    {
        $po = $this->poModel->getWithRelationsById($id);
        if (!$po) {
            return redirect()->to('/pembelian')->with('error', 'Purchase Order tidak ditemukan.');
        }

        $items = $this->poDetailModel->getByPO($id);

        return view('pembelian/show', [
            'title' => 'Detail Purchase Order – ' . $po['nomor_po'],
            'po'    => $po,
            'items' => $items,
        ]);
    }

    /**
     * Form to edit draft PO.
     */
    public function edit($id)
    {
        $po = $this->poModel->find($id);
        if (!$po) {
            return redirect()->to('/pembelian')->with('error', 'Purchase Order tidak ditemukan.');
        }

        if ($po['status'] !== 'draft' && $po['status'] !== 'diajukan') {
            return redirect()->to('/pembelian')->with('error', 'Hanya Purchase Order berstatus draft atau diajukan yang dapat diedit.');
        }

        $items = $this->poDetailModel->getByPO($id);
        $suppliers = $this->supplierModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll();
        $bahanBaku = $this->bahanModel->where('is_active', 1)->orderBy('nama', 'ASC')->findAll();

        return view('pembelian/edit', [
            'title'     => 'Edit Purchase Order – ' . $po['nomor_po'],
            'po'        => $po,
            'items'     => $items,
            'suppliers' => $suppliers,
            'bahanBaku' => $bahanBaku,
        ]);
    }

    /**
     * Save edited PO header and details.
     */
    public function update($id)
    {
        $po = $this->poModel->find($id);
        if (!$po) {
            return redirect()->to('/pembelian')->with('error', 'Purchase Order tidak ditemukan.');
        }

        if ($po['status'] !== 'draft' && $po['status'] !== 'diajukan') {
            return redirect()->to('/pembelian')->with('error', 'Hanya Purchase Order berstatus draft atau diajukan yang dapat diperbarui.');
        }

        $rules = [
            'supplier_id'        => 'required|integer',
            'tanggal_po'         => 'required|valid_date',
            'tanggal_dibutuhkan' => 'permit_empty|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $bahanBakuIds = $this->request->getPost('bahan_baku_ids');
        $qtys         = $this->request->getPost('qtys');
        $hargaSatuans = $this->request->getPost('harga_satuans');
        $catatans     = $this->request->getPost('catatans');

        $totalNilai = 0.00;
        $itemsToInsert = [];

        if (is_array($bahanBakuIds)) {
            for ($i = 0; $i < count($bahanBakuIds); $i++) {
                if (empty($bahanBakuIds[$i])) {
                    continue;
                }
                $bahanId = $bahanBakuIds[$i];
                $qty = (float) ($qtys[$i] ?? 0);
                if ($qty <= 0) {
                    continue;
                }

                $bahan = $this->bahanModel->find($bahanId);
                if (!$bahan) {
                    continue;
                }

                $harga = (float) ($hargaSatuans[$i] ?? $bahan['harga_per_satuan']);
                $subtotal = $qty * $harga;
                $totalNilai += $subtotal;

                $itemsToInsert[] = [
                    'po_id'         => $id,
                    'bahan_baku_id' => $bahanId,
                    'qty'           => $qty,
                    'satuan'        => $bahan['satuan'],
                    'harga_satuan'  => $harga,
                    'subtotal'      => $subtotal,
                    'qty_diterima'  => 0.00,
                    'catatan'       => $catatans[$i] ?? '',
                ];
            }
        }

        if (empty($itemsToInsert)) {
            return redirect()->back()->withInput()->with('error', 'Purchase Order harus memiliki minimal 1 item barang.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Delete existing items
        $this->poDetailModel->deleteByPO($id);

        $submitType = $this->request->getPost('submit_type'); // 'draft' or 'diajukan'
        $status = ($submitType === 'draft') ? 'draft' : 'diajukan';

        $poData = [
            'supplier_id'        => (int) $this->request->getPost('supplier_id'),
            'tanggal_po'         => $this->request->getPost('tanggal_po'),
            'tanggal_dibutuhkan' => $this->request->getPost('tanggal_dibutuhkan') ?: null,
            'status'             => $status,
            'total_nilai'        => $totalNilai,
            'catatan'            => $this->request->getPost('catatan'),
        ];

        $this->poModel->update($id, $poData);

        foreach ($itemsToInsert as $item) {
            $this->poDetailModel->insert($item);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui Purchase Order.');
        }

        return redirect()->to('/pembelian')->with('success', 'Purchase Order berhasil diperbarui.');
    }

    /**
     * Approve PO (Admin only). Change status to 'disetujui'.
     */
    public function approve($id)
    {
        if (session()->get('user_role') !== 'admin') {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya Admin yang dapat menyetujui Purchase Order.');
        }

        $po = $this->poModel->find($id);
        if (!$po) {
            return redirect()->back()->with('error', 'Purchase Order tidak ditemukan.');
        }

        if ($po['status'] !== 'diajukan') {
            return redirect()->back()->with('error', 'Hanya PO dengan status diajukan yang dapat disetujui.');
        }

        $data = [
            'status'            => 'disetujui',
            'disetujui_oleh'    => session()->get('user_id') ?: 1,
            'tanggal_disetujui' => date('Y-m-d H:i:s'),
        ];

        if ($this->poModel->update($id, $data)) {
            AuditLogger::log('APPROVE', 'Pembelian', $id, "PO {$po['nomor_po']} disetujui.", $po, $data);

            // Notify PO creator via WhatsApp
            $userModel = new \App\Models\UserModel();
            $creator   = $userModel->find($po['dibuat_oleh']);
            $wa        = new WhatsAppService();
            $supplier  = $this->supplierModel->find($po['supplier_id']);
            $poNotif   = array_merge($po, ['supplier_name' => $supplier['name'] ?? '-']);

            if ($creator && !empty($creator['phone'])) {
                $wa->sendPoApproved($poNotif, $creator['phone']);
            }

            return redirect()->to('/pembelian/show/' . $id)->with('success', 'Purchase Order berhasil disetujui.');
        } else {
            return redirect()->back()->with('error', 'Gagal menyetujui Purchase Order.');
        }
    }

    /**
     * Reject PO with reason (Admin only). Change status to 'ditolak'.
     */
    public function reject($id)
    {
        if (session()->get('user_role') !== 'admin') {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya Admin yang dapat menolak Purchase Order.');
        }

        $po = $this->poModel->find($id);
        if (!$po) {
            return redirect()->back()->with('error', 'Purchase Order tidak ditemukan.');
        }

        if ($po['status'] !== 'diajukan') {
            return redirect()->back()->with('error', 'Hanya PO dengan status diajukan yang dapat ditolak.');
        }

        $alasanTolak = $this->request->getPost('alasan_tolak');
        if (empty($alasanTolak)) {
            return redirect()->back()->with('error', 'Alasan penolakan (alasan_tolak) wajib diisi.');
        }

        $data = [
            'status'            => 'ditolak',
            'alasan_tolak'      => $alasanTolak,
            'disetujui_oleh'    => session()->get('user_id') ?: 1,
            'tanggal_disetujui' => date('Y-m-d H:i:s'),
        ];

        if ($this->poModel->update($id, $data)) {
            AuditLogger::log('REJECT', 'Pembelian', $id, "PO {$po['nomor_po']} ditolak. Alasan: {$alasanTolak}.", $po, $data);

            // Notify PO creator via WhatsApp
            $userModel = new \App\Models\UserModel();
            $creator   = $userModel->find($po['dibuat_oleh']);
            $wa        = new WhatsAppService();
            $poNotif   = array_merge($po, ['alasan_tolak' => $alasanTolak]);

            if ($creator && !empty($creator['phone'])) {
                $wa->sendPoRejected($poNotif, $creator['phone']);
            }

            return redirect()->to('/pembelian/show/' . $id)->with('success', 'Purchase Order telah ditolak.');
        } else {
            return redirect()->back()->with('error', 'Gagal menolak Purchase Order.');
        }
    }

    /**
     * Delete PO (draft / diajukan only).
     */
    public function delete($id)
    {
        $po = $this->poModel->find($id);
        if (!$po) {
            return redirect()->to('/pembelian')->with('error', 'Purchase Order tidak ditemukan.');
        }

        if ($po['status'] !== 'draft' && $po['status'] !== 'diajukan') {
            return redirect()->to('/pembelian')->with('error', 'Hanya Purchase Order berstatus draft atau diajukan yang dapat dihapus.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $this->poDetailModel->deleteByPO($id);
        $this->poModel->delete($id);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('/pembelian')->with('error', 'Gagal menghapus Purchase Order.');
        }

        return redirect()->to('/pembelian')->with('success', 'Purchase Order berhasil dihapus.');
    }
}
