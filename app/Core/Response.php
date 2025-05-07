<?php

namespace App\Core;

class Response {
    private static function setCorsHeaders() {
        // İzin verilen originleri tanımla
        $allowedOrigins = [
            'http://localhost:5000',   // Frontend domain
            'http://localhost:3000',   // Olası diğer geliştirme ortamı
            'http://127.0.0.1:5000',   // Alternatif lokal adres
            'http://127.0.0.1:3000',   // Alternatif lokal adres
            'null'                     // Yerel dosya sistemi için
        ];
        
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        
        // Geliştirme aşamasında hızlı çözüm için tüm originlere izin ver
        header('Access-Control-Allow-Origin: *');
        
        // İzin verilen originler listesini kontrol et
        // if (in_array($origin, $allowedOrigins)) {
        //     header("Access-Control-Allow-Origin: {$origin}");
        // } else {
        //     // Geliştirme aşamasında tüm originlere izin ver (canlı ortamda kaldırılmalı)
        //     header('Access-Control-Allow-Origin: *');
        // }
        
        // Diğer CORS başlıklarını ayarla
        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept, Origin, Authorization');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400'); // 24 saat
    }

    public static function json($data, $status = 200) {
        http_response_code($status);
        self::setCorsHeaders();
        return json_encode($data);
    }

    public static function success($data = [], $message = 'İşlem başarılı', $status = 200) {
        return self::json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    public static function error($message = 'Bir hata oluştu', $status = 400) {
        return self::json([
            'success' => false,
            'error' => $message
        ], $status);
    }

    public static function notFound($message = 'Kayıt bulunamadı') {
        return self::error($message, 404);
    }

    public static function unauthorized($message = 'Yetkisiz erişim') {
        return self::error($message, 401);
    }

    public static function forbidden($message = 'Bu işlem için yetkiniz yok') {
        return self::error($message, 403);
    }
} 