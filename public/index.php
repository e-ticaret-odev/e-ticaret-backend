<?php

// Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// .env değişkenlerini yükle
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Hata gösterimi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Temel CORS başlıklarını doğrudan ekle - hiçbir koşul olmadan
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400'); // 24 saat

// Preflight OPTIONS isteklerini hemen yanıtla
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Content-Type: application/json');
    http_response_code(200);
    exit(0);
}

// İçerik tipini belirle
header('Content-Type: application/json');

// Router'ı başlat
require_once __DIR__ . '/../app/Routes/api.php'; 