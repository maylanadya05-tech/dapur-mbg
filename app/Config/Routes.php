<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Default redirect to dashboard
$routes->get('/', static function () {
    return redirect()->to('/dashboard');
});

// =============================================================
// AUTH ROUTES (no filter)
// =============================================================
$routes->group('auth', static function ($routes) {
    $routes->get('login', 'Auth::login');
    $routes->post('login', 'Auth::processLogin');
    $routes->get('logout', 'Auth::logout');
});

// =============================================================
// PROTECTED ROUTES (auth filter applied)
// =============================================================

// Dashboard
$routes->group('dashboard', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Dashboard::index');
});

// Stok Gudang (Admin and Gudang)
$routes->group('stok', ['filter' => ['auth', 'role:admin,gudang']], static function ($routes) {
    $routes->get('/', 'Stok::index');
    $routes->get('create', 'Stok::create');
    $routes->post('store', 'Stok::store');
    $routes->get('edit/(:num)', 'Stok::edit/$1');
    $routes->post('update/(:num)', 'Stok::update/$1');
    $routes->post('delete/(:num)', 'Stok::delete/$1');
    $routes->get('kartu-stok/(:num)', 'Stok::kartuStok/$1');
});

// Resep (Menu/BOM)
$routes->group('resep', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Resep::index');
    $routes->get('create', 'Resep::create');
    $routes->post('store', 'Resep::store');
    $routes->get('show/(:num)', 'Resep::show/$1');
    $routes->get('edit/(:num)', 'Resep::edit/$1');
    $routes->post('update/(:num)', 'Resep::update/$1');
    $routes->post('delete/(:num)', 'Resep::delete/$1');
});

// Pembelian / Purchase Orders (Admin, Pembelian, Gudang)
$routes->group('pembelian', ['filter' => ['auth', 'role:admin,pembelian,gudang']], static function ($routes) {
    $routes->get('/', 'Pembelian::index');
    $routes->get('create', 'Pembelian::create');
    $routes->post('store', 'Pembelian::store');
    $routes->get('show/(:num)', 'Pembelian::show/$1');
    $routes->get('edit/(:num)', 'Pembelian::edit/$1');
    $routes->post('update/(:num)', 'Pembelian::update/$1');
    $routes->post('approve/(:num)', 'Pembelian::approve/$1');
    $routes->post('reject/(:num)', 'Pembelian::reject/$1');
    $routes->post('delete/(:num)', 'Pembelian::delete/$1');
});

// Produksi / Batch (Admin and Produksi)
$routes->group('produksi', ['filter' => ['auth', 'role:admin,produksi']], static function ($routes) {
    $routes->get('/', 'Produksi::index');
    $routes->get('create', 'Produksi::create');
    $routes->post('store', 'Produksi::store');
    $routes->get('show/(:num)', 'Produksi::show/$1');
    $routes->post('update-status/(:num)', 'Produksi::updateStatus/$1');
});

// Sekolah
$routes->group('sekolah', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Sekolah::index');
    $routes->get('create', 'Sekolah::create');
    $routes->post('store', 'Sekolah::store');
    $routes->get('edit/(:num)', 'Sekolah::edit/$1');
    $routes->post('update/(:num)', 'Sekolah::update/$1');
    $routes->post('delete/(:num)', 'Sekolah::delete/$1');
});

// Distribusi
$routes->group('distribusi', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Distribusi::index');
    $routes->get('create', 'Distribusi::create');
    $routes->post('store', 'Distribusi::store');
    $routes->get('show/(:num)', 'Distribusi::show/$1');
    $routes->post('update-status/(:num)', 'Distribusi::updateStatus/$1');
});

// Food Waste / Sampah
$routes->group('sampah', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'FoodWaste::index');
    $routes->get('create', 'FoodWaste::create');
    $routes->post('store', 'FoodWaste::store');
    $routes->get('edit/(:num)', 'FoodWaste::edit/$1');
    $routes->post('update/(:num)', 'FoodWaste::update/$1');
    $routes->post('delete/(:num)', 'FoodWaste::delete/$1');
});

$routes->group('sisa', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'FoodWaste::index');
    $routes->get('create', 'FoodWaste::create');
    $routes->post('store', 'FoodWaste::store');
    $routes->get('edit/(:num)', 'FoodWaste::edit/$1');
    $routes->post('update/(:num)', 'FoodWaste::update/$1');
    $routes->post('delete/(:num)', 'FoodWaste::delete/$1');
});


// Feedback
$routes->group('feedback', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Feedback::index');
    $routes->get('create', 'Feedback::create');
    $routes->post('store', 'Feedback::store');
    $routes->get('show/(:num)', 'Feedback::show/$1');
});

// Invoice (Admin and Pembelian)
$routes->group('invoice', ['filter' => ['auth', 'role:admin,pembelian']], static function ($routes) {
    $routes->get('/', 'Invoice::index');
    $routes->get('create', 'Invoice::create');
    $routes->post('store', 'Invoice::store');
    $routes->get('show/(:num)', 'Invoice::show/$1');
    $routes->post('update-status/(:num)', 'Invoice::updateStatus/$1');
    $routes->get('export-pdf/(:num)', 'Invoice::exportPdf/$1');
});

// Supplier
$routes->group('supplier', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Supplier::index');
    $routes->get('create', 'Supplier::create');
    $routes->post('store', 'Supplier::store');
    $routes->get('edit/(:num)', 'Supplier::edit/$1');
    $routes->post('update/(:num)', 'Supplier::update/$1');
    $routes->post('delete/(:num)', 'Supplier::delete/$1');
});

// Jadwal Siklus (Admin only)
$routes->group('jadwal', ['filter' => ['auth', 'role:admin']], static function ($routes) {
    $routes->get('/', 'JadwalSiklus::index');
    $routes->get('create', 'JadwalSiklus::create');
    $routes->post('store', 'JadwalSiklus::store');
    $routes->get('edit/(:num)', 'JadwalSiklus::edit/$1');
    $routes->post('update/(:num)', 'JadwalSiklus::update/$1');
    $routes->post('delete/(:num)', 'JadwalSiklus::delete/$1');
});

// Users Management (Admin only)
$routes->group('users', ['filter' => ['auth', 'role:admin']], static function ($routes) {
    $routes->get('/', 'Users::index');
    $routes->get('create', 'Users::create');
    $routes->post('store', 'Users::store');
    $routes->get('edit/(:num)', 'Users::edit/$1');
    $routes->post('update/(:num)', 'Users::update/$1');
    $routes->post('delete/(:num)', 'Users::delete/$1');
});

// Laporan / Reports (Admin only)
$routes->group('laporan', ['filter' => ['auth', 'role:admin']], static function ($routes) {
    $routes->get('/', 'Laporan::index');
    $routes->get('produksi', 'Laporan::produksi');
    $routes->get('distribusi', 'Laporan::distribusi');
    $routes->get('stok', 'Laporan::stok');
    $routes->get('waste', 'Laporan::waste');
    $routes->get('export-pdf', 'Laporan::exportPdf');
    $routes->get('export-excel', 'Laporan::exportExcel');
});

// =============================================================
// PROFIL & PENGATURAN (All authenticated users)
// =============================================================
$routes->group('profil', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Profil::index');
    $routes->post('update', 'Profil::update');
});

$routes->group('pengaturan', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Pengaturan::index');
    $routes->post('update', 'Pengaturan::update');
});

$routes->group('notifikasi', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Notifikasi::index');
});

$routes->group('bantuan', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Bantuan::index');
});

// =============================================================
// NEW FEATURE ROUTES
// =============================================================

// Audit Log (Admin only)
$routes->group('audit-log', ['filter' => ['auth', 'role:admin']], static function ($routes) {
    $routes->get('/', 'AuditLog::index');
});

// Armada / Fleet Management
$routes->group('armada', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Armada::index');
    $routes->get('create', 'Armada::create');
    $routes->post('store', 'Armada::store');
    $routes->get('edit/(:num)', 'Armada::edit/$1');
    $routes->post('update/(:num)', 'Armada::update/$1');
    $routes->post('delete/(:num)', 'Armada::delete/$1');
});

// Distribusi tambahan: surat jalan, QR konfirmasi, update-status
$routes->get('distribusi/surat-jalan/(:num)', 'Distribusi::suratJalan/$1', ['filter' => 'auth']);
$routes->get('distribusi/konfirm-qr/(:any)', 'Distribusi::konfirmQr/$1');
$routes->post('distribusi/update-status/(:num)', 'Distribusi::updateStatus/$1', ['filter' => 'auth']);

// Sekolah: realisasi per sekolah
$routes->get('sekolah/realisasi/(:num)', 'Sekolah::realisasi/$1', ['filter' => 'auth']);

// JadwalSiklus: generate batch + estimasi bahan
$routes->post('jadwal/generate-batch/(:num)', 'JadwalSiklus::generateBatch/$1', ['filter' => ['auth', 'role:admin']]);
$routes->get('jadwal/estimasi-bahan/(:num)', 'JadwalSiklus::estimasiBahan/$1', ['filter' => ['auth', 'role:admin']]);

// Laporan: keuangan
$routes->get('laporan/keuangan', 'Laporan::keuangan', ['filter' => ['auth', 'role:admin']]);

// Resep: HPP calculator
$routes->get('resep/hpp/(:num)', 'Resep::hpp/$1', ['filter' => 'auth']);

// Feedback: chart data API
$routes->get('feedback/chart-data', 'Feedback::chartData', ['filter' => 'auth']);
