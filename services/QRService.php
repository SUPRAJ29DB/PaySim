<?php
/**
 * PaySim - UPI QR Code Generation & Parsing Service
 */

require_once dirname(__DIR__) . '/utils/UPIValidator.php';

class QRService {
    public static function generateUpiUri(string $upiId, string $name, ?float $amount = null, ?string $note = null): string {
        $cleanUpi  = strtolower(trim($upiId));
        $cleanName = trim($name);

        $params = [
            'pa' => $cleanUpi,
            'pn' => $cleanName,
            'cu' => 'INR',
        ];

        if ($amount !== null && $amount > 0) {
            $params['am'] = number_format($amount, 2, '.', '');
        }
        if (!empty($note)) {
            $params['tn'] = trim($note);
        }

        return 'upi://pay?' . http_build_query($params);
    }

    public static function parseUpiUri(string $uri): ?array {
        $uri = trim($uri);
        if (!str_starts_with($uri, 'upi://pay?')) {
            // Check if user simply provided a plain UPI ID
            if (UPIValidator::validate($uri)) {
                return [
                    'upi_id' => strtolower($uri),
                    'name'   => '',
                    'amount' => null,
                    'note'   => '',
                ];
            }
            return null;
        }

        $query = parse_url($uri, PHP_URL_QUERY);
        if (!$query) {
            return null;
        }

        parse_str($query, $params);

        $upiId = $params['pa'] ?? null;
        if (!$upiId || !UPIValidator::validate($upiId)) {
            return null;
        }

        return [
            'upi_id' => strtolower($upiId),
            'name'   => $params['pn'] ?? '',
            'amount' => !empty($params['am']) ? (float)$params['am'] : null,
            'note'   => $params['tn'] ?? '',
        ];
    }

    public static function getQrImageUrl(string $upiUri, int $size = 280): string {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size . '&data=' . urlencode($upiUri) . '&format=svg';
    }
}
