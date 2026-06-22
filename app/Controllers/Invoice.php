<?php

namespace App\Controllers;

use App\Models\InvoiceModel;
use App\Models\DistribusiModel;
use CodeIgniter\HTTP\ResponseInterface;
use Dompdf\Dompdf;

class Invoice extends BaseController
{
    /**
     * List invoices and payment statuses.
     */
    public function index(): string
    {
        $invoiceModel = new InvoiceModel();
        
        // Auto update status of overdue invoices
        $invoiceModel->updateJatuhTempo();

        $invoices = $invoiceModel->getWithRelations();

        return view('invoice/index', [
            'title'    => 'Tagihan & Invoice – Dapur MBG',
            'invoices' => $invoices,
        ]);
    }

    /**
     * Form to generate invoice.
     * Must query the total portions successfully distributed ('diterima')
     * to schools within a date range and calculate total amount.
     */
    public function create(): string
    {
        $periodeDari   = $this->request->getGet('periode_dari');
        $periodeSampai = $this->request->getGet('periode_sampai');
        $hargaPerPorsi = $this->request->getGet('harga_per_porsi') !== null ? (float)$this->request->getGet('harga_per_porsi') : 15000.00;

        $totalPorsi = 0;
        $totalNilai = 0.0;
        $details = [];

        if ($periodeDari && $periodeSampai) {
            $distribusiModel = new DistribusiModel();
            
            // Query total portions successfully distributed ('diterima')
            $result = $distribusiModel->selectSum('jumlah_porsi')
                ->where('tanggal_distribusi >=', $periodeDari)
                ->where('tanggal_distribusi <=', $periodeSampai)
                ->where('status', 'diterima')
                ->first();

            $totalPorsi = (int)($result['jumlah_porsi'] ?? 0);
            $totalNilai = $totalPorsi * $hargaPerPorsi;

            // Details of successful distributions in that period
            $details = $distribusiModel->db->table('distribusi d')
                ->select('d.*, s.nama AS nama_sekolah, s.jenjang')
                ->join('sekolah s', 's.id = d.sekolah_id', 'left')
                ->where('d.tanggal_distribusi >=', $periodeDari)
                ->where('d.tanggal_distribusi <=', $periodeSampai)
                ->where('d.status', 'diterima')
                ->orderBy('d.tanggal_distribusi', 'ASC')
                ->get()
                ->getResultArray();
        }

        return view('invoice/create', [
            'title'         => 'Buat Invoice Baru – Dapur MBG',
            'periodeDari'   => $periodeDari,
            'periodeSampai' => $periodeSampai,
            'hargaPerPorsi' => $hargaPerPorsi,
            'totalPorsi'    => $totalPorsi,
            'totalNilai'    => $totalNilai,
            'details'       => $details,
        ]);
    }

    /**
     * Save invoice header (auto-generates INV-YYYY-001 format).
     */
    public function store()
    {
        $invoiceModel = new InvoiceModel();
        $distribusiModel = new DistribusiModel();

        $periodeDari   = $this->request->getPost('periode_dari');
        $periodeSampai = $this->request->getPost('periode_sampai');
        $hargaPerPorsi = (float)$this->request->getPost('harga_per_porsi');

        // Query total portions successfully distributed ('diterima')
        $result = $distribusiModel->selectSum('jumlah_porsi')
            ->where('tanggal_distribusi >=', $periodeDari)
            ->where('tanggal_distribusi <=', $periodeSampai)
            ->where('status', 'diterima')
            ->first();

        $totalPorsi = (int)($result['jumlah_porsi'] ?? 0);
        $totalNilai = $totalPorsi * $hargaPerPorsi;

        // Auto-generate invoice number format (INV-YYYY-NNN)
        $nomorInvoice = $invoiceModel->generateNomorInvoice();

        $data = [
            'nomor_invoice'   => $nomorInvoice,
            'tanggal'         => $this->request->getPost('tanggal') ?: date('Y-m-d'),
            'jatuh_tempo'     => $this->request->getPost('jatuh_tempo') ?: date('Y-m-d', strtotime('+14 days')),
            'periode_dari'    => $periodeDari,
            'periode_sampai'  => $periodeSampai,
            'total_porsi'     => $totalPorsi,
            'harga_per_porsi' => $hargaPerPorsi,
            'total_nilai'     => $totalNilai,
            'status'          => 'draft',
            'catatan'         => $this->request->getPost('catatan'),
            'dibuat_oleh'     => session()->get('user_id') ?: 1,
        ];

        if (!$this->validate($invoiceModel->validationRules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        if (!$invoiceModel->insert($data)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $invoiceModel->errors());
        }

        return redirect()->to('/invoice')
            ->with('success', 'Invoice #' . $nomorInvoice . ' berhasil dibuat.');
    }

    /**
     * Detail view of invoice.
     */
    public function show(int $id)
    {
        $invoiceModel = new InvoiceModel();
        $distribusiModel = new DistribusiModel();

        $invoice = $invoiceModel->db->table('invoice inv')
            ->select('inv.*, u.name AS dibuat_oleh_name')
            ->join('users u', 'u.id = inv.dibuat_oleh', 'left')
            ->where('inv.id', $id)
            ->get()
            ->getRowArray();

        if (!$invoice) {
            return redirect()->to('/invoice')
                ->with('error', 'Invoice tidak ditemukan.');
        }

        $details = $distribusiModel->db->table('distribusi d')
            ->select('d.*, s.nama AS nama_sekolah, s.jenjang')
            ->join('sekolah s', 's.id = d.sekolah_id', 'left')
            ->where('d.tanggal_distribusi >=', $invoice['periode_dari'])
            ->where('d.tanggal_distribusi <=', $invoice['periode_sampai'])
            ->where('d.status', 'diterima')
            ->orderBy('d.tanggal_distribusi', 'ASC')
            ->get()
            ->getResultArray();

        return view('invoice/show', [
            'title'   => 'Detail Invoice – Dapur MBG',
            'invoice' => $invoice,
            'details' => $details,
        ]);
    }

    /**
     * Mark as dikirim, dibayar, or jatuh_tempo.
     */
    public function updateStatus(int $id)
    {
        $invoiceModel = new InvoiceModel();
        $invoice = $invoiceModel->find($id);

        if (!$invoice) {
            return redirect()->to('/invoice')
                ->with('error', 'Invoice tidak ditemukan.');
        }

        $newStatus = $this->request->getPost('status');
        $validStatuses = ['draft', 'dikirim', 'dibayar', 'jatuh_tempo'];

        if (!in_array($newStatus, $validStatuses)) {
            return redirect()->back()->with('error', 'Status invoice tidak valid.');
        }

        if (!$invoiceModel->update($id, ['status' => $newStatus])) {
            return redirect()->back()->with('errors', $invoiceModel->errors());
        }

        return redirect()->to('/invoice/show/' . $id)
            ->with('success', 'Status Invoice #' . $invoice['nomor_invoice'] . ' berhasil diubah.');
    }

    /**
     * Generate clean invoice PDF using DomPDF.
     */
    public function exportPdf(int $id)
    {
        $invoiceModel = new InvoiceModel();
        $distribusiModel = new DistribusiModel();

        $invoice = $invoiceModel->db->table('invoice inv')
            ->select('inv.*, u.name AS dibuat_oleh_name')
            ->join('users u', 'u.id = inv.dibuat_oleh', 'left')
            ->where('inv.id', $id)
            ->get()
            ->getRowArray();

        if (!$invoice) {
            return redirect()->to('/invoice')
                ->with('error', 'Invoice tidak ditemukan.');
        }

        $details = $distribusiModel->db->table('distribusi d')
            ->select('d.*, s.nama AS nama_sekolah, s.jenjang')
            ->join('sekolah s', 's.id = d.sekolah_id', 'left')
            ->where('d.tanggal_distribusi >=', $invoice['periode_dari'])
            ->where('d.tanggal_distribusi <=', $invoice['periode_sampai'])
            ->where('d.status', 'diterima')
            ->orderBy('d.tanggal_distribusi', 'ASC')
            ->get()
            ->getResultArray();

        // PDF HTML Layout with professional styling
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Invoice ' . $invoice['nomor_invoice'] . '</title>
            <style>
                body {
                    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
                    color: #333;
                    font-size: 13px;
                    line-height: 1.5;
                }
                .invoice-box {
                    max-width: 800px;
                    margin: auto;
                    padding: 30px;
                }
                .header-table, .details-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                }
                .header-table td {
                    vertical-align: top;
                }
                .title {
                    font-size: 24px;
                    color: #0d9488;
                    font-weight: bold;
                }
                .text-right {
                    text-align: right;
                }
                .meta-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                    margin-bottom: 20px;
                }
                .meta-table th {
                    background-color: #f3f4f6;
                    padding: 8px;
                    border: 1px solid #e5e7eb;
                    text-align: left;
                }
                .meta-table td {
                    padding: 8px;
                    border: 1px solid #e5e7eb;
                }
                .items-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                }
                .items-table th {
                    background-color: #0d9488;
                    color: white;
                    padding: 10px;
                    text-align: left;
                    font-weight: 600;
                }
                .items-table td {
                    padding: 10px;
                    border-bottom: 1px solid #e5e7eb;
                }
                .total-section {
                    margin-top: 30px;
                    float: right;
                    width: 300px;
                }
                .total-table {
                    width: 100%;
                    border-collapse: collapse;
                }
                .total-table td {
                    padding: 8px 0;
                }
                .grand-total {
                    font-size: 16px;
                    font-weight: bold;
                    color: #0d9488;
                    border-top: 2px solid #0d9488;
                }
                .footer {
                    margin-top: 100px;
                    border-top: 1px solid #e5e7eb;
                    padding-top: 20px;
                    text-align: center;
                    color: #6b7280;
                    font-size: 11px;
                }
            </style>
        </head>
        <body>
            <div class="invoice-box">
                <table class="header-table">
                    <tr>
                        <td>
                            <div class="title">DAPUR MBG</div>
                            <p>Program SPPG Makanan Bergizi Gratis<br>
                            Jl. Raya Dapur MBG No. 45, Jakarta<br>
                            Telp: 0812-3456-7890</p>
                        </td>
                        <td class="text-right">
                            <div class="title" style="color: #4b5563;">INVOICE</div>
                            <p><strong>Nomor:</strong> ' . $invoice['nomor_invoice'] . '<br>
                            <strong>Tanggal:</strong> ' . date('d M Y', strtotime($invoice['tanggal'])) . '<br>
                            <strong>Jatuh Tempo:</strong> ' . date('d M Y', strtotime($invoice['jatuh_tempo'])) . '</p>
                        </td>
                    </tr>
                </table>

                <table class="meta-table">
                    <tr>
                        <th>Periode Distribusi</th>
                        <td>' . date('d M Y', strtotime($invoice['periode_dari'])) . ' s/d ' . date('d M Y', strtotime($invoice['periode_sampai'])) . '</td>
                        <th>Status Pembayaran</th>
                        <td><strong>' . strtoupper($invoice['status']) . '</strong></td>
                    </tr>
                    <tr>
                        <th>Dibuat Oleh</th>
                        <td>' . ($invoice['dibuat_oleh_name'] ?: 'System') . '</td>
                        <th>Total Portions</th>
                        <td>' . number_format($invoice['total_porsi']) . ' Porsi</td>
                    </tr>
                </table>

                <h3>Rincian Distribusi Sukses ("Diterima")</h3>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Sekolah</th>
                            <th>Jenjang</th>
                            <th>Penerima</th>
                            <th class="text-right">Porsi</th>
                        </tr>
                    </thead>
                    <tbody>';
        
        $no = 1;
        foreach ($details as $detail) {
            $html .= '
                        <tr>
                            <td>' . $no++ . '</td>
                            <td>' . date('d/m/Y', strtotime($detail['tanggal_distribusi'])) . '</td>
                            <td>' . $detail['nama_sekolah'] . '</td>
                            <td>' . $detail['jenjang'] . '</td>
                            <td>' . $detail['penerima'] . '</td>
                            <td class="text-right">' . number_format($detail['jumlah_porsi']) . '</td>
                        </tr>';
        }

        if (empty($details)) {
            $html .= '<tr><td colspan="6" style="text-align: center;">Tidak ada distribusi dengan status diterima pada periode ini.</td></tr>';
        }

        $html .= '
                    </tbody>
                </table>

                <div class="total-section">
                    <table class="total-table">
                        <tr>
                            <td>Total Porsi</td>
                            <td class="text-right">' . number_format($invoice['total_porsi']) . '</td>
                        </tr>
                        <tr>
                            <td>Harga per Porsi</td>
                            <td class="text-right">Rp ' . number_format($invoice['harga_per_porsi'], 2, ',', '.') . '</td>
                        </tr>
                        <tr class="grand-total">
                            <td>Total Nilai Tagihan</td>
                            <td class="text-right">Rp ' . number_format($invoice['total_nilai'], 2, ',', '.') . '</td>
                        </tr>
                    </table>
                </div>

                <div style="clear: both;"></div>';
        
        if (!empty($invoice['catatan'])) {
            $html .= '
                <div style="margin-top: 40px;">
                    <strong>Catatan:</strong><br>
                    ' . nl2br(htmlspecialchars($invoice['catatan'])) . '
                </div>';
        }

        $html .= '
                <div class="footer">
                    Terima kasih atas kerja sama Anda.<br>
                    Invoice ini dibuat secara otomatis oleh SPPG System Dapur MBG.
                </div>
            </div>
        </body>
        </html>';

        $dompdf = new Dompdf([
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true,
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="Invoice-' . $invoice['nomor_invoice'] . '.pdf"')
            ->setBody($dompdf->output());
    }
}
