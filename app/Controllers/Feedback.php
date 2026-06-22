<?php

namespace App\Controllers;

use App\Models\FeedbackModel;
use App\Models\DistribusiModel;
use App\Models\SekolahModel;
use CodeIgniter\HTTP\ResponseInterface;

class Feedback extends BaseController
{
    /**
     * List school feedback, ratings, and filter by aspects.
     */
    public function index(): string
    {
        $feedbackModel = new FeedbackModel();
        $aspekFilter = $this->request->getGet('aspek');

        $query = $feedbackModel->db->table('feedback f')
            ->select('f.*, s.nama AS nama_sekolah, s.jenjang, bp.nomor_batch, r.nama_menu')
            ->join('sekolah s', 's.id = f.sekolah_id', 'left')
            ->join('batch_produksi bp', 'bp.id = f.batch_id', 'left')
            ->join('resep r', 'r.id = bp.resep_id', 'left');

        if (!empty($aspekFilter)) {
            $query->where('f.aspek', $aspekFilter);
        }

        $feedback = $query->orderBy('f.tanggal', 'DESC')->get()->getResultArray();
        $averageRatings = $feedbackModel->getAverageRatingBySekolah();

        return view('feedback/index', [
            'title'          => 'Umpan Balik & Kepuasan Sekolah – Dapur MBG',
            'feedback'       => $feedback,
            'averageRatings' => $averageRatings,
            'selectedAspek'  => $aspekFilter,
        ]);
    }

    /**
     * Publicly accessible or private form to submit feedback for a specific distribution batch.
     */
    public function create(): string
    {
        $distribusiModel = new DistribusiModel();
        $sekolahModel = new SekolahModel();
        
        $selectedDistribusiId = $this->request->getGet('distribusi_id');
        $selectedDistribusi = null;

        if ($selectedDistribusiId) {
            $selectedDistribusi = $distribusiModel->db->table('distribusi d')
                ->select('d.*, s.nama AS nama_sekolah, bp.nomor_batch')
                ->join('sekolah s', 's.id = d.sekolah_id', 'left')
                ->join('batch_produksi bp', 'bp.id = d.batch_id', 'left')
                ->where('d.id', $selectedDistribusiId)
                ->get()
                ->getRowArray();
        }

        // Get recent distributions (last 50) for the feedback dropdown selector
        $distributions = $distribusiModel->db->table('distribusi d')
            ->select('d.id, d.tanggal_distribusi, d.jumlah_porsi, s.nama AS nama_sekolah, bp.nomor_batch')
            ->join('sekolah s', 's.id = d.sekolah_id', 'left')
            ->join('batch_produksi bp', 'bp.id = d.batch_id', 'left')
            ->orderBy('d.tanggal_distribusi', 'DESC')
            ->limit(50)
            ->get()
            ->getResultArray();

        $sekolahList = $sekolahModel->getActiveList();

        return view('feedback/create', [
            'title'              => 'Kirim Umpan Balik Sekolah – Dapur MBG',
            'distributions'      => $distributions,
            'selectedDistribusi' => $selectedDistribusi,
            'sekolahList'        => $sekolahList,
        ]);
    }

    /**
     * Save feedback (rating 1-5, comment, aspect).
     */
    public function store()
    {
        $feedbackModel = new FeedbackModel();
        $distribusiModel = new DistribusiModel();

        $distribusiId = $this->request->getPost('distribusi_id');
        $sekolahId = $this->request->getPost('sekolah_id');
        $batchId = $this->request->getPost('batch_id');

        // Auto-fill relation details if distribution is specified
        if (!empty($distribusiId)) {
            $distribusi = $distribusiModel->find($distribusiId);
            if ($distribusi) {
                $sekolahId = $distribusi['sekolah_id'];
                $batchId = $distribusi['batch_id'];
            }
        }

        $data = [
            'distribusi_id' => !empty($distribusiId) ? (int)$distribusiId : null,
            'sekolah_id'    => (int)$sekolahId,
            'batch_id'      => !empty($batchId) ? (int)$batchId : null,
            'rating'        => (int)$this->request->getPost('rating'),
            'komentar'      => $this->request->getPost('komentar'),
            'aspek'         => $this->request->getPost('aspek') ?: 'keseluruhan',
            'nama_pemberi'  => $this->request->getPost('nama_pemberi') ?: 'Perwakilan Sekolah',
            'tanggal'       => $this->request->getPost('tanggal') ?: date('Y-m-d'),
        ];

        if (!$this->validate($feedbackModel->validationRules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        if (!$feedbackModel->insert($data)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $feedbackModel->errors());
        }

        if (session()->get('isLoggedIn')) {
            return redirect()->to('/feedback')
                ->with('success', 'Umpan balik sekolah berhasil dikirim.');
        } else {
            return redirect()->back()
                ->with('success', 'Terima kasih atas umpan balik Anda! Data Anda telah berhasil kami simpan.');
        }
    }

    /**
     * Detail view.
     */
    public function show(int $id)
    {
        $feedbackModel = new FeedbackModel();

        $feedback = $feedbackModel->db->table('feedback f')
            ->select('f.*, s.nama AS nama_sekolah, s.jenjang, s.alamat, bp.nomor_batch, r.nama_menu, d.tanggal_distribusi, d.jumlah_porsi')
            ->join('sekolah s', 's.id = f.sekolah_id', 'left')
            ->join('batch_produksi bp', 'bp.id = f.batch_id', 'left')
            ->join('resep r', 'r.id = bp.resep_id', 'left')
            ->join('distribusi d', 'd.id = f.distribusi_id', 'left')
            ->where('f.id', $id)
            ->get()
            ->getRowArray();

        if (!$feedback) {
            return redirect()->to('/feedback')
                ->with('error', 'Feedback tidak ditemukan.');
        }

        return view('feedback/show', [
            'title'    => 'Detail Umpan Balik – Dapur MBG',
            'feedback' => $feedback,
        ]);
    }
    /**
     * API endpoint: rating per sekolah per bulan (Chart.js JSON)
     */
    public function chartData()
    {
        $db = \Config\Database::connect();

        $months = 6;
        $startDate = date('Y-m-01', strtotime("-" . ($months - 1) . " months"));

        $data = $db->table('feedback f')
            ->select("s.nama as nama_sekolah, DATE_FORMAT(f.tanggal, '%Y-%m') as bulan, AVG(f.rating) as avg_rating, COUNT(f.id) as total")
            ->join('sekolah s', 's.id = f.sekolah_id', 'left')
            ->where('f.tanggal >=', $startDate)
            ->groupBy("f.sekolah_id, DATE_FORMAT(f.tanggal, '%Y-%m')")
            ->orderBy('bulan')
            ->get()->getResultArray();

        return $this->response->setJSON(['success' => true, 'data' => $data]);
    }
}
