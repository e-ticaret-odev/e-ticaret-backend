<?php

namespace App\Core;

class Response {
    public static function json($data, $status = 200) {
        http_response_code($status);
        return json_encode($data);
    }

    public static function success($data = [], $message = 'İşlem başarılı', $status = 200) {
        return self::json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    public static function error($message = 'Bir hata oluştu', $status = 400) {
        return self::json([
            'success' => false,
            'error' => $message
        ], $status);
    }

    public static function notFound($message = 'Kayıt bulunamadı') {
        return self::error($message, 404);
    }

    public static function unauthorized($message = 'Yetkisiz erişim') {
        return self::error($message, 401);
    }

    public static function forbidden($message = 'Bu işlem için yetkiniz yok') {
        return self::error($message, 403);
    }
} 