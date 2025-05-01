<?php

namespace App\Controllers;

use App\Models\Cart;
use App\Core\Response;
use App\Core\JWTService;

class CartController {
    private $cartModel;

    public function __construct() {
        $this->cartModel = new Cart();
    }

    public function getCart() {
        try {
            // Token doğrulama ve kullanıcı bilgilerini al
            $userData = JWTService::requireAuth();
            
            // Kullanıcının sepetini getir
            $cart = $this->cartModel->getByUserId($userData['userId']);
            
            // Başarılı cevap
            return Response::success($cart, 'Sepet başarıyla getirildi');
        } catch (\Exception $e) {
            return Response::error('Bir hata oluştu: ' . $e->getMessage());
        }
    }

    public function addToCart() {
        try {
            // Token doğrulama ve kullanıcı bilgilerini al
            $userData = JWTService::requireAuth();
            
            // JSON verisini al
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Gerekli alanları kontrol et
            if (!isset($data['product_id'])) {
                return Response::error('Ürün ID zorunludur');
            }
            
            // Ürün miktarı kontrolü
            $quantity = isset($data['quantity']) ? (int)$data['quantity'] : 1;
            if ($quantity <= 0) {
                return Response::error('Miktar 1 veya daha fazla olmalıdır');
            }
            
            // Sepete ekle
            $result = $this->cartModel->addItem(
                $userData['userId'],
                $data['product_id'],
                $quantity
            );
            
            // Hata kontrolü
            if (isset($result['error'])) {
                return Response::error($result['error']);
            }
            
            // Başarılı cevap
            return Response::success($result, 'Ürün sepete eklendi');
        } catch (\Exception $e) {
            return Response::error('Bir hata oluştu: ' . $e->getMessage());
        }
    }

    public function updateQuantity($cartItemId) {
        try {
            // Token doğrulama ve kullanıcı bilgilerini al
            $userData = JWTService::requireAuth();
            
            // JSON verisini al
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Gerekli alanları kontrol et
            if (!isset($data['quantity'])) {
                return Response::error('Miktar zorunludur');
            }
            
            // Ürün miktarı kontrolü
            $quantity = (int)$data['quantity'];
            if ($quantity <= 0) {
                return Response::error('Miktar 1 veya daha fazla olmalıdır');
            }
            
            // Miktarı güncelle
            $result = $this->cartModel->updateQuantity(
                $userData['userId'],
                $cartItemId,
                $quantity
            );
            
            // Hata kontrolü
            if (isset($result['error'])) {
                return Response::error($result['error']);
            }
            
            // Başarılı cevap
            return Response::success($result, 'Ürün miktarı güncellendi');
        } catch (\Exception $e) {
            return Response::error('Bir hata oluştu: ' . $e->getMessage());
        }
    }

    public function removeFromCart($cartItemId) {
        try {
            // Token doğrulama ve kullanıcı bilgilerini al
            $userData = JWTService::requireAuth();
            
            // Sepetten kaldır
            $result = $this->cartModel->removeItem($userData['userId'], $cartItemId);
            
            // Hata kontrolü
            if (isset($result['error'])) {
                return Response::error($result['error']);
            }
            
            // Başarılı cevap
            return Response::success(null, 'Ürün sepetten kaldırıldı');
        } catch (\Exception $e) {
            return Response::error('Bir hata oluştu: ' . $e->getMessage());
        }
    }

    public function clearCart() {
        try {
            // Token doğrulama ve kullanıcı bilgilerini al
            $userData = JWTService::requireAuth();
            
            // Sepeti temizle
            $result = $this->cartModel->clearCart($userData['userId']);
            
            // Hata kontrolü
            if (isset($result['error'])) {
                return Response::error($result['error']);
            }
            
            // Başarılı cevap
            return Response::success(null, 'Sepet temizlendi');
        } catch (\Exception $e) {
            return Response::error('Bir hata oluştu: ' . $e->getMessage());
        }
    }
} 