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

    public static function requireAuth() {
        $token = self::getBearerToken();
        
        if (!$token) {
            header('HTTP/1.0 401 Unauthorized');
            echo json_encode(['error' => 'Yetkilendirme gerekli']);
            exit;
        }
        
        $userData = self::validateToken($token);
        
        if (!$userData) {
            header('HTTP/1.0 401 Unauthorized');
            echo json_encode(['error' => 'Geçersiz token']);
            exit;
        }
        
        return $userData;
    }

    public static function requireAdmin() {
        $userData = self::requireAuth();
        
        if (!isset($userData['isAdmin']) || !$userData['isAdmin']) {
            header('HTTP/1.0 403 Forbidden');
            echo json_encode(['error' => 'Admin yetkisi gerekli']);
            exit;
        }
        
        return $userData;
    }
} 