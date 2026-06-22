<?php

namespace App\Controllers;

use App\Models\BatchProduksiModel;
use App\Models\BahanBakuModel;
use App\Models\DistribusiModel;
use App\Models\PurchaseOrderModel;
use App\Models\SekolahModel;
use App\Models\FeedbackModel;
use App\Models\FoodWasteModel;

class Dashboard extends BaseController
{
    protected BatchProduksiModel  $batchModel;
    protected BahanBakuModel      $bahanModel;
    protected DistribusiModel     $distribusiModel;
    protected PurchaseOrderModel  $poModel;
    protected SekolahModel        $sekolahModel;
    protected FeedbackModel       $feedbackModel;
    protected FoodWasteModel      $wasteModel;

    public function __construct()
    {
        $this->batchModel     = new BatchProduksiModel();
        $this->bahanModel     = new BahanBakuModel();
        $this->distribusiModel = new DistribusiModel();
        $this->poModel        = new PurchaseOrderModel();
        $this->sekolahModel   = new SekolahModel();
        $this->feedbackModel  = new FeedbackModel();
        $this->wasteModel     = new FoodWasteModel();
    }

    /**
     * Main dashboard page
     */
    public function index()
    {
        $today = date('Y-m-d');

        // ─── KPI Cards ─────────────────────────────────────────────────
        // Total porsi yang diproduksi hari ini (semua batch selesai/sedang masak)
        $totalPorsiHariIni = (int) $this->batchModel
            ->selectSum('porsi_selesai')
            ->where('tanggal_produksi', $today)
            ->whereNotIn('status', ['dibatalkan'])
            ->first()['porsi_selesai'] ?? 0;

        // Jumlah sekolah yang menerima distribusi hari ini
        $distribusiHariIni = $this->distribusiModel->getDistribusiHariIni();
        $sekolahTerlayani  = count(array_unique(array_column($distribusiHariIni, 'sekolah_id')));

        // Stok kritis (stock < minimum)
        $stokKritis     = $this->bahanModel->getStokKritisSimple();
        $totalStokKritis = count($stokKritis);

        // PO pending approval
        $poPending = $this->poModel->countPending();

        // Recent POs
        $recentPO = $this->poModel->db->table('purchase_orders po')
            ->select('po.nomor_po as no_po, s.name as supplier, po.tanggal_po as tgl_po, po.total_nilai as total, po.status')
            ->join('supplier s', 's.id = po.supplier_id', 'left')
            ->orderBy('po.created_at', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        // Batch aktif hari ini (persiapan + sedang_masak)
        $batchAktifHariIni = $this->batchModel
            ->where('tanggal_produksi', $today)
            ->whereIn('status', ['persiapan', 'sedang_masak'])
            ->countAllResults();

        // ─── Chart Data: Tren Produksi 7 Hari Terakhir ─────────────────
        $trenProduksiRaw = $this->batchModel->getTrenProduksi(7);

        // Build a complete 7-day array (fill missing dates with 0)
        $trenLabels = [];
        $trenData   = [];
        $trendMap   = array_column($trenProduksiRaw, null, 'tanggal_produksi');

        for ($i = 6; $i >= 0; $i--) {
            $dateKey      = date('Y-m-d', strtotime("-{$i} days"));
            $labelKey     = date('d M', strtotime("-{$i} days"));
            $trenLabels[] = $labelKey;
            $trenData[]   = isset($trendMap[$dateKey])
                ? (int) $trendMap[$dateKey]['total_porsi']
                : 0;
        }

        // ─── Batch Produksi Aktif Hari Ini (list) ──────────────────────
        $batchHariIni = $this->batchModel->getHariIni();

        // ─── Distribusi Hari Ini ────────────────────────────────────────
        $listDistribusiHariIni = $distribusiHariIni;

        // ─── Top 5 Stok Kritis ─────────────────────────────────────────
        $topStokKritis = array_slice($stokKritis, 0, 5);

        // ─── Average Feedback Rating ────────────────────────────────────
        $avgRatingRow = $this->feedbackModel
            ->selectAvg('rating')
            ->first();
        $avgRating = $avgRatingRow ? round((float) ($avgRatingRow['rating'] ?? 0), 1) : 0;

        // ─── Total Sekolah Aktif ─────────────────────────────────────────
        $totalSekolah = $this->sekolahModel->where('is_active', 1)->countAllResults();

        // ─── Waste Bulan Ini ─────────────────────────────────────────────
        $wasteNilai = $this->wasteModel->getTotalNilai(
            date('Y-m-01'),
            date('Y-m-t')
        );

        return view('dashboard/index', [
            'title'                  => 'Dashboard',
            'totalPorsiHariIni'      => $totalPorsiHariIni,
            'sekolahTerlayani'       => $sekolahTerlayani,
            'totalStokKritis'        => $totalStokKritis,
            'poPending'              => $poPending,
            'batchAktifHariIni'      => $batchAktifHariIni,
            'trenLabels'             => json_encode($trenLabels),
            'trenData'               => json_encode($trenData),
            'batchHariIni'           => $batchHariIni,
            'listDistribusiHariIni'  => $listDistribusiHariIni,
            'recentPO'               => $recentPO,
            'topStokKritis'          => $topStokKritis,
            'avgRating'              => $avgRating,
            'totalSekolah'           => $totalSekolah,
            'wasteNilai'             => $wasteNilai,
            'today'                  => $today,
        ]);
    }
}
