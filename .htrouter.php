<?php
/**
 * Bu dosya PHP'nin dahili web sunucusu için tüm istekleri index.php'ye yönlendirir
 */

// CORS başlıklarını her durumda ayarla
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400'); // 24 saat

// OPTIONS isteklerini hemen yanıtla
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Content-Type: application/json');
    header('Content-Length: 0');
    http_response_code(200);
    exit(0);
}

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Statik dosyalar hariç tüm istekleri index.php'ye yönlendir
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    return false;
}

// Tüm istekleri index.php'ye yönlendir
require_once __DIR__ . '/public/index.php'; 