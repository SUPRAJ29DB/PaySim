<?php
/**
 * PaySim - Global Helper Functions & Utilities
 */

class Helpers {
    public static function formatCurrency(float $amount, string $symbol = '₹'): string {
        $formatted = number_format($amount, 2, '.', ',');
        // Convert to Indian number comma format
        $parts = explode('.', $formatted);
        $intPart = $parts[0];
        $decPart = $parts[1] ?? '00';

        $intClean = str_replace(',', '', $intPart);
        if (strlen($intClean) > 3) {
            $lastThree = substr($intClean, -3);
            $restUnits = substr($intClean, 0, -3);
            $restFormatted = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $restUnits);
            $intPart = $restFormatted . ',' . $lastThree;
        }

        return $symbol . $intPart . '.' . $decPart;
    }

    public static function timeAgo(string $datetime): string {
        $time = strtotime($datetime);
        $diff = time() - $time;

        if ($diff < 60) {
            return 'Just now';
        }
        if ($diff < 3600) {
            $mins = floor($diff / 60);
            return $mins . ' min' . ($mins > 1 ? 's' : '') . ' ago';
        }
        if ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hr' . ($hours > 1 ? 's' : '') . ' ago';
        }
        if ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        }
        return date('d M Y', $time);
    }

    public static function getInitials(string $name): string {
        $words = explode(' ', trim($name));
        $initials = '';
        foreach ($words as $w) {
            if (!empty($w)) {
                $initials .= strtoupper(substr($w, 0, 1));
            }
            if (strlen($initials) >= 2) {
                break;
            }
        }
        return $initials ?: 'U';
    }

    public static function getAvatarColor(string $name): string {
        $colors = ['#6366f1', '#10b981', '#f59e0b', '#8b5cf6', '#06b6d4', '#ec4899', '#3b82f6'];
        $index = abs(crc32($name)) % count($colors);
        return $colors[$index];
    }

    public static function setFlash(string $type, string $message): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash'] = [
            'type'    => $type, // 'success', 'error', 'info', 'warning'
            'message' => $message,
        ];
    }

    public static function getFlash(): ?array {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }

    public static function redirect(string $url): void {
        header("Location: {$url}");
        exit();
    }
}
