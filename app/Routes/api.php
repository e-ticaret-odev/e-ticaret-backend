<?php

use App\Core\Router;
use App\Core\Response;
use App\Controllers\UserController;
use App\Controllers\ProductController;
use App\Controllers\CartController;
use App\Controllers\OrderController;

// API kullanılabilirlik kontrolü
Router::get('api', function() {
    return Response::success(['version' => '1.0'], 'API çalışıyor');
});

// Kullanıcı rotaları
Router::post('api/register', [new UserController(), 'register']);
Router::post('api/login', [new UserController(), 'login']);
Router::get('api/profile', [new UserController(), 'getProfile']);

// Ürün rotaları
Router::get('api/products', [new ProductController(), 'getAll']);
Router::get('api/products/:id', [new ProductController(), 'getById']);
Router::post('api/products', [new ProductController(), 'create']);
Router::put('api/products/:id', [new ProductController(), 'update']);
Router::delete('api/products/:id', [new ProductController(), 'delete']);

// Sepet rotaları
Router::get('api/cart', [new CartController(), 'getCart']);
Router::post('api/cart/add', [new CartController(), 'addToCart']);
Router::put('api/cart/:id', [new CartController(), 'updateQuantity']);
Router::delete('api/cart/:id', [new CartController(), 'removeFromCart']);
Router::delete('api/cart', [new CartController(), 'clearCart']);

// Sipariş rotaları
Router::post('api/orders', [new OrderController(), 'createOrder']);
Router::get('api/orders', [new OrderController(), 'getUserOrders']);
Router::get('api/orders/:id', [new OrderController(), 'getOrderDetails']);
Router::put('api/orders/:id/status', [new OrderController(), 'updateOrderStatus']);

// 404 - Bulunamadı
Router::notFound(function() {
    return Response::notFound('Endpoint bulunamadı');
});

// Router'ı çalıştır
Router::resolve(); 