<?php
/**
 * PaySim - Standardized API Response Handler
 */

class Response {
    public static function json(array $data = [], int $status = 200, array $headers = []): void {
        http_response_code($status);

        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');

        foreach ($headers as $key => $val) {
            header("{$key}: {$val}");
        }

        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        exit();
    }

    public static function success($data = null, string $message = 'Success', int $status = 200): void {
        self::json([
            'success'   => true,
            'message'   => $message,
            'data'      => $data,
            'timestamp' => date('c'),
        ], $status);
    }

    public static function error(string $message = 'Error occurred', int $status = 400, $errors = null): void {
        self::json([
            'success'   => false,
            'message'   => $message,
            'errors'    => $errors,
            'timestamp' => date('c'),
        ], $status);
    }

    public static function unauthorized(string $message = 'Authentication required'): void {
        self::error($message, 401);
    }

    public static function forbidden(string $message = 'Access forbidden'): void {
        self::error($message, 403);
    }

    public static function notFound(string $message = 'Resource not found'): void {
        self::error($message, 404);
    }

    public static function methodNotAllowed(string $message = 'Method not allowed'): void {
        self::error($message, 405);
    }
}
