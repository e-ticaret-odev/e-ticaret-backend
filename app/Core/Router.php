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

    private static function addRoute($method, $route, $handler) {
        // Route formatını düzenle (başta ve sonda / kontrolü)
        $route = trim($route, '/');
        self::$routes[$method][$route] = $handler;
    }

    public static function notFound($handler) {
        self::$notFoundHandler = $handler;
    }

    public static function resolve() {
        // İstek metodu ve URI'yi al
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        
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
            header('HTTP/1.1 404 Not Found');
            echo json_encode(['error' => 'Endpoint bulunamadı']);
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