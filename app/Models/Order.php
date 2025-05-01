<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Order {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($userId, $items, $totalAmount, $status = 'pending') {
        try {
            $this->db->beginTransaction();
            
            // Ana sipariş kaydını oluştur
            $query = "INSERT INTO orders (user_id, total_amount, status, created_at) 
                      VALUES (:userId, :totalAmount, :status, NOW()) 
                      RETURNING *";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':totalAmount', $totalAmount, PDO::PARAM_STR);
            $stmt->bindParam(':status', $status);
            $stmt->execute();
            
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$order) {
                $this->db->rollBack();
                return ['error' => 'Sipariş oluşturulamadı'];
            }
            
            // Sipariş detaylarını ekle
            foreach ($items as $item) {
                $query = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                          VALUES (:orderId, :productId, :quantity, :price)";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':orderId', $order['id'], PDO::PARAM_INT);
                $stmt->bindParam(':productId', $item['product_id'], PDO::PARAM_INT);
                $stmt->bindParam(':quantity', $item['quantity'], PDO::PARAM_INT);
                $stmt->bindParam(':price', $item['price'], PDO::PARAM_STR);
                $stmt->execute();
                
                // Ürün stoğunu güncelle
                $query = "UPDATE products SET stock = stock - :quantity 
                          WHERE id = :productId AND stock >= :quantity";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':quantity', $item['quantity'], PDO::PARAM_INT);
                $stmt->bindParam(':productId', $item['product_id'], PDO::PARAM_INT);
                $stmt->execute();
                
                // Stok güncellemesi başarılı mı kontrol et
                if ($stmt->rowCount() === 0) {
                    $this->db->rollBack();
                    return ['error' => 'Yetersiz stok: ' . $item['name']];
                }
            }
            
            // İşlemi tamamla
            $this->db->commit();
            
            // Siparişi detaylarıyla birlikte getir
            return $this->getById($order['id']);
        } catch (\PDOException $e) {
            $this->db->rollBack();
            return ['error' => $e->getMessage()];
        }
    }

    public function getById($orderId) {
        try {
            // Siparişi getir
            $query = "SELECT * FROM orders WHERE id = :orderId LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':orderId', $orderId, PDO::PARAM_INT);
            $stmt->execute();
            
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$order) {
                return null;
            }
            
            // Sipariş detaylarını getir
            $query = "SELECT oi.*, p.name, p.description, p.image 
                      FROM order_items oi
                      JOIN products p ON oi.product_id = p.id
                      WHERE oi.order_id = :orderId";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':orderId', $orderId, PDO::PARAM_INT);
            $stmt->execute();
            
            $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Siparişi ve detayları birleştir
            $order['items'] = $orderItems;
            
            return $order;
        } catch (\PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function getByUserId($userId) {
        try {
            // Kullanıcının tüm siparişlerini getir
            $query = "SELECT * FROM orders WHERE user_id = :userId ORDER BY created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Her sipariş için detayları getir
            foreach ($orders as &$order) {
                $query = "SELECT oi.*, p.name 
                          FROM order_items oi
                          JOIN products p ON oi.product_id = p.id
                          WHERE oi.order_id = :orderId";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':orderId', $order['id'], PDO::PARAM_INT);
                $stmt->execute();
                
                $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return $orders;
        } catch (\PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function updateStatus($orderId, $status) {
        try {
            $query = "UPDATE orders SET status = :status, updated_at = NOW() 
                      WHERE id = :orderId RETURNING *";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':orderId', $orderId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function createFromCart($userId) {
        try {
            // Kullanıcının sepetini getir
            $cartModel = new Cart();
            $cart = $cartModel->getByUserId($userId);
            
            if (isset($cart['error'])) {
                return $cart;
            }
            
            if (empty($cart['items'])) {
                return ['error' => 'Sepet boş'];
            }
            
            // Sepetteki ürünleri sipariş formatına dönüştür
            $items = [];
            foreach ($cart['items'] as $item) {
                $items[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'name' => $item['name']
                ];
            }
            
            // Sipariş oluştur
            $order = $this->create($userId, $items, $cart['total_price']);
            
            if (!isset($order['error'])) {
                // Sepeti temizle
                $cartModel->clearCart($userId);
            }
            
            return $order;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
} 