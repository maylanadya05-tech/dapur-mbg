<?php

namespace App\Controllers;

use App\Models\BahanBakuModel;
use CodeIgniter\HTTP\ResponseInterface;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Laporan extends BaseController
{
    /**
     * Show report dashboard with filter selection and overall KPIs.
     */
    public function index(): string
    {
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate   = $this->request->getGet('end_date') ?: date('Y-m-t');

        $db = \Config\Database::connect();
        
        // Portions completed in production
        $prod = $db->table('batch_produksi')
            ->selectSum('porsi_selesai')
            ->where('tanggal_produksi >=', $startDate)
            ->where('tanggal_produksi <=', $endDate)
            ->get()->getRowArray();
        
        // Portions successfully received by schools
        $dist = $db->table('distribusi')
            ->selectSum('jumlah_porsi')
            ->where('tanggal_distribusi >=', $startDate)
            ->where('tanggal_distribusi <=', $endDate)
            ->where('status', 'diterima')
            ->get()->getRowArray();
        
        // Value of food waste
        $waste = $db->table('food_waste')
            ->selectSum('estimasi_nilai')
            ->where('tanggal >=', $startDate)
            ->where('tanggal <=', $endDate)
            ->get()->getRowArray();

        // Critical stock count
        $bahanBakuModel = new BahanBakuModel();
        $stokKritisCount = count($bahanBakuModel->getStokKritisSimple());

        return view('laporan/index', [
            'title'      => 'Laporan & Analisis KPI – Dapur MBG',
            'startDate'  => $startDate,
            'endDate'    => $endDate,
            'totalProd'  => (int)($prod['porsi_selesai'] ?? 0),
            'totalDist'  => (int)($dist['jumlah_porsi'] ?? 0),
            'totalWaste' => (float)($waste['estimasi_nilai'] ?? 0.0),
            'stokKritis' => $stokKritisCount,
        ]);
    }

    /**
     * Gathers production metrics and data.
     */
    public function produksi(): string
    {
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate   = $this->request->getGet('end_date') ?: date('Y-m-t');

        $db = \Config\Database::connect();

        $batches = $db->table('batch_produksi bp')
            ->select('bp.*, r.nama_menu')
            ->join('resep r', 'r.id = bp.resep_id', 'left')
            ->where('bp.tanggal_produksi >=', $startDate)
            ->where('bp.tanggal_produksi <=', $endDate)
            ->orderBy('bp.tanggal_produksi', 'ASC')
            ->get()->getResultArray();

        $summary = $db->table('batch_produksi')
            ->select('status, COUNT(id) as total_batches, SUM(target_porsi) as total_target, SUM(porsi_selesai) as total_selesai')
            ->where('tanggal_produksi >=', $startDate)
            ->where('tanggal_produksi <=', $endDate)
            ->groupBy('status')
            ->get()->getResultArray();

        return view('laporan/produksi', [
            'title'     => 'Laporan Produksi Makanan – Dapur MBG',
            'startDate' => $startDate,
            'endDate'   => $endDate,
            'batches'   => $batches,
            'summary'   => $summary,
        ]);
    }

    /**
     * Gathers distribution metrics and data.
     */
    public function distribusi(): string
    {
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate   = $this->request->getGet('end_date') ?: date('Y-m-t');

        $db = \Config\Database::connect();

        $distributions = $db->table('distribusi d')
            ->select('d.*, s.nama AS nama_sekolah, bp.nomor_batch')
            ->join('sekolah s', 's.id = d.sekolah_id', 'left')
            ->join('batch_produksi bp', 'bp.id = d.batch_id', 'left')
            ->where('d.tanggal_distribusi >=', $startDate)
            ->where('d.tanggal_distribusi <=', $endDate)
            ->orderBy('d.tanggal_distribusi', 'ASC')
            ->get()->getResultArray();

        $statusBreakdown = $db->table('distribusi')
            ->select('status, COUNT(id) as count, SUM(jumlah_porsi) as total_porsi')
            ->where('tanggal_distribusi >=', $startDate)
            ->where('tanggal_distribusi <=', $endDate)
            ->groupBy('status')
            ->get()->getResultArray();

        $schoolBreakdown = $db->table('distribusi d')
            ->select('s.nama as nama_sekolah, COUNT(d.id) as total_deliveries, SUM(d.jumlah_porsi) as total_porsi')
            ->join('sekolah s', 's.id = d.sekolah_id', 'left')
            ->where('d.tanggal_distribusi >=', $startDate)
            ->where('d.tanggal_distribusi <=', $endDate)
            ->where('d.status', 'diterima')
            ->groupBy('d.sekolah_id')
            ->get()->getResultArray();

        return view('laporan/distribusi', [
            'title'           => 'Laporan Distribusi Makanan – Dapur MBG',
            'startDate'       => $startDate,
            'endDate'         => $endDate,
            'distributions'   => $distributions,
            'statusBreakdown' => $statusBreakdown,
            'schoolBreakdown' => $schoolBreakdown,
        ]);
    }

    /**
     * Gathers stock movement report.
     */
    public function stok(): string
    {
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate   = $this->request->getGet('end_date') ?: date('Y-m-t');

        $db = \Config\Database::connect();
        
        $movements = $db->table('stok_gudang sg')
            ->select('sg.*, bb.nama as nama_bahan, bb.satuan, bb.kode, u.name as nama_user')
            ->join('bahan_baku bb', 'bb.id = sg.bahan_baku_id', 'left')
            ->join('users u', 'u.id = sg.created_by', 'left')
            ->where('sg.tanggal >=', $startDate)
            ->where('sg.tanggal <=', $endDate)
            ->orderBy('sg.tanggal', 'DESC')
            ->get()->getResultArray();

        $summary = $db->table('stok_gudang sg')
            ->select('bb.kode, bb.nama as nama_bahan, bb.satuan, SUM(sg.stok_masuk) as total_masuk, SUM(sg.stok_keluar) as total_keluar')
            ->join('bahan_baku bb', 'bb.id = sg.bahan_baku_id')
            ->where('sg.tanggal >=', $startDate)
            ->where('sg.tanggal <=', $endDate)
            ->groupBy('sg.bahan_baku_id')
            ->get()->getResultArray();

        return view('laporan/stok', [
            'title'     => 'Laporan Pergerakan Stok – Dapur MBG',
            'startDate' => $startDate,
            'endDate'   => $endDate,
            'movements' => $movements,
            'summary'   => $summary,
        ]);
    }

    /**
     * Gathers food waste analysis.
     */
    public function waste(): string
    {
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate   = $this->request->getGet('end_date') ?: date('Y-m-t');

        $db = \Config\Database::connect();

        $wasteLogs = $db->table('food_waste fw')
            ->select('fw.*, bp.nomor_batch, bb.nama as nama_bahan, u.name as dicatat_oleh_name')
            ->join('batch_produksi bp', 'bp.id = fw.batch_id', 'left')
            ->join('bahan_baku bb', 'bb.id = fw.bahan_baku_id', 'left')
            ->join('users u', 'u.id = fw.dicatat_oleh', 'left')
            ->where('fw.tanggal >=', $startDate)
            ->where('fw.tanggal <=', $endDate)
            ->orderBy('fw.tanggal', 'DESC')
            ->get()->getResultArray();

        $categorySummary = $db->table('food_waste')
            ->select('kategori, SUM(qty) as total_qty, SUM(estimasi_nilai) as total_nilai, COUNT(id) as total_records')
            ->where('tanggal >=', $startDate)
            ->where('tanggal <=', $endDate)
            ->groupBy('kategori')
            ->get()->getResultArray();

        $supplierSummary = $db->table('food_waste fw')
            ->select('s.name as nama_supplier, COUNT(fw.id) as total_records, SUM(fw.estimasi_nilai) as total_nilai')
            ->join('bahan_baku bb', 'bb.id = fw.bahan_baku_id')
            ->join('supplier s', 's.id = bb.supplier_id')
            ->where('fw.tanggal >=', $startDate)
            ->where('fw.tanggal <=', $endDate)
            ->groupBy('bb.supplier_id')
            ->get()->getResultArray();

        return view('laporan/waste', [
            'title'           => 'Laporan Analisis Food Waste – Dapur MBG',
            'startDate'       => $startDate,
            'endDate'         => $endDate,
            'wasteLogs'       => $wasteLogs,
            'categorySummary' => $categorySummary,
            'supplierSummary' => $supplierSummary,
        ]);
    }

    /**
     * Exports filtered report to PDF.
     */
    public function exportPdf()
    {
        $type = $this->request->getGet('type') ?: 'produksi';
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate   = $this->request->getGet('end_date') ?: date('Y-m-t');

        $db = \Config\Database::connect();
        $data = [];

        if ($type === 'produksi') {
            $data = $db->table('batch_produksi bp')
                ->select('bp.*, r.nama_menu')
                ->join('resep r', 'r.id = bp.resep_id', 'left')
                ->where('bp.tanggal_produksi >=', $startDate)
                ->where('bp.tanggal_produksi <=', $endDate)
                ->orderBy('bp.tanggal_produksi', 'ASC')
                ->get()->getResultArray();
        } elseif ($type === 'distribusi') {
            $data = $db->table('distribusi d')
                ->select('d.*, s.nama AS nama_sekolah, bp.nomor_batch')
                ->join('sekolah s', 's.id = d.sekolah_id', 'left')
                ->join('batch_produksi bp', 'bp.id = d.batch_id', 'left')
                ->where('d.tanggal_distribusi >=', $startDate)
                ->where('d.tanggal_distribusi <=', $endDate)
                ->orderBy('d.tanggal_distribusi', 'ASC')
                ->get()->getResultArray();
        } elseif ($type === 'stok') {
            $data = $db->table('stok_gudang sg')
                ->select('sg.*, bb.nama as nama_bahan, bb.satuan, bb.kode, u.name as nama_user')
                ->join('bahan_baku bb', 'bb.id = sg.bahan_baku_id', 'left')
                ->join('users u', 'u.id = sg.created_by', 'left')
                ->where('sg.tanggal >=', $startDate)
                ->where('sg.tanggal <=', $endDate)
                ->orderBy('sg.tanggal', 'DESC')
                ->get()->getResultArray();
        } elseif ($type === 'waste') {
            $data = $db->table('food_waste fw')
                ->select('fw.*, bp.nomor_batch, bb.nama as nama_bahan')
                ->join('batch_produksi bp', 'bp.id = fw.batch_id', 'left')
                ->join('bahan_baku bb', 'bb.id = fw.bahan_baku_id', 'left')
                ->where('fw.tanggal >=', $startDate)
                ->where('fw.tanggal <=', $endDate)
                ->orderBy('fw.tanggal', 'DESC')
                ->get()->getResultArray();
        }

        $html = $this->generatePdfHtml($type, $startDate, $endDate, $data);

        $dompdf = new Dompdf([
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true,
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="Laporan-' . ucfirst($type) . '-' . $startDate . '-to-' . $endDate . '.pdf"')
            ->setBody($dompdf->output());
    }

    /**
     * Exports filtered report to Excel using PhpSpreadsheet.
     */
    public function exportExcel()
    {
        $type = $this->request->getGet('type') ?: 'produksi';
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate   = $this->request->getGet('end_date') ?: date('Y-m-t');

        $db = \Config\Database::connect();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(ucfirst($type));

        // Set Report Headers
        $sheet->setCellValue('A1', 'DAPUR MBG – SPPG SYSTEM');
        $sheet->setCellValue('A2', 'LAPORAN ' . strtoupper($type));
        $sheet->setCellValue('A3', 'Periode Laporan: ' . date('d M Y', strtotime($startDate)) . ' s/d ' . date('d M Y', strtotime($endDate)));

        if ($type === 'produksi') {
            $data = $db->table('batch_produksi bp')
                ->select('bp.nomor_batch, bp.tanggal_produksi, r.nama_menu, bp.target_porsi, bp.porsi_selesai, bp.tim_produksi, bp.status')
                ->join('resep r', 'r.id = bp.resep_id', 'left')
                ->where('bp.tanggal_produksi >=', $startDate)
                ->where('bp.tanggal_produksi <=', $endDate)
                ->orderBy('bp.tanggal_produksi', 'ASC')
                ->get()->getResultArray();

            $headers = ['Nomor Batch', 'Tanggal Produksi', 'Nama Menu', 'Target Porsi', 'Porsi Selesai', 'Tim Produksi', 'Status'];
            
            $col = 'A';
            foreach ($headers as $h) {
                $sheet->setCellValue($col . '5', $h);
                $col++;
            }

            $rowNum = 6;
            foreach ($data as $row) {
                $sheet->setCellValue('A' . $rowNum, $row['nomor_batch']);
                $sheet->setCellValue('B' . $rowNum, $row['tanggal_produksi']);
                $sheet->setCellValue('C' . $rowNum, $row['nama_menu']);
                $sheet->setCellValue('D' . $rowNum, $row['target_porsi']);
                $sheet->setCellValue('E' . $rowNum, $row['porsi_selesai']);
                $sheet->setCellValue('F' . $rowNum, $row['tim_produksi']);
                $sheet->setCellValue('G' . $rowNum, $row['status']);
                $rowNum++;
            }
        } elseif ($type === 'distribusi') {
            $data = $db->table('distribusi d')
                ->select('d.tanggal_distribusi, bp.nomor_batch, s.nama AS nama_sekolah, d.jumlah_porsi, d.pengirim, d.penerima, d.waktu_terima, d.status')
                ->join('sekolah s', 's.id = d.sekolah_id', 'left')
                ->join('batch_produksi bp', 'bp.id = d.batch_id', 'left')
                ->where('d.tanggal_distribusi >=', $startDate)
                ->where('d.tanggal_distribusi <=', $endDate)
                ->orderBy('d.tanggal_distribusi', 'ASC')
                ->get()->getResultArray();

            $headers = ['Tanggal Distribusi', 'Nomor Batch', 'Sekolah Penerima', 'Porsi', 'Pengirim', 'Penerima', 'Waktu Terima', 'Status'];
            
            $col = 'A';
            foreach ($headers as $h) {
                $sheet->setCellValue($col . '5', $h);
                $col++;
            }

            $rowNum = 6;
            foreach ($data as $row) {
                $sheet->setCellValue('A' . $rowNum, $row['tanggal_distribusi']);
                $sheet->setCellValue('B' . $rowNum, $row['nomor_batch']);
                $sheet->setCellValue('C' . $rowNum, $row['nama_sekolah']);
                $sheet->setCellValue('D' . $rowNum, $row['jumlah_porsi']);
                $sheet->setCellValue('E' . $rowNum, $row['pengirim']);
                $sheet->setCellValue('F' . $rowNum, $row['penerima']);
                $sheet->setCellValue('G' . $rowNum, $row['waktu_terima']);
                $sheet->setCellValue('H' . $rowNum, $row['status']);
                $rowNum++;
            }
        } elseif ($type === 'stok') {
            $data = $db->table('stok_gudang sg')
                ->select('sg.tanggal, bb.kode, bb.nama as nama_bahan, bb.satuan, sg.stok_masuk, sg.stok_keluar, sg.keterangan, u.name as nama_user')
                ->join('bahan_baku bb', 'bb.id = sg.bahan_baku_id', 'left')
                ->join('users u', 'u.id = sg.created_by', 'left')
                ->where('sg.tanggal >=', $startDate)
                ->where('sg.tanggal <=', $endDate)
                ->orderBy('sg.tanggal', 'DESC')
                ->get()->getResultArray();

            $headers = ['Tanggal', 'Kode Bahan', 'Nama Bahan', 'Satuan', 'Stok Masuk', 'Stok Keluar', 'Keterangan', 'Petugas'];
            
            $col = 'A';
            foreach ($headers as $h) {
                $sheet->setCellValue($col . '5', $h);
                $col++;
            }

            $rowNum = 6;
            foreach ($data as $row) {
                $sheet->setCellValue('A' . $rowNum, $row['tanggal']);
                $sheet->setCellValue('B' . $rowNum, $row['kode']);
                $sheet->setCellValue('C' . $rowNum, $row['nama_bahan']);
                $sheet->setCellValue('D' . $rowNum, $row['satuan']);
                $sheet->setCellValue('E' . $rowNum, $row['stok_masuk']);
                $sheet->setCellValue('F' . $rowNum, $row['stok_keluar']);
                $sheet->setCellValue('G' . $rowNum, $row['keterangan']);
                $sheet->setCellValue('H' . $rowNum, $row['nama_user']);
                $rowNum++;
            }
        } elseif ($type === 'waste') {
            $data = $db->table('food_waste fw')
                ->select('fw.tanggal, fw.kategori, bp.nomor_batch, bb.nama as nama_bahan, fw.qty, fw.satuan, fw.estimasi_nilai, fw.keterangan')
                ->join('batch_produksi bp', 'bp.id = fw.batch_id', 'left')
                ->join('bahan_baku bb', 'bb.id = fw.bahan_baku_id', 'left')
                ->where('fw.tanggal >=', $startDate)
                ->where('fw.tanggal <=', $endDate)
                ->orderBy('fw.tanggal', 'DESC')
                ->get()->getResultArray();

            $headers = ['Tanggal', 'Kategori', 'Nomor Batch', 'Bahan Baku', 'Qty', 'Satuan', 'Estimasi Nilai (Rp)', 'Keterangan'];
            
            $col = 'A';
            foreach ($headers as $h) {
                $sheet->setCellValue($col . '5', $h);
                $col++;
            }

            $rowNum = 6;
            foreach ($data as $row) {
                $sheet->setCellValue('A' . $rowNum, $row['tanggal']);
                $sheet->setCellValue('B' . $rowNum, $row['kategori']);
                $sheet->setCellValue('C' . $rowNum, $row['nomor_batch']);
                $sheet->setCellValue('D' . $rowNum, $row['nama_bahan']);
                $sheet->setCellValue('E' . $rowNum, $row['qty']);
                $sheet->setCellValue('F' . $rowNum, $row['satuan']);
                $sheet->setCellValue('G' . $rowNum, $row['estimasi_nilai']);
                $sheet->setCellValue('H' . $rowNum, $row['keterangan']);
                $rowNum++;
            }
        }

        $writer = new Xlsx($spreadsheet);
        
        ob_start();
        $writer->save('php://output');
        $excelData = ob_get_clean();

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="Laporan-' . ucfirst($type) . '-' . $startDate . '-to-' . $endDate . '.xlsx"')
            ->setBody($excelData);
    }

    /**
     * Self-contained PDF HTML helper to style landscape tables beautifully.
     */
    private function generatePdfHtml(string $type, string $startDate, string $endDate, array $data): string
    {
        $title = 'Laporan ' . ucfirst($type);
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>' . $title . '</title>
            <style>
                body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
                .title { font-size: 18px; color: #0d9488; font-weight: bold; margin-bottom: 3px; }
                .subtitle { font-size: 11px; color: #4b5563; margin-bottom: 15px; }
                table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                th { background-color: #0d9488; color: white; padding: 7px; border: 1px solid #d1d5db; text-align: left; font-weight: bold; }
                td { padding: 6px; border: 1px solid #e5e7eb; }
                .text-right { text-align: right; }
                .text-center { text-align: center; }
                .footer { margin-top: 40px; text-align: center; font-size: 9px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 10px; }
            </style>
        </head>
        <body>
            <div class="title">DAPUR MBG – SPPG SYSTEM</div>
            <div class="title" style="font-size:14px; color:#4b5563;">' . strtoupper($title) . '</div>
            <div class="subtitle">Periode Laporan: ' . date('d M Y', strtotime($startDate)) . ' s/d ' . date('d M Y', strtotime($endDate)) . '</div>
            <table>';

        if ($type === 'produksi') {
            $html .= '
                <thead>
                    <tr>
                        <th style="width: 4%;">No</th>
                        <th style="width: 16%;">Nomor Batch</th>
                        <th style="width: 12%;">Tanggal Produksi</th>
                        <th style="width: 25%;">Nama Menu</th>
                        <th style="width: 10%;" class="text-right">Target Porsi</th>
                        <th style="width: 10%;" class="text-right">Porsi Selesai</th>
                        <th style="width: 13%;">Tim Produksi</th>
                        <th style="width: 10%;">Status</th>
                    </tr>
                </thead>
                <tbody>';
            $no = 1;
            foreach ($data as $row) {
                $html .= '
                    <tr>
                        <td class="text-center">' . $no++ . '</td>
                        <td>' . $row['nomor_batch'] . '</td>
                        <td>' . date('d/m/Y', strtotime($row['tanggal_produksi'])) . '</td>
                        <td>' . $row['nama_menu'] . '</td>
                        <td class="text-right">' . number_format($row['target_porsi']) . '</td>
                        <td class="text-right">' . number_format($row['porsi_selesai']) . '</td>
                        <td>' . $row['tim_produksi'] . '</td>
                        <td>' . ucfirst($row['status']) . '</td>
                    </tr>';
            }
        } elseif ($type === 'distribusi') {
            $html .= '
                <thead>
                    <tr>
                        <th style="width: 4%;">No</th>
                        <th style="width: 12%;">Tanggal</th>
                        <th style="width: 15%;">Nomor Batch</th>
                        <th style="width: 22%;">Sekolah Penerima</th>
                        <th style="width: 8%;" class="text-right">Porsi</th>
                        <th style="width: 12%;">Pengirim</th>
                        <th style="width: 12%;">Penerima</th>
                        <th style="width: 10%;">Waktu Terima</th>
                        <th style="width: 8%;">Status</th>
                    </tr>
                </thead>
                <tbody>';
            $no = 1;
            foreach ($data as $row) {
                $html .= '
                    <tr>
                        <td class="text-center">' . $no++ . '</td>
                        <td>' . date('d/m/Y', strtotime($row['tanggal_distribusi'])) . '</td>
                        <td>' . $row['nomor_batch'] . '</td>
                        <td>' . $row['nama_sekolah'] . '</td>
                        <td class="text-right">' . number_format($row['jumlah_porsi']) . '</td>
                        <td>' . $row['pengirim'] . '</td>
                        <td>' . ($row['penerima'] ?: '-') . '</td>
                        <td>' . ($row['waktu_terima'] ? date('d/m/Y H:i', strtotime($row['waktu_terima'])) : '-') . '</td>
                        <td>' . ucfirst($row['status']) . '</td>
                    </tr>';
            }
        } elseif ($type === 'stok') {
            $html .= '
                <thead>
                    <tr>
                        <th style="width: 4%;">No</th>
                        <th style="width: 10%;">Tanggal</th>
                        <th style="width: 12%;">Kode Bahan</th>
                        <th style="width: 20%;">Nama Bahan</th>
                        <th style="width: 8%;">Satuan</th>
                        <th style="width: 10%;" class="text-right">Stok Masuk</th>
                        <th style="width: 10%;" class="text-right">Stok Keluar</th>
                        <th style="width: 16%;">Keterangan</th>
                        <th style="width: 10%;">Petugas</th>
                    </tr>
                </thead>
                <tbody>';
            $no = 1;
            foreach ($data as $row) {
                $html .= '
                    <tr>
                        <td class="text-center">' . $no++ . '</td>
                        <td>' . date('d/m/Y', strtotime($row['tanggal'])) . '</td>
                        <td>' . $row['kode'] . '</td>
                        <td>' . $row['nama_bahan'] . '</td>
                        <td>' . $row['satuan'] . '</td>
                        <td class="text-right">' . number_format($row['stok_masuk'], 2) . '</td>
                        <td class="text-right">' . number_format($row['stok_keluar'], 2) . '</td>
                        <td>' . $row['keterangan'] . '</td>
                        <td>' . $row['nama_user'] . '</td>
                    </tr>';
            }
        } elseif ($type === 'waste') {
            $html .= '
                <thead>
                    <tr>
                        <th style="width: 4%;">No</th>
                        <th style="width: 10%;">Tanggal</th>
                        <th style="width: 12%;">Kategori</th>
                        <th style="width: 14%;">Nomor Batch</th>
                        <th style="width: 16%;">Bahan Baku</th>
                        <th style="width: 8%;" class="text-right">Qty</th>
                        <th style="width: 8%;">Satuan</th>
                        <th style="width: 12%;" class="text-right">Estimasi Nilai</th>
                        <th style="width: 16%;">Keterangan</th>
                    </tr>
                </thead>
                <tbody>';
            $no = 1;
            foreach ($data as $row) {
                $html .= '
                    <tr>
                        <td class="text-center">' . $no++ . '</td>
                        <td>' . date('d/m/Y', strtotime($row['tanggal'])) . '</td>
                        <td>' . ucfirst(str_replace('_', ' ', $row['kategori'])) . '</td>
                        <td>' . ($row['nomor_batch'] ?: '-') . '</td>
                        <td>' . ($row['nama_bahan'] ?: '-') . '</td>
                        <td class="text-right">' . number_format($row['qty'], 2) . '</td>
                        <td>' . $row['satuan'] . '</td>
                        <td class="text-right">Rp ' . number_format($row['estimasi_nilai'], 2, ',', '.') . '</td>
                        <td>' . $row['keterangan'] . '</td>
                    </tr>';
            }
        }

        if (empty($data)) {
            $html .= '<tr><td colspan="10" style="text-align:center; padding: 20px;">Tidak ada data ditemukan untuk filter periode ini.</td></tr>';
        }

        $html .= '
                </tbody>
            </table>
            <div class="footer">
                Dokumen Laporan digenerate dari Aplikasi SPPG Dapur MBG pada: ' . date('d/m/Y H:i:s') . '
            </div>
        </body>
        </html>';

        return $html;
    }

    /**
     * Laporan keuangan terintegrasi (pembelian, produksi, waste, invoice)
     */
    public function keuangan(): string
    {
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate   = $this->request->getGet('end_date')   ?: date('Y-m-t');

        $db = \Config\Database::connect();

        // Total nilai pembelian (PO yang disetujui)
        $totalPembelian = $db->table('purchase_orders')
            ->selectSum('total_nilai')
            ->where('status', 'disetujui')
            ->where('tanggal_po >=', $startDate)
            ->where('tanggal_po <=', $endDate)
            ->get()->getRowArray();

        // Total nilai invoice yang lunas
        $totalInvoice = $db->table('invoice')
            ->selectSum('total_nilai')
            ->where('status', 'lunas')
            ->where('tanggal >=', $startDate)
            ->where('tanggal <=', $endDate)
            ->get()->getRowArray();

        // Total nilai food waste
        $totalWaste = $db->table('food_waste')
            ->selectSum('estimasi_nilai')
            ->where('tanggal >=', $startDate)
            ->where('tanggal <=', $endDate)
            ->get()->getRowArray();

        // Biaya produksi (porsi selesai × HPP rata-rata — approximasi)
        $totalPorsi = $db->table('batch_produksi')
            ->selectSum('porsi_selesai')
            ->where('status', 'selesai')
            ->where('tanggal_produksi >=', $startDate)
            ->where('tanggal_produksi <=', $endDate)
            ->get()->getRowArray();

        // Monthly trend: pembelian per bulan
        $trendPembelian = $db->table('purchase_orders')
            ->select("DATE_FORMAT(tanggal_po, '%Y-%m') as bulan, SUM(total_nilai) as total")
            ->where('status', 'disetujui')
            ->where('tanggal_po >=', date('Y-m-01', strtotime('-5 months')))
            ->groupBy("DATE_FORMAT(tanggal_po, '%Y-%m')")
            ->orderBy('bulan')
            ->get()->getResultArray();

        // Monthly trend: waste per bulan
        $trendWaste = $db->table('food_waste')
            ->select("DATE_FORMAT(tanggal, '%Y-%m') as bulan, SUM(estimasi_nilai) as total")
            ->where('tanggal >=', date('Y-m-01', strtotime('-5 months')))
            ->groupBy("DATE_FORMAT(tanggal, '%Y-%m')")
            ->orderBy('bulan')
            ->get()->getResultArray();

        // Top 5 supplier by pembelian value
        $topSupplier = $db->table('purchase_orders po')
            ->select('s.name as nama_supplier, SUM(po.total_nilai) as total_nilai, COUNT(po.id) as total_po')
            ->join('supplier s', 's.id = po.supplier_id', 'left')
            ->where('po.status', 'disetujui')
            ->where('po.tanggal_po >=', $startDate)
            ->where('po.tanggal_po <=', $endDate)
            ->groupBy('po.supplier_id')
            ->orderBy('total_nilai', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        return view('laporan/keuangan', [
            'title'          => 'Laporan Keuangan – Dapur MBG',
            'startDate'      => $startDate,
            'endDate'        => $endDate,
            'totalPembelian' => (float)($totalPembelian['total_nilai'] ?? 0),
            'totalInvoice'   => (float)($totalInvoice['total_nilai'] ?? 0),
            'totalWaste'     => (float)($totalWaste['estimasi_nilai'] ?? 0),
            'totalPorsi'     => (int)($totalPorsi['porsi_selesai'] ?? 0),
            'trendPembelian' => $trendPembelian,
            'trendWaste'     => $trendWaste,
            'topSupplier'    => $topSupplier,
        ]);
    }
}
