<?php
/**
 * PaySim - Security & Cryptographic Protection Utility
 */

class Security {
    public static function generateCsrfToken(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrfToken(?string $token): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function sanitize(string $input): string {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    public static function hashPin(string $pin): string {
        return password_hash($pin, PASSWORD_DEFAULT);
    }

    public static function verifyPin(string $pin, string $hash): bool {
        return password_verify($pin, $hash);
    }

    public static function maskAccountNumber(string $accountNumber): string {
        $len = strlen($accountNumber);
        if ($len <= 4) {
            return $accountNumber;
        }
        return '•••• ' . substr($accountNumber, -4);
    }

    public static function getClientIp(): string {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    public static function getUserAgent(): string {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown Agent';
    }
}
