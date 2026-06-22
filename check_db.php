<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$m = new mysqli('localhost', 'root', '', 'dapur_mbg');
if ($m->connect_error) {
    die('Connect Error: ' . $m->connect_error);
}

echo '<pre>';

// Total rows
$r = $m->query('SELECT COUNT(*) as c FROM batch_produksi');
$row = $r->fetch_assoc();
echo "TOTAL ROWS batch_produksi: " . $row['c'] . "\n\n";

// 15 data terbaru
$r2 = $m->query('SELECT tanggal_produksi, porsi_selesai, status FROM batch_produksi ORDER BY tanggal_produksi DESC LIMIT 15');
echo "15 DATA TERBARU:\n";
if ($r2 && $r2->num_rows > 0) {
    while ($row = $r2->fetch_assoc()) {
        echo "  tanggal: {$row['tanggal_produksi']} | porsi_selesai: {$row['porsi_selesai']} | status: {$row['status']}\n";
    }
} else {
    echo "  (kosong)\n";
}

// Tren 7 hari
$from = date('Y-m-d', strtotime('-7 days'));
echo "\nTREN PRODUKSI 7 HARI (dari {$from}):\n";
$r3 = $m->query("SELECT tanggal_produksi, SUM(porsi_selesai) AS total_porsi, COUNT(id) AS total_batch FROM batch_produksi WHERE tanggal_produksi >= '{$from}' GROUP BY tanggal_produksi ORDER BY tanggal_produksi ASC");
if ($r3 && $r3->num_rows > 0) {
    while ($row = $r3->fetch_assoc()) {
        echo "  tanggal: {$row['tanggal_produksi']} | total_porsi: {$row['total_porsi']} | total_batch: {$row['total_batch']}\n";
    }
} else {
    echo "  Tidak ada data 7 hari terakhir\n";
}

echo '</pre>';
