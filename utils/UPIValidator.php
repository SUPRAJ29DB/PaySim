<?php
/**
 * PaySim - UPI ID (Virtual Payment Address) Validator & Parser
 */

class UPIValidator {
    // Recognized handles in Indian UPI Ecosystem & Simulator
    private static array $knownHandles = [
        'paysim', 'oksbi', 'okaxis', 'okicici', 'okhdfcbank', 'paytm', 'ybl', 'ibl', 'axl', 'barodampay'
    ];

    public static function validate(string $upiId): bool {
        $trimmed = trim(strtolower($upiId));
        if (!preg_match('/^[a-zA-Z0-9.\-_]{2,50}@[a-zA-Z0-9]{2,20}$/', $trimmed)) {
            return false;
        }
        return true;
    }

    public static function parse(string $upiId): ?array {
        if (!self::validate($upiId)) {
            return null;
        }
        [$username, $handle] = explode('@', strtolower(trim($upiId)), 2);
        return [
            'username' => $username,
            'handle'   => $handle,
            'vpa'      => strtolower(trim($upiId)),
            'is_local' => ($handle === 'paysim'),
            'bank_hint'=> self::getBankHint($handle),
        ];
    }

    public static function getBankHint(string $handle): string {
        return match (strtolower($handle)) {
            'paysim'     => 'PaySim Central Switch',
            'oksbi'      => 'State Bank of India',
            'okaxis', 'axl' => 'Axis Bank',
            'okicici', 'ibl'=> 'ICICI Bank',
            'okhdfcbank' => 'HDFC Bank',
            'paytm'      => 'Paytm Payments Bank',
            'ybl'        => 'YES Bank',
            default      => 'NPCI Unified Switch',
        };
    }
}
