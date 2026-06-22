<?php

namespace App\Controllers;

use App\Models\DistribusiModel;
use App\Models\BatchProduksiModel;
use App\Models\SekolahModel;
use App\Models\ArmadaModel;
use App\Libraries\AuditLogger;
use App\Libraries\WhatsAppService;
use CodeIgniter\HTTP\ResponseInterface;

class Distribusi extends BaseController
{
    /**
     * List today's and historical distributions with status.
     */
    public function index(): string
    {
        $distribusiModel = new DistribusiModel();

        $hariIni = $distribusiModel->getDistribusiHariIni();
        $riwayat = $distribusiModel->getWithRelations();

        return view('distribusi/index', [
            'title'   => 'Pemantauan Distribusi Makanan – Dapur MBG',
            'hariIni' => $hariIni,
            'riwayat' => $riwayat,
        ]);
    }

    /**
     * Form to schedule distribution.
     */
    public function create(): string
    {
        $batchModel   = new BatchProduksiModel();
        $sekolahModel = new SekolahModel();
        $armadaModel  = new ArmadaModel();

        $batches = $batchModel->select('batch_produksi.*, resep.nama_menu')
            ->join('resep', 'resep.id = batch_produksi.resep_id', 'left')
            ->orderBy('batch_produksi.tanggal_produksi', 'DESC')
            ->orderBy('batch_produksi.id', 'DESC')
            ->findAll();

        $sekolah = $sekolahModel->getActiveList();
        $armada  = $armadaModel->getAvailable();

        return view('distribusi/create', [
            'title'   => 'Jadwalkan Distribusi Baru – Dapur MBG',
            'batches' => $batches,
            'sekolah' => $sekolah,
            'armada'  => $armada,
        ]);
    }

    /**
     * Insert distribution.
     */
    public function store()
    {
        $distribusiModel = new DistribusiModel();

        $data = [
            'batch_id'           => $this->request->getPost('batch_id'),
            'sekolah_id'         => $this->request->getPost('sekolah_id'),
            'tanggal_distribusi' => $this->request->getPost('tanggal_distribusi') ?: date('Y-m-d'),
            'jumlah_porsi'       => $this->request->getPost('jumlah_porsi'),
            'pengirim'           => $this->request->getPost('pengirim'),
            'armada_id'          => $this->request->getPost('armada_id') ?: null,
            'status'             => 'dijadwalkan',
            'catatan'            => $this->request->getPost('catatan'),
            'qr_token'           => bin2hex(random_bytes(16)),
        ];

        if (!$this->validate($distribusiModel->validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if (!$distribusiModel->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $distribusiModel->errors());
        }

        $newId = $distribusiModel->getInsertID();
        AuditLogger::log('CREATE', 'Distribusi', $newId, "Distribusi dijadwalkan.", [], $data);

        return redirect()->to('/distribusi')->with('success', 'Jadwal distribusi berhasil disimpan.');
    }

    /**
     * View distribution details, proof of delivery, surat jalan link.
     */
    public function show(int $id)
    {
        $distribusiModel = new DistribusiModel();

        $distribusi = $distribusiModel->db->table('distribusi d')
            ->select('d.*, bp.nomor_batch, bp.tanggal_produksi, r.nama_menu, s.nama AS nama_sekolah, s.jenjang, s.alamat, s.phone, arm.no_polisi, arm.pengemudi, arm.phone_pengemudi')
            ->join('batch_produksi bp', 'bp.id = d.batch_id', 'left')
            ->join('resep r', 'r.id = bp.resep_id', 'left')
            ->join('sekolah s', 's.id = d.sekolah_id', 'left')
            ->join('armada arm', 'arm.id = d.armada_id', 'left')
            ->where('d.id', $id)
            ->get()->getRowArray();

        if (!$distribusi) {
            return redirect()->to('/distribusi')->with('error', 'Data distribusi tidak ditemukan.');
        }

        return view('distribusi/show', [
            'title'      => 'Detail Distribusi #' . $id . ' – Dapur MBG',
            'distribusi' => $distribusi,
        ]);
    }

    /**
     * Print Surat Jalan with QR Code
     */
    public function suratJalan(int $id): string
    {
        $distribusiModel = new DistribusiModel();

        $distribusi = $distribusiModel->db->table('distribusi d')
            ->select('d.*, bp.nomor_batch, bp.tanggal_produksi, r.nama_menu, s.nama AS nama_sekolah, s.jenjang, s.alamat, s.phone, arm.no_polisi, arm.pengemudi, arm.jenis as jenis_kendaraan')
            ->join('batch_produksi bp', 'bp.id = d.batch_id', 'left')
            ->join('resep r', 'r.id = bp.resep_id', 'left')
            ->join('sekolah s', 's.id = d.sekolah_id', 'left')
            ->join('armada arm', 'arm.id = d.armada_id', 'left')
            ->where('d.id', $id)
            ->get()->getRowArray();

        if (!$distribusi) {
            return redirect()->to('/distribusi')->with('error', 'Data distribusi tidak ditemukan.');
        }

        // Generate QR content: konfirmasi URL
        $qrContent = base_url('/distribusi/konfirm-qr/' . ($distribusi['qr_token'] ?: $id));

        return view('distribusi/surat_jalan', [
            'title'      => 'Surat Jalan – Distribusi #' . $id,
            'distribusi' => $distribusi,
            'qrContent'  => $qrContent,
        ]);
    }

    /**
     * QR Code scan confirmation endpoint
     */
    public function konfirmQr(string $token)
    {
        $db = \Config\Database::connect();
        $distribusi = $db->table('distribusi d')
            ->select('d.*, s.nama as nama_sekolah')
            ->join('sekolah s', 's.id = d.sekolah_id', 'left')
            ->where('d.qr_token', $token)
            ->get()->getRowArray();

        if (!$distribusi) {
            return redirect()->to('/distribusi')->with('error', 'Token QR tidak valid atau sudah kadaluarsa.');
        }

        if ($distribusi['status'] === 'diterima') {
            return redirect()->to('/distribusi/show/' . $distribusi['id'])
                ->with('info', 'Distribusi ini sudah dikonfirmasi diterima.');
        }

        // Auto update status to diterima
        $db->table('distribusi')->where('id', $distribusi['id'])->update([
            'status'      => 'diterima',
            'waktu_terima'=> date('Y-m-d H:i:s'),
            'penerima'    => 'Konfirmasi via QR Code',
        ]);

        AuditLogger::log('UPDATE', 'Distribusi', (int) $distribusi['id'],
            "Distribusi ke {$distribusi['nama_sekolah']} dikonfirmasi via QR Code.");

        return redirect()->to('/distribusi/show/' . $distribusi['id'])
            ->with('success', 'Penerimaan makanan berhasil dikonfirmasi via QR Code! Terima kasih.');
    }

    /**
     * Update status of distribution.
     */
    public function updateStatus(int $id)
    {
        $distribusiModel = new DistribusiModel();
        $distribusi = $distribusiModel->find($id);

        if (!$distribusi) {
            return redirect()->to('/distribusi')->with('error', 'Data distribusi tidak ditemukan.');
        }

        $newStatus    = $this->request->getPost('status');
        $validStatuses = ['dijadwalkan', 'dikirim', 'diterima', 'bermasalah'];

        if (!in_array($newStatus, $validStatuses)) {
            return redirect()->back()->with('error', 'Status tidak valid.');
        }

        $data = ['status' => $newStatus];

        if ($newStatus === 'dikirim') {
            $data['waktu_kirim'] = date('Y-m-d H:i:s');

            // Send WA to school
            $wa = new WhatsAppService();
            $db = \Config\Database::connect();
            $detail = $db->table('distribusi d')
                ->select('d.*, s.nama as nama_sekolah, s.phone, bp.nomor_batch')
                ->join('sekolah s', 's.id = d.sekolah_id', 'left')
                ->join('batch_produksi bp', 'bp.id = d.batch_id', 'left')
                ->where('d.id', $id)->get()->getRowArray();

            if ($detail && !empty($detail['phone'])) {
                $wa->sendDistribusiDikirim($detail, $detail['phone']);
            }

        } elseif ($newStatus === 'diterima') {
            $data['penerima']    = $this->request->getPost('penerima') ?: 'Penerima Sekolah';
            $data['waktu_terima'] = date('Y-m-d H:i:s');

            // Handle foto bukti upload
            $foto = $this->request->getFile('foto_bukti');
            if ($foto && $foto->isValid() && !$foto->hasMoved()) {
                $targetDir = FCPATH . 'uploads/distribusi';
                if (!is_dir($targetDir)) @mkdir($targetDir, 0777, true);
                $newName = $foto->getRandomName();
                $foto->move($targetDir, $newName);
                $data['foto_bukti'] = 'uploads/distribusi/' . $newName;
            }

        } elseif ($newStatus === 'bermasalah') {
            $data['catatan'] = $this->request->getPost('catatan') ?: 'Dilaporkan bermasalah.';
        }

        if (!$distribusiModel->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $distribusiModel->errors());
        }

        AuditLogger::log('UPDATE', 'Distribusi', $id,
            "Status distribusi #{$id} diubah menjadi {$newStatus}.", $distribusi, $data);

        return redirect()->to('/distribusi/show/' . $id)
            ->with('success', 'Status distribusi berhasil diubah menjadi ' . ucfirst($newStatus) . '.');
    }
}
