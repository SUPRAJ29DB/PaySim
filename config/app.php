<?php
/**
 * PaySim - Application Bootstrap & Configuration
 */

require_once __DIR__ . '/environment.php';
require_once __DIR__ . '/constants.php';

// Safe Session Start
if (session_status() === PHP_SESSION_NONE) {
    // Configure session security parameters before starting
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', '1');
    }
    session_start();
}

// Simple Class Autoloader for Models, Services, Utils, Middleware
spl_autoload_register(function ($className) {
    $directories = [
        ROOT_PATH . '/models/',
        ROOT_PATH . '/services/',
        ROOT_PATH . '/utils/',
        ROOT_PATH . '/middleware/',
    ];

    foreach ($directories as $dir) {
        $file = $dir . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

return [
    'name'        => APP_NAME,
    'version'     => APP_VERSION,
    'url'         => APP_URL,
    'env'         => APP_ENV,
    'currency'    => 'INR',
    'symbol'      => '₹',
    'log_channel' => ROOT_PATH . '/logs/paysim.log',
];
