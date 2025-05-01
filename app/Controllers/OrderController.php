<?php

namespace App\Controllers;

use App\Models\Order;
use App\Core\Response;
use App\Core\JWTService;

class OrderController {
    private $orderModel;

    public function __construct() {
        $this->orderModel = new Order();
    }

    public function createOrder() {
        try {
            // Token doğrulama ve kullanıcı bilgilerini al
            $userData = JWTService::requireAuth();
            
            // Sepetten sipariş oluştur
            $result = $this->orderModel->createFromCart($userData['userId']);
            
            // Hata kontrolü
            if (isset($result['error'])) {
                return Response::error($result['error']);
            }
            
            // Başarılı cevap
            return Response::success($result, 'Sipariş başarıyla oluşturuldu', 201);
        } catch (\Exception $e) {
            return Response::error('Bir hata oluştu: ' . $e->getMessage());
        }
    }

    public function getUserOrders() {
        try {
            // Token doğrulama ve kullanıcı bilgilerini al
            $userData = JWTService::requireAuth();
            
            // Kullanıcının siparişlerini getir
            $orders = $this->orderModel->getByUserId($userData['userId']);
            
            // Hata kontrolü
            if (isset($orders['error'])) {
                return Response::error($orders['error']);
            }
            
            // Başarılı cevap
            return Response::success(['orders' => $orders], 'Siparişler başarıyla getirildi');
        } catch (\Exception $e) {
            return Response::error('Bir hata oluştu: ' . $e->getMessage());
        }
    }

    public function getOrderDetails($orderId) {
        try {
            // Token doğrulama ve kullanıcı bilgilerini al
            $userData = JWTService::requireAuth();
            
            // Siparişi getir
            $order = $this->orderModel->getById($orderId);
            
            // Sipariş bulunamadı
            if (!$order) {
                return Response::notFound('Sipariş bulunamadı');
            }
            
            // Sipariş kullanıcıya ait değilse veya admin değilse erişimi engelle
            if ($order['user_id'] != $userData['userId'] && !$userData['isAdmin']) {
                return Response::forbidden('Bu siparişi görüntüleme yetkiniz yok');
            }
            
            // Başarılı cevap
            return Response::success($order, 'Sipariş detayları getirildi');
        } catch (\Exception $e) {
            return Response::error('Bir hata oluştu: ' . $e->getMessage());
        }
    }

    public function updateOrderStatus($orderId) {
        try {
            // Admin yetkisi kontrolü
            $userData = JWTService::requireAdmin();
            
            // JSON verisini al
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Gerekli alanları kontrol et
            if (!isset($data['status'])) {
                return Response::error('Durum alanı zorunludur');
            }
            
            // İzin verilen durumlar
            $allowedStatuses = ['pending', 'processing', 'completed', 'cancelled'];
            if (!in_array($data['status'], $allowedStatuses)) {
                return Response::error('Geçersiz sipariş durumu');
            }
            
            // Siparişi getir
            $order = $this->orderModel->getById($orderId);
            
            // Sipariş bulunamadı
            if (!$order) {
                return Response::notFound('Sipariş bulunamadı');
            }
            
            // Durumu güncelle
            $result = $this->orderModel->updateStatus($orderId, $data['status']);
            
            // Hata kontrolü
            if (isset($result['error'])) {
                return Response::error($result['error']);
            }
            
            // Başarılı cevap
            return Response::success($result, 'Sipariş durumu güncellendi');
        } catch (\Exception $e) {
            return Response::error('Bir hata oluştu: ' . $e->getMessage());
        }
    }
} 