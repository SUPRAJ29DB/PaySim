<?php
/**
 * PaySim - Environment Configuration Loader
 */

// Define environment mode ('development', 'testing', 'production')
if (!defined('APP_ENV')) {
    define('APP_ENV', getenv('APP_ENV') ?: 'development');
}

// Error reporting based on environment
if (APP_ENV === 'development' || APP_ENV === 'testing') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(0);
}

// Timezone configuration (India Standard Time for UPI simulation)
date_default_timezone_set('Asia/Kolkata');

// Helper to get environment variables with fallback
if (!function_exists('env')) {
    function env(string $key, $default = null) {
        $val = getenv($key);
        if ($val === false) {
            return $default;
        }
        switch (strtolower($val)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }
        return $val;
    }
}
