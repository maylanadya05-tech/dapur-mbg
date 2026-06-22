<?php
// Initialize CodeIgniter framework bootstrap
require __DIR__ . '/../app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/bootstrap.php';

$session = session();
echo "<h3>Session Data:</h3>";
echo "<pre>";
print_r($session->get());
echo "</pre>";
