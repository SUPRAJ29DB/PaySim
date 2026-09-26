<?php
/**
 * PaySim - Transaction & Reference ID Generator
 */

class TransactionID {
    public static function generateTxnId(): string {
        // e.g. TXN260926894012 (Year-Month-Day + random hex uppercase)
        $datePart = date('ymd');
        $randomPart = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        return 'TXN' . $datePart . $randomPart;
    }

    public static function generateUtr(): string {
        // 12-digit standard NPCI/RBI UTR number
        // Format: [Year digit][Julian Day (3 digits)][Bank code/hour (2 digits)][Sequence (6 digits)]
        $yearDigit = substr(date('Y'), -1);
        $julianDay = str_pad((string)date('z'), 3, '0', STR_PAD_LEFT);
        $randomSeq = str_pad((string)random_int(10000000, 99999999), 8, '0', STR_PAD_LEFT);
        return 'UTR' . $yearDigit . $julianDay . substr($randomSeq, 0, 8);
    }

    public static function generateRefNo(): string {
        // PaySim internal reference
        return 'PSM' . date('YmdHis') . random_int(100, 999);
    }

    public static function generateRequestCode(): string {
        return 'REQ' . strtoupper(substr(bin2hex(random_bytes(5)), 0, 8));
    }

    public static function generateAccountNumber(): string {
        return 'ACC' . date('y') . random_int(10000000, 99999999);
    }
}
