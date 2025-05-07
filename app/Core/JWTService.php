<?php

namespace App\Core;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JWTService {
    private static $secret;
    private static $expiration;

    public static function init() {
        self::$secret = $_ENV['JWT_SECRET'];
        self::$expiration = $_ENV['JWT_EXPIRATION'];
    }

    public static function generateToken($userId, $email, $isAdmin = false) {
        self::init();
        
        $issuedAt = time();
        $expire = $issuedAt + (int)self::$expiration;

        $payload = [
            'iat' => $issuedAt,
            'exp' => $expire,
            'userId' => $userId,
            'email' => $email,
            'isAdmin' => $isAdmin
        ];

        return JWT::encode($payload, self::$secret, 'HS256');
    }

    public static function validateToken($token) {
        self::init();
        
        try {
            $decoded = JWT::decode($token, new Key(self::$secret, 'HS256'));
            return (array)$decoded;
        } catch (Exception $e) {
            return null;
        }
    }

    public static function getAuthorizationHeader() {
        $headers = null;
        
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER['Authorization']);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(
                array_map('ucwords', array_keys($requestHeaders)),
                array_values($requestHeaders)
            );
            
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        
        return $headers;
    }

    public static function getBearerToken() {
        $headers = self::getAuthorizationHeader();
        
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }

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

    public static function requireAuth() {
        $token = self::getBearerToken();
        
        if (!$token) {
            http_response_code(401);
            self::setCorsHeaders();
            echo json_encode(['success' => false, 'error' => 'Yetkilendirme gerekli']);
            exit;
        }
        
        $userData = self::validateToken($token);
        
        if (!$userData) {
            http_response_code(401); 
            self::setCorsHeaders();
            echo json_encode(['success' => false, 'error' => 'Geçersiz token']);
            exit;
        }
        
        return $userData;
    }

    public static function requireAdmin() {
        $userData = self::requireAuth();
        
        if (!isset($userData['isAdmin']) || !$userData['isAdmin']) {
            http_response_code(403);
            self::setCorsHeaders();
            echo json_encode(['success' => false, 'error' => 'Admin yetkisi gerekli']);
            exit;
        }
        
        return $userData;
    }
} 