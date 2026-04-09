<?php

require __DIR__ . '/../vendor/autoload.php';

$uri = $_SERVER['REQUEST_URI'];

// Kalau akses root, tampilkan landing page
if ($uri === '/' || $uri === '') {
    readfile(__DIR__ . '/index.html');
    exit;
}

// Selain itu, jalankan framework seperti biasa
echo "Artemis berjalan!";