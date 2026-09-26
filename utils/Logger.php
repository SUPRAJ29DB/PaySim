<?php
/**
 * PaySim - Application File Logger Utility
 */

class Logger {
    private static string $logFile = ROOT_PATH . '/logs/paysim.log';

    public static function log(string $level, string $message, array $context = []): void {
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_SLASHES) : '';
        $formatted = sprintf("[%s] [%s] %s%s%s", $timestamp, strtoupper($level), $message, $contextStr, PHP_EOL);

        file_put_contents(self::$logFile, $formatted, FILE_APPEND | LOCK_EX);
    }

    public static function info(string $message, array $context = []): void {
        self::log('INFO', $message, $context);
    }

    public static function error(string $message, array $context = []): void {
        self::log('ERROR', $message, $context);
    }

    public static function warning(string $message, array $context = []): void {
        self::log('WARNING', $message, $context);
    }

    public static function security(string $message, array $context = []): void {
        self::log('SECURITY', $message, $context);
    }
}
