<?php

namespace App\Controllers;

use App\Models\FoodWasteModel;
use App\Models\BatchProduksiModel;
use App\Models\BahanBakuModel;
use CodeIgniter\HTTP\ResponseInterface;

class FoodWaste extends BaseController
{
    /**
     * List waste entries, group by category, calculate estimated value.
     */
    public function index(): string
    {
        $wasteModel = new FoodWasteModel();
        
        $entries = $wasteModel->getWithRelations();

        // Group by category summary
        $db = \Config\Database::connect();
        $summary = $db->table('food_waste')
            ->select('kategori, SUM(qty) as total_qty, SUM(estimasi_nilai) as total_nilai, COUNT(id) as total_records')
            ->groupBy('kategori')
            ->get()
            ->getResultArray();

        $totalEstimasiNilai = 0;
        foreach ($summary as $item) {
            $totalEstimasiNilai += (float)$item['total_nilai'];
        }

        return view('sampah/index', [
            'title'              => 'Laporan Food Waste – Dapur MBG',
            'entries'            => $entries,
            'summary'            => $summary,
            'totalEstimasiNilai' => $totalEstimasiNilai,
        ]);
    }

    /**
     * Form to record waste.
     */
    public function create(): string
    {
        $batchModel = new BatchProduksiModel();
        $bahanModel = new BahanBakuModel();

        $batches = $batchModel->orderBy('tanggal_produksi', 'DESC')->findAll();
        $bahanBaku = $bahanModel->where('is_active', 1)->orderBy('nama', 'ASC')->findAll();

        return view('sampah/create', [
            'title'     => 'Catat Food Waste – Dapur MBG',
            'batches'   => $batches,
            'bahanBaku' => $bahanBaku,
        ]);
    }

    /**
     * Insert waste entry.
     * Looks up the ingredient price to auto-calculate the estimated value if bahan_baku_id is provided!
     */
    public function store()
    {
        $wasteModel = new FoodWasteModel();
        $bahanModel = new BahanBakuModel();

        $bahanBakuId = $this->request->getPost('bahan_baku_id');
        $qty = (float)$this->request->getPost('qty');
        $estimasiNilai = 0.0;
        $satuan = $this->request->getPost('satuan') ?: 'kg';

        if (!empty($bahanBakuId)) {
            $bahan = $bahanModel->find($bahanBakuId);
            if ($bahan) {
                $estimasiNilai = $qty * (float)$bahan['harga_per_satuan'];
                $satuan = $bahan['satuan']; // Use default unit of the ingredient
            }
        } else {
            // Manual input fallback if provided
            $estimasiNilai = (float)$this->request->getPost('estimasi_nilai');
        }

        $data = [
            'batch_id'       => $this->request->getPost('batch_id') ?: null,
            'tanggal'        => $this->request->getPost('tanggal') ?: date('Y-m-d'),
            'kategori'       => $this->request->getPost('kategori'),
            'bahan_baku_id'  => !empty($bahanBakuId) ? (int)$bahanBakuId : null,
            'qty'            => $qty,
            'satuan'         => $satuan,
            'estimasi_nilai' => $estimasiNilai,
            'keterangan'     => $this->request->getPost('keterangan'),
            'dicatat_oleh'   => session()->get('user_id') ?: 1,
        ];

        if (!$this->validate($wasteModel->validationRules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        if (!$wasteModel->insert($data)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $wasteModel->errors());
        }

        return redirect()->to('/sisa')
            ->with('success', 'Pencatatan food waste berhasil disimpan.');
    }

    /**
     * Edit form.
     */
    public function edit(int $id)
    {
        $wasteModel = new FoodWasteModel();
        $batchModel = new BatchProduksiModel();
        $bahanModel = new BahanBakuModel();

        $waste = $wasteModel->find($id);

        if (!$waste) {
            return redirect()->to('/sisa')
                ->with('error', 'Data food waste tidak ditemukan.');
        }

        $batches = $batchModel->orderBy('tanggal_produksi', 'DESC')->findAll();
        $bahanBaku = $bahanModel->where('is_active', 1)->orderBy('nama', 'ASC')->findAll();

        return view('sampah/edit', [
            'title'     => 'Edit Food Waste – Dapur MBG',
            'waste'     => $waste,
            'batches'   => $batches,
            'bahanBaku' => $bahanBaku,
        ]);
    }

    /**
     * Validate and update.
     */
    public function update(int $id)
    {
        $wasteModel = new FoodWasteModel();
        $bahanModel = new BahanBakuModel();

        $waste = $wasteModel->find($id);
        if (!$waste) {
            return redirect()->to('/sisa')
                ->with('error', 'Data food waste tidak ditemukan.');
        }

        $bahanBakuId = $this->request->getPost('bahan_baku_id');
        $qty = (float)$this->request->getPost('qty');
        $estimasiNilai = 0.0;
        $satuan = $this->request->getPost('satuan') ?: 'kg';

        if (!empty($bahanBakuId)) {
            $bahan = $bahanModel->find($bahanBakuId);
            if ($bahan) {
                $estimasiNilai = $qty * (float)$bahan['harga_per_satuan'];
                $satuan = $bahan['satuan'];
            }
        } else {
            $estimasiNilai = (float)$this->request->getPost('estimasi_nilai');
        }

        $data = [
            'batch_id'       => $this->request->getPost('batch_id') ?: null,
            'tanggal'        => $this->request->getPost('tanggal') ?: date('Y-m-d'),
            'kategori'       => $this->request->getPost('kategori'),
            'bahan_baku_id'  => !empty($bahanBakuId) ? (int)$bahanBakuId : null,
            'qty'            => $qty,
            'satuan'         => $satuan,
            'estimasi_nilai' => $estimasiNilai,
            'keterangan'     => $this->request->getPost('keterangan'),
            'dicatat_oleh'   => session()->get('user_id') ?: $waste['dicatat_oleh'],
        ];

        if (!$this->validate($wasteModel->validationRules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        if (!$wasteModel->update($id, $data)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $wasteModel->errors());
        }

        return redirect()->to('/sisa')
            ->with('success', 'Pencatatan food waste berhasil diperbarui.');
    }

    /**
     * Delete waste entry.
     */
    public function delete(int $id)
    {
        $wasteModel = new FoodWasteModel();
        $waste = $wasteModel->find($id);

        if (!$waste) {
            return redirect()->to('/sisa')
                ->with('error', 'Data food waste tidak ditemukan.');
        }

        $wasteModel->delete($id);

        return redirect()->to('/sisa')
            ->with('success', 'Catatan food waste berhasil dihapus.');
    }
}
