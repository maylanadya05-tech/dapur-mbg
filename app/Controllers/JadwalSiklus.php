<?php

namespace App\Controllers;

use App\Models\JadwalSiklusModel;
use App\Models\ResepModel;
use App\Models\BatchProduksiModel;
use App\Libraries\HppCalculator;
use App\Libraries\AuditLogger;
use CodeIgniter\HTTP\ResponseInterface;

class JadwalSiklus extends BaseController
{
    /**
     * List cycle schedules, active cycle, start/end dates.
     */
    public function index(): string
    {
        $siklusModel = new JadwalSiklusModel();

        $schedules = $siklusModel->getWithDetailCount();
        $activeCycle = $siklusModel->getAktif();

        $db = \Config\Database::connect();
        foreach ($schedules as &$item) {
            // Fetch day-by-day menu names for this schedule
            $details = $db->table('jadwal_siklus_detail jsd')
                ->select('jsd.hari_ke, r.nama_menu')
                ->join('resep r', 'r.id = jsd.resep_id')
                ->where('jsd.siklus_id', $item['id'])
                ->orderBy('jsd.hari_ke', 'ASC')
                ->get()
                ->getResultArray();
                
            $item['detail_menu'] = [];
            foreach ($details as $detail) {
                $item['detail_menu'][$detail['hari_ke']] = $detail['nama_menu'];
            }
            
            // Map database status
            if ($item['is_active'] == 1) {
                $item['status'] = 'aktif';
            } else {
                $today = date('Y-m-d');
                if ($item['tanggal_selesai'] < $today) {
                    $item['status'] = 'selesai';
                } else {
                    $item['status'] = 'terjadwal';
                }
            }
        }

        return view('jadwal/index', [
            'title'       => 'Jadwal Siklus Menu Makanan – Dapur MBG',
            'jadwalList'  => $schedules,
            'activeCycle' => $activeCycle,
        ]);
    }

    /**
     * Form to create cycle schedule.
     */
    public function create(): string
    {
        $resepModel = new ResepModel();
        $recipes = $resepModel->getActiveList();

        return view('jadwal/create', [
            'title'   => 'Buat Jadwal Siklus Baru – Dapur MBG',
            'recipes' => $recipes,
        ]);
    }

    /**
     * Save cycle and cycle details.
     */
    public function store()
    {
        $siklusModel = new JadwalSiklusModel();
        
        $db = \Config\Database::connect();
        $db->transStart();

        $isActive = $this->request->getPost('is_active') !== null ? (int)$this->request->getPost('is_active') : 1;

        if ($isActive === 1) {
            // Deactivate all other cycles so only one is active at a time
            $db->table('jadwal_siklus')->update(['is_active' => 0]);
        }

        $tanggalMulai = $this->request->getPost('tanggal_mulai');
        $durasiHari = (int)$this->request->getPost('durasi_hari') ?: 5;
        $tanggalSelesai = !empty($tanggalMulai) ? date('Y-m-d', strtotime($tanggalMulai . ' + ' . ($durasiHari - 1) . ' days')) : null;

        $siklusData = [
            'nama_siklus'     => $this->request->getPost('nama_siklus'),
            'durasi_hari'     => $durasiHari,
            'tanggal_mulai'   => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'is_active'      => $isActive,
            'created_by'     => session()->get('user_id') ?: 1,
        ];

        if (!$this->validate($siklusModel->validationRules)) {
            $db->transRollback();
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        if (!$siklusModel->insert($siklusData)) {
            $db->transRollback();
            return redirect()->back()
                ->withInput()
                ->with('errors', $siklusModel->errors());
        }

        $siklusId = $siklusModel->getInsertID();

        // Save recipe mappings to Day 1, Day 2, ..., Day N
        $resepHari = $this->request->getPost('resep_hari'); // array: day_number => resep_id
        $keteranganHari = $this->request->getPost('keterangan_hari'); // array: day_number => keterangan text

        if (is_array($resepHari)) {
            foreach ($resepHari as $hariKe => $resepId) {
                if (!empty($resepId)) {
                    $db->table('jadwal_siklus_detail')->insert([
                        'siklus_id'  => $siklusId,
                        'hari_ke'    => (int)$hariKe,
                        'resep_id'   => (int)$resepId,
                        'keterangan' => $keteranganHari[$hariKe] ?? '',
                    ]);
                }
            }
        }

        $db->transComplete();

        return redirect()->to('/jadwal')
            ->with('success', 'Jadwal siklus ' . $siklusData['nama_siklus'] . ' berhasil disimpan.');
    }

    /**
     * Edit form.
     */
    public function edit(int $id)
    {
        $siklusModel = new JadwalSiklusModel();
        $resepModel = new ResepModel();

        $schedule = $siklusModel->find($id);
        if (!$schedule) {
            return redirect()->to('/jadwal')
                ->with('error', 'Jadwal siklus tidak ditemukan.');
        }

        $db = \Config\Database::connect();
        $details = $db->table('jadwal_siklus_detail')
            ->where('siklus_id', $id)
            ->orderBy('hari_ke', 'ASC')
            ->get()
            ->getResultArray();

        // Map details by day_number for easy access in view
        $mappedDetails = [];
        foreach ($details as $detail) {
            $mappedDetails[$detail['hari_ke']] = $detail;
        }

        $recipes = $resepModel->getActiveList();

        return view('jadwal/edit', [
            'title'         => 'Edit Jadwal Siklus – Dapur MBG',
            'schedule'      => $schedule,
            'mappedDetails' => $mappedDetails,
            'recipes'       => $recipes,
        ]);
    }

    /**
     * Validate and update.
     */
    public function update(int $id)
    {
        $siklusModel = new JadwalSiklusModel();
        $schedule = $siklusModel->find($id);

        if (!$schedule) {
            return redirect()->to('/jadwal')
                ->with('error', 'Jadwal siklus tidak ditemukan.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $isActive = $this->request->getPost('is_active') !== null ? (int)$this->request->getPost('is_active') : 0;

        if ($isActive === 1) {
            // Deactivate all other cycles so only one is active at a time
            $db->table('jadwal_siklus')->where('id !=', $id)->update(['is_active' => 0]);
        }

        $tanggalMulai = $this->request->getPost('tanggal_mulai');
        $durasiHari = (int)$this->request->getPost('durasi_hari') ?: 5;
        $tanggalSelesai = !empty($tanggalMulai) ? date('Y-m-d', strtotime($tanggalMulai . ' + ' . ($durasiHari - 1) . ' days')) : null;

        $siklusData = [
            'nama_siklus'     => $this->request->getPost('nama_siklus'),
            'durasi_hari'     => $durasiHari,
            'tanggal_mulai'   => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'is_active'      => $isActive,
            'created_by'     => $schedule['created_by'],
        ];

        if (!$this->validate($siklusModel->validationRules)) {
            $db->transRollback();
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        if (!$siklusModel->update($id, $siklusData)) {
            $db->transRollback();
            return redirect()->back()
                ->withInput()
                ->with('errors', $siklusModel->errors());
        }

        // Delete old mappings and write new ones
        $db->table('jadwal_siklus_detail')->where('siklus_id', $id)->delete();

        $resepHari = $this->request->getPost('resep_hari');
        $keteranganHari = $this->request->getPost('keterangan_hari');

        if (is_array($resepHari)) {
            foreach ($resepHari as $hariKe => $resepId) {
                if (!empty($resepId)) {
                    $db->table('jadwal_siklus_detail')->insert([
                        'siklus_id'  => $id,
                        'hari_ke'    => (int)$hariKe,
                        'resep_id'   => (int)$resepId,
                        'keterangan' => $keteranganHari[$hariKe] ?? '',
                    ]);
                }
            }
        }

        $db->transComplete();

        return redirect()->to('/jadwal')
            ->with('success', 'Jadwal siklus ' . $siklusData['nama_siklus'] . ' berhasil diperbarui.');
    }

    /**
     * Delete cycle schedule.
     */
    public function delete(int $id)
    {
        $siklusModel = new JadwalSiklusModel();
        $schedule = $siklusModel->find($id);

        if (!$schedule) {
            return redirect()->to('/jadwal')
                ->with('error', 'Jadwal siklus tidak ditemukan.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Delete details first
        $db->table('jadwal_siklus_detail')->where('siklus_id', $id)->delete();
        $siklusModel->delete($id);

        $db->transComplete();

        return redirect()->to('/jadwal')
            ->with('success', 'Jadwal siklus menu berhasil dihapus.');
    }
    /**
     * Auto-generate batch produksi dari jadwal siklus yang aktif untuk hari ini.
     */
    public function generateBatch(int $id)
    {
        $siklusModel = new JadwalSiklusModel();
        $batchModel  = new BatchProduksiModel();

        $siklus = $siklusModel->find($id);
        if (!$siklus) {
            return redirect()->to('/jadwal')->with('error', 'Jadwal siklus tidak ditemukan.');
        }

        $db = \Config\Database::connect();

        // Determine which day in the cycle today is
        $tanggalMulai = $siklus['tanggal_mulai'];
        $today        = date('Y-m-d');
        $diffDays     = (int) ((strtotime($today) - strtotime($tanggalMulai)) / 86400);
        $hariKe       = ($diffDays % (int) $siklus['durasi_hari']) + 1;

        // Get today's menu
        $detail = $db->table('jadwal_siklus_detail')
            ->where('siklus_id', $id)
            ->where('hari_ke', $hariKe)
            ->get()->getRowArray();

        if (!$detail) {
            return redirect()->to('/jadwal')
                ->with('warning', "Tidak ada menu yang dijadwalkan untuk Hari ke-{$hariKe} dalam siklus ini.");
        }

        // Check if batch already exists for this resep today
        $existing = $batchModel->where('resep_id', $detail['resep_id'])
            ->where('tanggal_produksi', $today)
            ->first();

        if ($existing) {
            return redirect()->to('/jadwal')
                ->with('warning', 'Batch untuk resep hari ini sudah dibuat sebelumnya: ' . $existing['nomor_batch']);
        }

        // Get target porsi from active schools total students
        $targetPorsi = $db->table('sekolah')
            ->selectSum('jumlah_siswa')
            ->where('is_active', 1)
            ->get()->getRowArray();

        $porsi = max((int)($targetPorsi['jumlah_siswa'] ?? 100), 100);

        $batchData = [
            'nomor_batch'      => $batchModel->generateNomorBatch(),
            'tanggal_produksi' => $today,
            'resep_id'         => $detail['resep_id'],
            'target_porsi'     => $porsi,
            'porsi_selesai'    => 0,
            'status'           => 'persiapan',
            'tim_produksi'     => 'Auto-generate dari Jadwal Siklus',
            'catatan'          => "Dibuat otomatis dari siklus: {$siklus['nama_siklus']} (Hari ke-{$hariKe})",
            'dibuat_oleh'      => session()->get('user_id') ?: 1,
        ];

        $batchModel->insert($batchData);
        $batchId = $batchModel->getInsertID();

        AuditLogger::log('CREATE', 'Produksi', $batchId,
            "Batch {$batchData['nomor_batch']} dibuat otomatis dari siklus {$siklus['nama_siklus']}.",
            [], $batchData);

        return redirect()->to('/produksi')->with('success',
            "Batch produksi {$batchData['nomor_batch']} berhasil dibuat otomatis dari siklus '{$siklus['nama_siklus']}'!");
    }

    /**
     * Estimasi kebutuhan bahan baku untuk satu siklus penuh.
     */
    public function estimasiBahan(int $id): string
    {
        $siklusModel = new JadwalSiklusModel();
        $siklus = $siklusModel->find($id);

        if (!$siklus) {
            return redirect()->to('/jadwal')->with('error', 'Jadwal siklus tidak ditemukan.');
        }

        $db = \Config\Database::connect();

        // Get total active students (porsi per hari)
        $targetRow = $db->table('sekolah')
            ->selectSum('jumlah_siswa')
            ->where('is_active', 1)
            ->get()->getRowArray();

        $porsiPerHari = max((int)($targetRow['jumlah_siswa'] ?? 100), 100);

        $estimasi = HppCalculator::estimatiBahanSiklus($id, $porsiPerHari);

        return view('jadwal/estimasi_bahan', [
            'title'       => 'Estimasi Bahan Baku – ' . $siklus['nama_siklus'],
            'siklus'      => $siklus,
            'estimasi'    => $estimasi,
            'porsiPerHari'=> $porsiPerHari,
        ]);
    }
}
