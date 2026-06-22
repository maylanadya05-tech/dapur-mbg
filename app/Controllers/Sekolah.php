<?php

namespace App\Controllers;

use App\Models\SekolahModel;
use App\Libraries\AuditLogger;
use CodeIgniter\HTTP\ResponseInterface;

class Sekolah extends BaseController
{
    /**
     * List schools, active status, and total students.
     */
    public function index(): string
    {
        $sekolahModel = new SekolahModel();
        
        $sekolah = $sekolahModel->orderBy('nama', 'ASC')->findAll();
        $totalSiswa = $sekolahModel->countTotalSiswa();

        return view('sekolah/index', [
            'title'      => 'Manajemen Sekolah – Dapur MBG',
            'sekolah'    => $sekolah,
            'totalSiswa' => $totalSiswa,
        ]);
    }

    /**
     * Form to add school.
     */
    public function create(): string
    {
        return view('sekolah/create', [
            'title' => 'Tambah Sekolah Baru – Dapur MBG',
        ]);
    }

    /**
     * Validate and insert school data.
     */
    public function store()
    {
        $sekolahModel = new SekolahModel();

        $data = [
            'kode'           => $this->request->getPost('kode'),
            'nama'           => $this->request->getPost('nama'),
            'jenjang'        => $this->request->getPost('jenjang'),
            'alamat'         => $this->request->getPost('alamat'),
            'kelurahan'      => $this->request->getPost('kelurahan'),
            'kecamatan'      => $this->request->getPost('kecamatan'),
            'kota'           => $this->request->getPost('kota'),
            'kepala_sekolah' => $this->request->getPost('kepala_sekolah'),
            'phone'          => $this->request->getPost('phone'),
            'jumlah_siswa'   => $this->request->getPost('jumlah_siswa') ? (int) $this->request->getPost('jumlah_siswa') : 0,
            'is_active'      => $this->request->getPost('is_active') !== null ? (int) $this->request->getPost('is_active') : 1,
        ];

        if (!$this->validate($sekolahModel->validationRules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        if (!$sekolahModel->insert($data)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $sekolahModel->errors());
        }

        return redirect()->to('/sekolah')
            ->with('success', 'Sekolah ' . $data['nama'] . ' berhasil ditambahkan.');
    }

    /**
     * Edit form.
     */
    public function edit(int $id)
    {
        $sekolahModel = new SekolahModel();
        $sekolah = $sekolahModel->find($id);

        if (!$sekolah) {
            return redirect()->to('/sekolah')
                ->with('error', 'Sekolah tidak ditemukan.');
        }

        return view('sekolah/edit', [
            'title'   => 'Edit Sekolah – Dapur MBG',
            'sekolah' => $sekolah,
        ]);
    }

    /**
     * Validate and update.
     */
    public function update(int $id)
    {
        $sekolahModel = new SekolahModel();
        $sekolah = $sekolahModel->find($id);

        if (!$sekolah) {
            return redirect()->to('/sekolah')
                ->with('error', 'Sekolah tidak ditemukan.');
        }

        $data = [
            'kode'           => $this->request->getPost('kode'),
            'nama'           => $this->request->getPost('nama'),
            'jenjang'        => $this->request->getPost('jenjang'),
            'alamat'         => $this->request->getPost('alamat'),
            'kelurahan'      => $this->request->getPost('kelurahan'),
            'kecamatan'      => $this->request->getPost('kecamatan'),
            'kota'           => $this->request->getPost('kota'),
            'kepala_sekolah' => $this->request->getPost('kepala_sekolah'),
            'phone'          => $this->request->getPost('phone'),
            'jumlah_siswa'   => $this->request->getPost('jumlah_siswa') ? (int) $this->request->getPost('jumlah_siswa') : 0,
            'is_active'      => $this->request->getPost('is_active') !== null ? (int) $this->request->getPost('is_active') : 0,
        ];

        if (!$this->validate($sekolahModel->validationRules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        if (!$sekolahModel->update($id, $data)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $sekolahModel->errors());
        }

        return redirect()->to('/sekolah')
            ->with('success', 'Data sekolah ' . $data['nama'] . ' berhasil diperbarui.');
    }

    /**
     * Soft delete school.
     */
    public function delete(int $id)
    {
        $sekolahModel = new SekolahModel();
        $sekolah = $sekolahModel->find($id);

        if (!$sekolah) {
            return redirect()->to('/sekolah')
                ->with('error', 'Sekolah tidak ditemukan.');
        }

        $sekolahModel->delete($id);

        return redirect()->to('/sekolah')
            ->with('success', 'Sekolah ' . $sekolah['nama'] . ' berhasil dinonaktifkan/dihapus.');
    }

    /**
     * Realisasi target vs aktual per sekolah
     */
    public function realisasi(int $id): string
    {
        $sekolahModel = new SekolahModel();
        $sekolah = $sekolahModel->find($id);

        if (!$sekolah) {
            return redirect()->to('/sekolah')->with('error', 'Sekolah tidak ditemukan.');
        }

        $db = \Config\Database::connect();

        // Get last 6 months of distribusi data for this school
        $sixMonthsAgo = date('Y-m-01', strtotime('-5 months'));

        $distribusiData = $db->table('distribusi')
            ->select("DATE_FORMAT(tanggal_distribusi, '%Y-%m') as bulan, SUM(jumlah_porsi) as total_diterima, COUNT(id) as total_pengiriman")
            ->where('sekolah_id', $id)
            ->where('status', 'diterima')
            ->where('tanggal_distribusi >=', $sixMonthsAgo)
            ->groupBy("DATE_FORMAT(tanggal_distribusi, '%Y-%m')")
            ->orderBy('bulan')
            ->get()->getResultArray();

        // Feedback data for this school
        $feedbackData = $db->table('feedback')
            ->select("DATE_FORMAT(tanggal, '%Y-%m') as bulan, AVG(rating) as avg_rating, COUNT(id) as total_feedback")
            ->where('sekolah_id', $id)
            ->where('tanggal >=', $sixMonthsAgo)
            ->groupBy("DATE_FORMAT(tanggal, '%Y-%m')")
            ->orderBy('bulan')
            ->get()->getResultArray();

        // Monthly target (jumlah_siswa × school days ~20/month)
        $targetPerBulan = ($sekolah['jumlah_siswa'] ?? 0) * 20;

        // All-time stats
        $totalDiterima = $db->table('distribusi')
            ->selectSum('jumlah_porsi')
            ->where('sekolah_id', $id)
            ->where('status', 'diterima')
            ->get()->getRowArray();

        $avgRating = $db->table('feedback')
            ->selectAvg('rating')
            ->where('sekolah_id', $id)
            ->get()->getRowArray();

        $totalPengiriman = $db->table('distribusi')
            ->where('sekolah_id', $id)
            ->countAllResults();

        $bermasalah = $db->table('distribusi')
            ->where('sekolah_id', $id)
            ->where('status', 'bermasalah')
            ->countAllResults();

        return view('sekolah/realisasi', [
            'title'          => 'Realisasi Distribusi – ' . $sekolah['nama'],
            'sekolah'        => $sekolah,
            'distribusiData' => $distribusiData,
            'feedbackData'   => $feedbackData,
            'targetPerBulan' => $targetPerBulan,
            'totalDiterima'  => (int)($totalDiterima['jumlah_porsi'] ?? 0),
            'avgRating'      => round((float)($avgRating['rating'] ?? 0), 1),
            'totalPengiriman'=> $totalPengiriman,
            'bermasalah'     => $bermasalah,
        ]);
    }
}
