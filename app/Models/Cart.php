<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Cart {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByUserId($userId) {
        try {
            // Kullanıcının sepet ürünlerini ürün detaylarıyla birlikte getir
            $query = "SELECT ci.id, ci.user_id, ci.product_id, ci.quantity, 
                            p.name, p.description, p.price, p.image, 
                            (p.price * ci.quantity) as total_price
                      FROM cart_items ci
                      JOIN products p ON ci.product_id = p.id
                      WHERE ci.user_id = :userId";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Toplam fiyatı hesapla
            $totalPrice = 0;
            foreach ($cartItems as $item) {
                $totalPrice += (float)$item['total_price'];
            }
            
            return [
                'items' => $cartItems,
                'total_items' => count($cartItems),
                'total_price' => $totalPrice
            ];
        } catch (\PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function addItem($userId, $productId, $quantity = 1) {
        try {
            // Ürünün varlığını ve stok durumunu kontrol et
            $productModel = new Product();
            $product = $productModel->getById($productId);
            
            if (!$product) {
                return ['error' => 'Ürün bulunamadı'];
            }
            
            if ($product['stock'] < $quantity) {
                return ['error' => 'Yetersiz stok'];
            }
            
            // Sepette aynı ürün var mı kontrol et
            $query = "SELECT * FROM cart_items 
                      WHERE user_id = :userId AND product_id = :productId LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':productId', $productId, PDO::PARAM_INT);
            $stmt->execute();
            
            $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingItem) {
                // Mevcut ürünün miktarını güncelle
                $newQuantity = $existingItem['quantity'] + $quantity;
                
                if ($product['stock'] < $newQuantity) {
                    return ['error' => 'Yetersiz stok'];
                }
                
                $query = "UPDATE cart_items SET quantity = :quantity 
                          WHERE id = :id RETURNING *";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':quantity', $newQuantity, PDO::PARAM_INT);
                $stmt->bindParam(':id', $existingItem['id'], PDO::PARAM_INT);
                $stmt->execute();
                
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                // Yeni ürün ekle
                $query = "INSERT INTO cart_items (user_id, product_id, quantity, created_at) 
                          VALUES (:userId, :productId, :quantity, NOW()) 
                          RETURNING *";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
                $stmt->bindParam(':productId', $productId, PDO::PARAM_INT);
                $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
                $stmt->execute();
                
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (\PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function updateQuantity($userId, $cartItemId, $quantity) {
        try {
            // Sepet öğesinin kullanıcıya ait olduğunu kontrol et
            $query = "SELECT ci.*, p.stock FROM cart_items ci 
                      JOIN products p ON ci.product_id = p.id
                      WHERE ci.id = :cartItemId AND ci.user_id = :userId LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':cartItemId', $cartItemId, PDO::PARAM_INT);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$cartItem) {
                return ['error' => 'Sepet öğesi bulunamadı'];
            }
            
            // Stok durumunu kontrol et
            if ($cartItem['stock'] < $quantity) {
                return ['error' => 'Yetersiz stok'];
            }
            
            // Miktarı güncelle
            $query = "UPDATE cart_items SET quantity = :quantity 
                      WHERE id = :cartItemId RETURNING *";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $stmt->bindParam(':cartItemId', $cartItemId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function removeItem($userId, $cartItemId) {
        try {
            // Sepet öğesinin kullanıcıya ait olduğunu kontrol et
            $query = "SELECT * FROM cart_items 
                      WHERE id = :cartItemId AND user_id = :userId LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':cartItemId', $cartItemId, PDO::PARAM_INT);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$cartItem) {
                return ['error' => 'Sepet öğesi bulunamadı'];
            }
            
            // Sepet öğesini sil
            $query = "DELETE FROM cart_items WHERE id = :cartItemId";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':cartItemId', $cartItemId, PDO::PARAM_INT);
            $stmt->execute();
            
            return ['success' => true, 'message' => 'Ürün sepetten kaldırıldı'];
        } catch (\PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function clearCart($userId) {
        try {
            $query = "DELETE FROM cart_items WHERE user_id = :userId";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return ['success' => true, 'message' => 'Sepet temizlendi'];
        } catch (\PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
} 