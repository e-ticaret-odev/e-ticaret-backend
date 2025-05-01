<?php

namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        try {
            $dsn = "pgsql:host=" . $_ENV['DB_HOST'] . 
                   ";port=" . $_ENV['DB_PORT'] . 
                   ";dbname=" . $_ENV['DB_NAME'];
            
            $this->conn = new PDO(
                $dsn,
                $_ENV['DB_USER'],
                $_ENV['DB_PASSWORD'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Veritabanı bağlantı hatası: ' . $e->getMessage()]);
            exit;
        }
    }

    // Singleton pattern
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }
} 