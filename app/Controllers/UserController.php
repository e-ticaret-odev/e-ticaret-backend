<?php

namespace App\Controllers;

use App\Models\User;
use App\Core\Response;
use App\Core\JWTService;

class UserController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function register() {
        try {
            // JSON verisini al
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Gerekli alanları kontrol et
            if (!isset($data['name']) || !isset($data['email']) || !isset($data['password'])) {
                return Response::error('Ad, email ve şifre alanları zorunludur');
            }

            // Şifre uzunluğu kontrolü
            if (strlen($data['password']) < 6) {
                return Response::error('Şifre en az 6 karakter olmalıdır');
            }

            // Email formatı kontrolü
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return Response::error('Geçerli bir email adresi giriniz');
            }

            // Kullanıcıyı kaydet
            $result = $this->userModel->register(
                $data['name'],
                $data['email'],
                $data['password'],
                $data['is_admin'] ?? false
            );

            // Hata kontrolü
            if (isset($result['error'])) {
                return Response::error($result['error']);
            }

            // Başarılı cevap
            return Response::success($result, 'Kullanıcı başarıyla kaydedildi', 201);
        } catch (\Exception $e) {
            return Response::error('Bir hata oluştu: ' . $e->getMessage());
        }
    }

    public function login() {
        try {
            // JSON verisini al
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Gerekli alanları kontrol et
            if (!isset($data['email']) || !isset($data['password'])) {
                return Response::error('Email ve şifre alanları zorunludur');
            }

            // Kullanıcıyı doğrula
            $user = $this->userModel->login($data['email'], $data['password']);

            // Hata kontrolü
            if (isset($user['error'])) {
                return Response::error($user['error']);
            }

            // JWT token oluştur
            $token = JWTService::generateToken($user['id'], $user['email'], $user['is_admin']);

            // Başarılı cevap
            return Response::success([
                'user' => $user,
                'token' => $token
            ], 'Giriş başarılı');
        } catch (\Exception $e) {
            return Response::error('Bir hata oluştu: ' . $e->getMessage());
        }
    }

    public function getProfile() {
        try {
            // Token doğrulama ve kullanıcı bilgilerini al
            $userData = JWTService::requireAuth();
            
            // Kullanıcı bilgilerini getir
            $user = $this->userModel->findById($userData['userId']);
            
            if (!$user) {
                return Response::notFound('Kullanıcı bulunamadı');
            }

            // Başarılı cevap
            return Response::success($user, 'Profil bilgileri getirildi');
        } catch (\Exception $e) {
            return Response::error('Bir hata oluştu: ' . $e->getMessage());
        }
    }
} 