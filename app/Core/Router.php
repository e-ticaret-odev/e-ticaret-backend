<?php

namespace App\Core;

class Router {
    private static $routes = [];
    private static $notFoundHandler = null;

    public static function get($route, $handler) {
        self::addRoute('GET', $route, $handler);
    }

    public static function post($route, $handler) {
        self::addRoute('POST', $route, $handler);
    }

    public static function put($route, $handler) {
        self::addRoute('PUT', $route, $handler);
    }

    public static function delete($route, $handler) {
        self::addRoute('DELETE', $route, $handler);
    }

    public static function options($route, $handler) {
        self::addRoute('OPTIONS', $route, $handler);
    }

    private static function addRoute($method, $route, $handler) {
        // Route formatını düzenle (başta ve sonda / kontrolü)
        $route = trim($route, '/');
        self::$routes[$method][$route] = $handler;
    }

    public static function notFound($handler) {
        self::$notFoundHandler = $handler;
    }

    private static function setCorsHeaders() {
        // İzin verilen originleri tanımla
        $allowedOrigins = [
            'http://localhost:5000',   // Frontend domain
            'http://localhost:3000',   // Olası diğer geliştirme ortamı
            'http://127.0.0.1:5000',   // Alternatif lokal adres
            'http://127.0.0.1:3000',   // Alternatif lokal adres
            'null'                     // Yerel dosya sistemi için
        ];
        
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        
        // Geliştirme aşamasında hızlı çözüm için tüm originlere izin ver
        header('Access-Control-Allow-Origin: *');
        
        // İzin verilen originler listesini kontrol et
        // if (in_array($origin, $allowedOrigins)) {
        //     header("Access-Control-Allow-Origin: {$origin}");
        // } else {
        //     // Geliştirme aşamasında tüm originlere izin ver (canlı ortamda kaldırılmalı)
        //     header('Access-Control-Allow-Origin: *');
        // }
        
        // Diğer CORS başlıklarını ayarla
        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept, Origin, Authorization');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400'); // 24 saat
    }

    private static function handleOptionsRequest($uri) {
        // OPTIONS istekleri için CORS başlıklarını ayarla ve 200 OK yanıtı gönder
        self::setCorsHeaders();
        header('Content-Length: 0');
        header('Content-Type: text/plain');
        http_response_code(200);
        exit(0);
    }

    public static function resolve() {
        // İstek metodu ve URI'yi al
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        
        // OPTIONS istekleri için özel işleme
        if ($method === 'OPTIONS') {
            self::handleOptionsRequest($uri);
            return;
        }
        
        // Base path'i ayırma (eğer varsa)
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = trim($uri, '/');
        
        // API prefix'i kontrol et ve kaldır
        if (strpos($uri, 'api/') === 0) {
            $uri = substr($uri, 4);
        }
        
        // Route'u bul
        if (isset(self::$routes[$method][$uri])) {
            $handler = self::$routes[$method][$uri];
            echo self::executeHandler($handler);
            return;
        }
        
        // Dinamik route'ları kontrol et
        foreach (self::$routes[$method] ?? [] as $route => $handler) {
            $pattern = '@^' . preg_replace('/\:([a-zA-Z0-9_]+)/', '(?<$1>[^/]+)', $route) . '$@';
            
            if (preg_match($pattern, $uri, $matches)) {
                // URL parametrelerini ayıkla
                $params = array_filter($matches, function($key) {
                    return !is_numeric($key);
                }, ARRAY_FILTER_USE_KEY);
                
                echo self::executeHandler($handler, $params);
                return;
            }
        }
        
        // Route bulunamadı
        if (self::$notFoundHandler) {
            echo self::executeHandler(self::$notFoundHandler);
        } else {
            self::setCorsHeaders(); // 404 yanıtlarında da CORS başlıkları ekle
            header('HTTP/1.1 404 Not Found');
            echo json_encode(['success' => false, 'error' => 'Endpoint bulunamadı']);
        }
    }
    
    private static function executeHandler($handler, $params = []) {
        if (is_callable($handler)) {
            return call_user_func_array($handler, $params);
        }
        
        if (is_string($handler)) {
            if (strpos($handler, '@') !== false) {
                [$controller, $method] = explode('@', $handler);
                $controller = "\\App\\Controllers\\$controller";
                $instance = new $controller();
                return call_user_func_array([$instance, $method], $params);
            }
        }
        
        if (is_array($handler) && count($handler) == 2) {
            [$controller, $method] = $handler;
            $instance = new $controller();
            return call_user_func_array([$instance, $method], $params);
        }
        
        throw new \Exception('Geçersiz route handler');
    }
} 