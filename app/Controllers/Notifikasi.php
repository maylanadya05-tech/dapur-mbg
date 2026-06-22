<?php

namespace App\Controllers;

use App\Models\BahanBakuModel;
use App\Models\PurchaseOrderModel;

class Notifikasi extends BaseController
{
    protected BahanBakuModel $bahanModel;
    protected PurchaseOrderModel $poModel;

    public function __construct()
    {
        $this->bahanModel = new BahanBakuModel();
        $this->poModel = new PurchaseOrderModel();
    }

    /**
     * Display the system notifications page with role-based filtering and urgency
     */
    public function index()
    {
        $lang = session()->get('pref_lang') ?? 'id';
        $role = strtolower(session()->get('user_role') ?? 'viewer');
        
        $notifications = [];

        // 1. Fetch Critical Stock
        $stokKritis = $this->bahanModel->getStokKritisSimple();
        foreach ($stokKritis as $item) {
            $stok = (float) $item['stok_saat_ini'];
            $min = (float) $item['stok_minimum'];
            $satuan = esc($item['satuan']);
            $nama = esc($item['nama']);
            
            $message = $lang === 'en'
                ? "Current stock ({$stok} {$satuan}) is below the minimum limit ({$min} {$satuan}). Please create a Purchase Order."
                : "Stok saat ini ({$stok} {$satuan}) berada di bawah batas minimum ({$min} {$satuan}). Silakan buat Purchase Order.";

            // Critical stock is highly urgent for Admin, Gudang (managing stock), Pembelian (buying), and Produksi (cooking)
            $isUrgent = in_array($role, ['admin', 'gudang', 'pembelian', 'produksi']);

            $notifications[] = [
                'type' => 'stok',
                'title' => $lang === 'en' ? "Critical Stock: {$nama}" : "Stok Kritis: {$nama}",
                'nama_bahan' => $nama,
                'kategori' => esc($item['kategori']),
                'message' => $message,
                'severity' => 'danger',
                'link' => base_url('/stok'),
                'icon' => 'alert-triangle',
                'is_urgent' => $isUrgent,
                'timestamp' => $item['updated_at'] ?? $item['created_at'] ?? date('Y-m-d H:i:s'),
                'friendly_time' => $this->formatFriendlyTime($item['updated_at'] ?? $item['created_at'] ?? date('Y-m-d H:i:s'), $lang)
            ];
        }

        // 2. Fetch Purchase Orders
        $purchaseOrders = $this->poModel->getWithRelations();
        foreach ($purchaseOrders as $po) {
            $nomorPo = esc($po['nomor_po']);
            $supplierName = esc($po['supplier_name']);
            $totalNilai = number_format((float) $po['total_nilai'], 0, ',', '.');
            $dibuatOleh = esc($po['dibuat_oleh_name']);
            $disetujuiOleh = esc($po['disetujui_oleh_name']);
            $status = $po['status'];
            
            $severity = 'info';
            $icon = 'shopping-cart';
            $title = '';
            $message = '';
            $timestamp = $po['created_at'];
            $isUrgent = false;
            $showForRole = true;

            if ($status === 'diajukan') {
                $severity = 'warning';
                $icon = 'file-text';
                $title = $lang === 'en' ? "PO Submitted: {$nomorPo}" : "PO Diajukan: {$nomorPo}";
                $message = $lang === 'en'
                    ? "Purchase Order {$nomorPo} for supplier {$supplierName} worth Rp {$totalNilai} has been submitted by {$dibuatOleh} and is awaiting approval."
                    : "Purchase Order {$nomorPo} untuk supplier {$supplierName} senilai Rp {$totalNilai} telah diajukan oleh {$dibuatOleh} dan menunggu persetujuan.";
                
                // Urgent for Admin (needs to approve it)
                if ($role === 'admin') {
                    $isUrgent = true;
                }
                // Hide from kitchen staff (produksi)
                if ($role === 'produksi') {
                    $showForRole = false;
                }
                $timestamp = $po['created_at'];

            } elseif ($status === 'disetujui') {
                $severity = 'success';
                $icon = 'check-circle';
                $title = $lang === 'en' ? "PO Approved: {$nomorPo}" : "PO Disetujui: {$nomorPo}";
                $message = $lang === 'en'
                    ? "Purchase Order {$nomorPo} for supplier {$supplierName} worth Rp {$totalNilai} has been approved by {$disetujuiOleh}."
                    : "Purchase Order {$nomorPo} untuk supplier {$supplierName} senilai Rp {$totalNilai} telah disetujui oleh {$disetujuiOleh}.";
                
                // Urgent for Gudang (expecting delivery)
                if ($role === 'gudang') {
                    $isUrgent = true;
                }
                $timestamp = $po['tanggal_disetujui'] ?? $po['updated_at'] ?? $po['created_at'];

            } elseif ($status === 'ditolak') {
                $severity = 'danger';
                $icon = 'x-circle';
                $title = $lang === 'en' ? "PO Rejected: {$nomorPo}" : "PO Ditolak: {$nomorPo}";
                $alasan = esc($po['alasan_tolak'] ?: ($lang === 'en' ? 'no note' : 'tidak ada catatan'));
                $message = $lang === 'en'
                    ? "Purchase Order {$nomorPo} for supplier {$supplierName} has been rejected. Reason: {$alasan}."
                    : "Purchase Order {$nomorPo} untuk supplier {$supplierName} ditolak. Alasan: {$alasan}.";
                
                // Urgent for Pembelian (created PO was rejected, needs to fix)
                if ($role === 'pembelian') {
                    $isUrgent = true;
                }
                // Hide from kitchen staff (produksi)
                if ($role === 'produksi') {
                    $showForRole = false;
                }
                $timestamp = $po['updated_at'] ?? $po['created_at'];
            } else {
                continue; // Skip if other status
            }

            if ($showForRole) {
                $notifications[] = [
                    'type' => 'po',
                    'title' => $title,
                    'kategori' => null,
                    'nama_bahan' => null,
                    'message' => $message,
                    'severity' => $severity,
                    'link' => base_url("/pembelian/show/{$po['id']}"),
                    'icon' => $icon,
                    'is_urgent' => $isUrgent,
                    'timestamp' => $timestamp,
                    'friendly_time' => $this->formatFriendlyTime($timestamp, $lang)
                ];
            }
        }

        // Sort all notifications chronologically descending (newest first)
        usort($notifications, function ($a, $b) {
            return strcmp($b['timestamp'], $a['timestamp']);
        });

        // Split notifications into Urgent and Info
        $urgentList = [];
        $infoList = [];
        foreach ($notifications as $notif) {
            if ($notif['is_urgent']) {
                $urgentList[] = $notif;
            } else {
                $infoList[] = $notif;
            }
        }

        return view('notifikasi/index', [
            'title' => $lang === 'en' ? 'System Notifications' : 'Notifikasi Sistem',
            'urgentNotifications' => $urgentList,
            'infoNotifications' => $infoList,
            'userRole' => $role,
        ]);
    }

    /**
     * Format a database datetime into a friendly relative time
     */
    private function formatFriendlyTime(string $datetime, string $lang = 'id'): string
    {
        $time = strtotime($datetime);
        if (!$time) {
            return '-';
        }

        $diff = time() - $time;
        if ($diff < 0) {
            return date($lang === 'en' ? 'd M Y, H:i' : 'd M Y, H:i', $time);
        }

        if ($diff < 60) {
            return $lang === 'en' ? 'Just now' : 'Baru saja';
        }

        $mins = round($diff / 60);
        if ($mins < 60) {
            return $lang === 'en' ? $mins . ' min ago' : $mins . ' menit yang lalu';
        }

        $hours = round($diff / 3600);
        if ($hours < 24) {
            return $lang === 'en' ? $hours . ' hours ago' : $hours . ' jam yang lalu';
        }

        $days = round($diff / 86400);
        if ($days < 7) {
            return $lang === 'en' ? $days . ' days ago' : $days . ' hari yang lalu';
        }

        return date($lang === 'en' ? 'd M Y, H:i' : 'd M Y, H:i', $time);
    }
}
