<?php
/**
 * PaySim - Database Connection & Schema Test Suite
 */

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/database.php';

class DatabaseTest {
    private PDO $pdo;
    private int $passed = 0;
    private int $failed = 0;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    private function assert(bool $condition, string $testName): void {
        if ($condition) {
            echo "  [PASS] {$testName}\n";
            $this->passed++;
        } else {
            echo "  [FAIL] {$testName}\n";
            $this->failed++;
        }
    }

    public function run(): bool {
        echo "=== Running Database Test Suite ===\n";

        // 1. Connection check
        $this->assert($this->pdo instanceof PDO, "PDO connection instance established");
        $driver = Database::getInstance()->getDriverName();
        echo "  [INFO] Active Database Driver: {$driver}\n";

        // 2. Tables existence check
        $expectedTables = [
            'users',
            'accounts',
            'bank_accounts',
            'transactions',
            'payment_requests',
            'notifications',
            'audit_logs',
            'system_settings'
        ];

        foreach ($expectedTables as $table) {
            if ($driver === 'sqlite') {
                $stmt = $this->pdo->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name = :tbl");
                $stmt->execute(['tbl' => $table]);
                $exists = (bool) $stmt->fetch();
            } else {
                $stmt = $this->pdo->prepare("SHOW TABLES LIKE :tbl");
                $stmt->execute(['tbl' => $table]);
                $exists = (bool) $stmt->fetch();
            }
            $this->assert($exists, "Table `{$table}` exists in database");
        }

        // 3. Insert and Rollback Transaction Integrity
        $this->pdo->beginTransaction();
        $stmt = $this->pdo->prepare("
            INSERT INTO users (full_name, username, email, phone, password_hash, upi_id, role, status)
            VALUES ('Rollback Test', 'rb_test_" . time() . "', 'rb" . time() . "@test.com', '999" . random_int(1000000, 9999999) . "', 'hash', 'rb" . time() . "@paysim', 'user', 'active')
        ");
        $stmt->execute();
        $insertedId = (int)$this->pdo->lastInsertId();
        $this->assert($insertedId > 0, "Insert in transaction returned valid lastInsertId ({$insertedId})");

        // Roll back
        $this->pdo->rollBack();

        $checkStmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
        $checkStmt->execute(['id' => $insertedId]);
        $row = $checkStmt->fetch();
        $this->assert(!$row, "Database atomic rollback verified (record successfully discarded)");

        echo "\nDatabase Tests: {$this->passed} Passed, {$this->failed} Failed\n\n";
        return ($this->failed === 0);
    }
}

// Direct execution from CLI
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $test = new DatabaseTest();
    exit($test->run() ? 0 : 1);
}
