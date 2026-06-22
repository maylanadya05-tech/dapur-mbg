<?php

namespace App\Controllers;

use App\Models\ResepModel;
use App\Models\ResepDetailModel;
use App\Models\BahanBakuModel;
use App\Libraries\HppCalculator;
use App\Libraries\AuditLogger;

class Resep extends BaseController
{
    protected ResepModel $resepModel;
    protected ResepDetailModel $resepDetailModel;
    protected BahanBakuModel $bahanModel;

    public function __construct()
    {
        $this->resepModel = new ResepModel();
        $this->resepDetailModel = new ResepDetailModel();
        $this->bahanModel = new BahanBakuModel();
    }

    /**
     * List recipes, category, gizi metrics.
     */
    public function index()
    {
        $recipes = $this->resepModel->orderBy('nama_menu', 'ASC')->findAll();
        
        return view('resep/index', [
            'title'   => 'Daftar Resep & BOM',
            'recipes' => $recipes,
        ]);
    }

    /**
     * Form to create recipe (header details + BOM ingredients list dynamic inputs).
     */
    public function create()
    {
        $bahanBakuList = $this->bahanModel->where('is_active', 1)->orderBy('nama', 'ASC')->findAll();
        
        return view('resep/create', [
            'title'         => 'Tambah Resep & BOM Baru',
            'bahanBakuList' => $bahanBakuList,
            'kode'          => $this->resepModel->generateKode(),
        ]);
    }

    /**
     * Insert recipe header, upload food photo, insert multiple BOM detail rows.
     */
    public function store()
    {
        $rules = [
            'nama_menu'     => 'required|max_length[200]',
            'kategori'      => 'required|in_list[Makanan Pokok,Lauk Pauk,Sayuran,Buah,Minuman]',
            'porsi_standar' => 'required|integer|greater_than[0]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Handle File Upload
        $fotoPath = null;
        $fotoFile = $this->request->getFile('foto');
        if ($fotoFile && $fotoFile->isValid() && !$fotoFile->hasMoved()) {
            // Ensure target directory exists
            $targetDir = FCPATH . 'uploads/resep';
            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0777, true);
            }
            $newName = $fotoFile->getRandomName();
            $fotoFile->move($targetDir, $newName);
            $fotoPath = 'uploads/resep/' . $newName;
        }

        $resepData = [
            'kode'              => $this->request->getPost('kode') ?: $this->resepModel->generateKode(),
            'nama_menu'         => $this->request->getPost('nama_menu'),
            'deskripsi'         => $this->request->getPost('deskripsi'),
            'kategori'          => $this->request->getPost('kategori'),
            'total_kalori'      => (float) ($this->request->getPost('total_kalori') ?: 0),
            'total_protein'     => (float) ($this->request->getPost('total_protein') ?: 0),
            'total_karbohidrat' => (float) ($this->request->getPost('total_karbohidrat') ?: 0),
            'porsi_standar'     => (int) ($this->request->getPost('porsi_standar') ?: 1),
            'foto'              => $fotoPath,
            'is_active'         => $this->request->getPost('is_active') !== null ? 1 : 1,
        ];

        $this->resepModel->insert($resepData);
        $resepId = $this->resepModel->getInsertID();

        // Process BOM ingredients
        $bahanBakuIds = $this->request->getPost('bahan_baku_ids');
        $qtyPerPorsis = $this->request->getPost('qty_per_porsis');
        $satuans      = $this->request->getPost('satuans');
        $keterangans  = $this->request->getPost('keterangans');

        if (is_array($bahanBakuIds)) {
            for ($i = 0; $i < count($bahanBakuIds); $i++) {
                if (empty($bahanBakuIds[$i])) {
                    continue;
                }
                $this->resepDetailModel->insert([
                    'resep_id'      => $resepId,
                    'bahan_baku_id' => $bahanBakuIds[$i],
                    'qty_per_porsi' => (float) ($qtyPerPorsis[$i] ?? 0),
                    'satuan'        => $satuans[$i] ?? '',
                    'keterangan'    => $keterangans[$i] ?? '',
                ]);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan resep dan BOM.');
        }

        return redirect()->to('/resep')->with('success', 'Resep dan BOM berhasil disimpan.');
    }

    /**
     * Display detailed recipe page, gizi, and list of BOM ingredients.
     */
    public function show($id)
    {
        $resep = $this->resepModel->find($id);
        if (!$resep) {
            return redirect()->to('/resep')->with('error', 'Resep tidak ditemukan.');
        }

        $details = $this->resepDetailModel->getByResep($id);

        return view('resep/show', [
            'title'   => 'Detail Resep – ' . $resep['nama_menu'],
            'resep'   => $resep,
            'details' => $details,
        ]);
    }

    /**
     * Form to edit recipe details + BOM.
     */
    public function edit($id)
    {
        $resep = $this->resepModel->find($id);
        if (!$resep) {
            return redirect()->to('/resep')->with('error', 'Resep tidak ditemukan.');
        }

        $details = $this->resepDetailModel->getByResep($id);
        $bahanBakuList = $this->bahanModel->where('is_active', 1)->orderBy('nama', 'ASC')->findAll();

        return view('resep/edit', [
            'title'         => 'Edit Resep & BOM – ' . $resep['nama_menu'],
            'resep'         => $resep,
            'details'       => $details,
            'bahanBakuList' => $bahanBakuList,
        ]);
    }

    /**
     * Save edited recipe and details.
     */
    public function update($id)
    {
        $resep = $this->resepModel->find($id);
        if (!$resep) {
            return redirect()->to('/resep')->with('error', 'Resep tidak ditemukan.');
        }

        $rules = [
            'nama_menu'     => 'required|max_length[200]',
            'kategori'      => 'required|in_list[Makanan Pokok,Lauk Pauk,Sayuran,Buah,Minuman]',
            'porsi_standar' => 'required|integer|greater_than[0]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $fotoPath = $resep['foto'];
        $fotoFile = $this->request->getFile('foto');
        if ($fotoFile && $fotoFile->isValid() && !$fotoFile->hasMoved()) {
            // Delete old photo file if it exists
            if (!empty($fotoPath) && file_exists(FCPATH . $fotoPath)) {
                @unlink(FCPATH . $fotoPath);
            }

            // Ensure target directory exists
            $targetDir = FCPATH . 'uploads/resep';
            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0777, true);
            }

            $newName = $fotoFile->getRandomName();
            $fotoFile->move($targetDir, $newName);
            $fotoPath = 'uploads/resep/' . $newName;
        }

        $resepData = [
            'nama_menu'         => $this->request->getPost('nama_menu'),
            'deskripsi'         => $this->request->getPost('deskripsi'),
            'kategori'          => $this->request->getPost('kategori'),
            'total_kalori'      => (float) ($this->request->getPost('total_kalori') ?: 0),
            'total_protein'     => (float) ($this->request->getPost('total_protein') ?: 0),
            'total_karbohidrat' => (float) ($this->request->getPost('total_karbohidrat') ?: 0),
            'porsi_standar'     => (int) ($this->request->getPost('porsi_standar') ?: 1),
            'foto'              => $fotoPath,
            'is_active'         => $this->request->getPost('is_active') !== null ? 1 : 0,
        ];

        $this->resepModel->update($id, $resepData);

        // Delete existing BOM details and replace them
        $this->resepDetailModel->deleteByResep($id);

        $bahanBakuIds = $this->request->getPost('bahan_baku_ids');
        $qtyPerPorsis = $this->request->getPost('qty_per_porsis');
        $satuans      = $this->request->getPost('satuans');
        $keterangans  = $this->request->getPost('keterangans');

        if (is_array($bahanBakuIds)) {
            for ($i = 0; $i < count($bahanBakuIds); $i++) {
                if (empty($bahanBakuIds[$i])) {
                    continue;
                }
                $this->resepDetailModel->insert([
                    'resep_id'      => $id,
                    'bahan_baku_id' => $bahanBakuIds[$i],
                    'qty_per_porsi' => (float) ($qtyPerPorsis[$i] ?? 0),
                    'satuan'        => $satuans[$i] ?? '',
                    'keterangan'    => $keterangans[$i] ?? '',
                ]);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui resep dan BOM.');
        }

        return redirect()->to('/resep')->with('success', 'Resep dan BOM berhasil diperbarui.');
    }

    /**
     * Delete recipe (soft delete).
     */
    public function delete($id)
    {
        $resep = $this->resepModel->find($id);
        if (!$resep) {
            return redirect()->to('/resep')->with('error', 'Resep tidak ditemukan.');
        }

        if ($this->resepModel->delete($id)) {
            AuditLogger::log('DELETE', 'Resep', $id, "Resep '{$resep['nama_menu']}' dihapus.", $resep);
            return redirect()->to('/resep')->with('success', 'Resep berhasil dihapus.');
        } else {
            return redirect()->to('/resep')->with('error', 'Gagal menghapus resep.');
        }
    }

    /**
     * Show HPP (Harga Pokok Produksi) calculation for a recipe.
     */
    public function hpp(int $id): string
    {
        $resep = $this->resepModel->find($id);
        if (!$resep) {
            return redirect()->to('/resep')->with('error', 'Resep tidak ditemukan.');
        }

        $porsi = (int) ($this->request->getGet('porsi') ?: $resep['porsi_standar']);
        $hpp   = HppCalculator::calculate($id, $porsi);

        return view('resep/hpp', [
            'title' => 'Kalkulasi HPP – ' . $resep['nama_menu'],
            'resep' => $resep,
            'hpp'   => $hpp,
            'porsi' => $porsi,
        ]);
    }
}
