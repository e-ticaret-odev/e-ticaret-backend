<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Product {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($limit = 10, $offset = 0) {
        $query = "SELECT * FROM products ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM products WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($name, $description, $price, $stock, $image = null) {
        try {
            $query = "INSERT INTO products (name, description, price, stock, image, created_at) 
                      VALUES (:name, :description, :price, :stock, :image, NOW()) 
                      RETURNING *";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':stock', $stock, PDO::PARAM_INT);
            $stmt->bindParam(':image', $image);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function update($id, $data) {
        try {
            // Mevcut ürünü kontrol et
            $product = $this->getById($id);
            if (!$product) {
                return ['error' => 'Ürün bulunamadı'];
            }
            
            // Güncellenecek alanları belirle
            $fields = [];
            $values = [];
            
            foreach ($data as $key => $value) {
                if (in_array($key, ['name', 'description', 'price', 'stock', 'image'])) {
                    $fields[] = "$key = :$key";
                    $values[":$key"] = $value;
                }
            }
            
            $values[':id'] = $id;
            $values[':updated_at'] = date('Y-m-d H:i:s');
            
            // Güncellenecek alan yoksa çık
            if (empty($fields)) {
                return $product;
            }
            
            $query = "UPDATE products SET " . implode(', ', $fields) . ", updated_at = :updated_at 
                      WHERE id = :id RETURNING *";
            
            $stmt = $this->db->prepare($query);
            foreach ($values as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function delete($id) {
        try {
            // Mevcut ürünü kontrol et
            $product = $this->getById($id);
            if (!$product) {
                return ['error' => 'Ürün bulunamadı'];
            }
            
            $query = "DELETE FROM products WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return ['success' => true, 'message' => 'Ürün başarıyla silindi'];
        } catch (\PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function count() {
        $query = "SELECT COUNT(*) as total FROM products";
        $stmt = $this->db->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
} 