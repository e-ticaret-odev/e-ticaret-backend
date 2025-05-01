<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function register($name, $email, $password, $isAdmin = false) {
        try {
            // Email kontrolü
            if ($this->findByEmail($email)) {
                return ['error' => 'Bu email adresi zaten kullanılıyor'];
            }

            // Şifre hashleme
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $query = "INSERT INTO users (name, email, password, is_admin, created_at) 
                      VALUES (:name, :email, :password, :is_admin, NOW()) 
                      RETURNING id, name, email, is_admin";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':is_admin', $isAdmin, PDO::PARAM_BOOL);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function login($email, $password) {
        try {
            $user = $this->findByEmail($email);
            
            if (!$user) {
                return ['error' => 'Geçersiz email veya şifre'];
            }
            
            if (!password_verify($password, $user['password'])) {
                return ['error' => 'Geçersiz email veya şifre'];
            }
            
            // Hassas bilgileri gizle
            unset($user['password']);
            
            return $user;
        } catch (\PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function findByEmail($email) {
        $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $query = "SELECT id, name, email, is_admin, created_at, updated_at 
                  FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
} 