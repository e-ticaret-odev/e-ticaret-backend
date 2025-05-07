<?php

use App\Core\Router;
use App\Core\Response;
use App\Controllers\UserController;
use App\Controllers\ProductController;
use App\Controllers\CartController;
use App\Controllers\OrderController;

// API kullanılabilirlik kontrolü
Router::get('', function() {
    return Response::success(['version' => '1.0'], 'API çalışıyor');
});

// OPTIONS için genel handler ekleyelim
Router::options('register', function() { return ''; });
Router::options('login', function() { return ''; });
Router::options('profile', function() { return ''; });
Router::options('products', function() { return ''; });
Router::options('products/:id', function() { return ''; });
Router::options('cart', function() { return ''; });
Router::options('cart/add', function() { return ''; });
Router::options('cart/:id', function() { return ''; });
Router::options('orders', function() { return ''; });
Router::options('orders/:id', function() { return ''; });
Router::options('orders/:id/status', function() { return ''; });

// Kullanıcı rotaları
Router::post('register', [new UserController(), 'register']);
Router::post('login', [new UserController(), 'login']);
Router::get('profile', [new UserController(), 'getProfile']);

// Ürün rotaları
Router::get('products', [new ProductController(), 'getAll']);
Router::get('products/:id', [new ProductController(), 'getById']);
Router::post('products', [new ProductController(), 'create']);
Router::put('products/:id', [new ProductController(), 'update']);
Router::delete('products/:id', [new ProductController(), 'delete']);

// Sepet rotaları
Router::get('cart', [new CartController(), 'getCart']);
Router::post('cart/add', [new CartController(), 'addToCart']);
Router::put('cart/:id', [new CartController(), 'updateQuantity']);
Router::delete('cart/:id', [new CartController(), 'removeFromCart']);
Router::delete('cart', [new CartController(), 'clearCart']);

// Sipariş rotaları
Router::post('orders', [new OrderController(), 'createOrder']);
Router::get('orders', [new OrderController(), 'getUserOrders']);
Router::get('orders/:id', [new OrderController(), 'getOrderDetails']);
Router::put('orders/:id/status', [new OrderController(), 'updateOrderStatus']);

// 404 - Bulunamadı
Router::notFound(function() {
    return Response::notFound('Endpoint bulunamadı');
});

// Router'ı çalıştır
Router::resolve(); 