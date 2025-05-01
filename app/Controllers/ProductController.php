<?php

namespace App\Controllers;

use App\Models\Product;
use App\Core\Response;
use App\Core\JWTService;

class ProductController {
    private $productModel;

    public function __construct() {
        $this->productModel = new Product();
    }

    public function getAll() {
        try {
            // Sayfalama parametreleri
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = ($page - 1) * $limit;
            
            // Ürünleri getir
            $products = $this->productModel->getAll($limit, $offset);
            $total = $this->productModel->count();
            
            // Başarılı cevap
            return Response::success([
                'products' => $products,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'pages' => ceil($total / $limit)
                ]
            ], 'Ürünler başarıyla listelendi');
        } catch (\Exception $e) {
            return Response::error('Bir hata oluştu: ' . $e->getMessage());
        }
    }

    public function getById($id) {
        try {
            // Ürünü getir
            $product = $this->productModel->getById($id);
            
            if (!$product) {
                return Response::notFound('Ürün bulunamadı');
            }
            
            // Başarılı cevap
            return Response::success($product, 'Ürün detayları getirildi');
        } catch (\Exception $e) {
            return Response::error('Bir hata oluştu: ' . $e->getMessage());
        }
    }

    public function create() {
        try {
            // Admin yetkisi kontrolü
            $userData = JWTService::requireAdmin();
            
            // JSON verisini al
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Gerekli alanları kontrol et
            if (!isset($data['name']) || !isset($data['price'])) {
                return Response::error('Ürün adı ve fiyat alanları zorunludur');
            }
            
            // Ürünü oluştur
            $result = $this->productModel->create(
                $data['name'],
                $data['description'] ?? '',
                $data['price'],
                $data['stock'] ?? 0,
                $data['image'] ?? null
            );
            
            // Hata kontrolü
            if (isset($result['error'])) {
                return Response::error($result['error']);
            }
            
            // Başarılı cevap
            return Response::success($result, 'Ürün başarıyla oluşturuldu', 201);
        } catch (\Exception $e) {
            return Response::error('Bir hata oluştu: ' . $e->getMessage());
        }
    }

    public function update($id) {
        try {
            // Admin yetkisi kontrolü
            $userData = JWTService::requireAdmin();
            
            // JSON verisini al
            $data = json_decode(file_get_contents('php://input'), true);
            
            // En az bir alan zorunlu
            if (empty($data)) {
                return Response::error('En az bir alanı güncellemelisiniz');
            }
            
            // Ürünü güncelle
            $result = $this->productModel->update($id, $data);
            
            // Hata kontrolü
            if (isset($result['error'])) {
                return Response::error($result['error']);
            }
            
            // Başarılı cevap
            return Response::success($result, 'Ürün başarıyla güncellendi');
        } catch (\Exception $e) {
            return Response::error('Bir hata oluştu: ' . $e->getMessage());
        }
    }

    public function delete($id) {
        try {
            // Admin yetkisi kontrolü
            $userData = JWTService::requireAdmin();
            
            // Ürünü sil
            $result = $this->productModel->delete($id);
            
            // Hata kontrolü
            if (isset($result['error'])) {
                return Response::error($result['error']);
            }
            
            // Başarılı cevap
            return Response::success(null, 'Ürün başarıyla silindi');
        } catch (\Exception $e) {
            return Response::error('Bir hata oluştu: ' . $e->getMessage());
        }
    }
} 