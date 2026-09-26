<?php
/**
 * PaySim - Database Connection Manager (PDO)
 * PDO database connection manager. Configure the driver explicitly.
 */

require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/environment.php';

class Database {
    private static ?Database $instance = null;
    private ?PDO $pdo = null;
    private string $driver = 'mysql';

    private function __construct() {
        $host     = env('DB_HOST', '127.0.0.1');
        $port     = env('DB_PORT', '3306');
        $dbName   = env('DB_NAME', 'paysim');
        $user     = env('DB_USER', 'root');
        $password = env('DB_PASS', '');
        $preferredDriver = strtolower((string)env('DB_DRIVER', 'mysql')); // explicitly choose mysql or sqlite

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        if ($preferredDriver === 'sqlite') {
            $this->connectSqlite($options);
            return;
        }

        if ($preferredDriver !== 'mysql') {
            throw new RuntimeException('Unsupported DB_DRIVER. Set DB_DRIVER=mysql or DB_DRIVER=sqlite explicitly.');
        }

        $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
        try {
            $this->pdo = new PDO($dsn, $user, $password, $options);
            $this->driver = 'mysql';
        } catch (PDOException $e) {
            error_log('[PaySim database] MySQL connection failed: ' . $e->getMessage());
            throw new RuntimeException('Unable to connect to the configured MySQL database. Verify DB_HOST, DB_PORT, DB_NAME, DB_USER, and DB_PASS, then import sql/paysim.sql.', 0, $e);
        }
    }

    private function connectSqlite(array $options): void {
        $sqliteDir = ROOT_PATH . '/sql';
        if (!is_dir($sqliteDir)) {
            mkdir($sqliteDir, 0777, true);
        }
        $sqliteFile = $sqliteDir . '/paysim.sqlite';
        $dsn = "sqlite:" . $sqliteFile;
        $this->pdo = new PDO($dsn, null, null, $options);
        $this->pdo->exec('PRAGMA foreign_keys = ON;');
        $this->driver = 'sqlite';

        // Auto initialize tables if not created
        $this->ensureSqliteSchema();
    }

    private function ensureSqliteSchema(): void {
        $check = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'")->fetch();
        if (!$check) {
            $this->initSchema();
        }
    }

    public function initSchema(): void {
        if ($this->driver === 'sqlite') {
            $schema = "
                CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    full_name VARCHAR(100) NOT NULL,
                    username VARCHAR(50) UNIQUE NOT NULL,
                    email VARCHAR(100) UNIQUE NOT NULL,
                    phone VARCHAR(20) UNIQUE NOT NULL,
                    password_hash VARCHAR(255) NOT NULL,
                    upi_id VARCHAR(50) UNIQUE NOT NULL,
                    upi_pin_hash VARCHAR(255) DEFAULT NULL,
                    avatar VARCHAR(255) DEFAULT NULL,
                    role VARCHAR(20) DEFAULT 'user',
                    status VARCHAR(20) DEFAULT 'active',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS accounts (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    account_number VARCHAR(30) UNIQUE NOT NULL,
                    account_type VARCHAR(20) DEFAULT 'wallet',
                    balance DECIMAL(15,2) DEFAULT 0.00,
                    currency VARCHAR(10) DEFAULT 'INR',
                    status VARCHAR(20) DEFAULT 'active',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                );

                CREATE TABLE IF NOT EXISTS bank_accounts (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    bank_name VARCHAR(100) NOT NULL,
                    account_number VARCHAR(30) NOT NULL,
                    ifsc_code VARCHAR(20) NOT NULL,
                    branch VARCHAR(100) DEFAULT '',
                    account_type VARCHAR(30) DEFAULT 'Savings Account',
                    balance DECIMAL(15,2) DEFAULT 50000.00,
                    is_primary INTEGER DEFAULT 0,
                    color_gradient VARCHAR(100) DEFAULT '',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                );

                CREATE TABLE IF NOT EXISTS transactions (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    txn_id VARCHAR(50) UNIQUE NOT NULL,
                    ref_no VARCHAR(50) UNIQUE NOT NULL,
                    utr VARCHAR(50) UNIQUE NOT NULL,
                    sender_id INTEGER NULL,
                    receiver_id INTEGER NULL,
                    sender_upi VARCHAR(50) NOT NULL,
                    receiver_upi VARCHAR(50) NOT NULL,
                    sender_name VARCHAR(100) NOT NULL,
                    receiver_name VARCHAR(100) NOT NULL,
                    amount DECIMAL(15,2) NOT NULL,
                    fee DECIMAL(10,2) DEFAULT 0.00,
                    cashback DECIMAL(10,2) DEFAULT 0.00,
                    txn_type VARCHAR(20) DEFAULT 'transfer',
                    status VARCHAR(20) DEFAULT 'completed',
                    note TEXT,
                    category VARCHAR(50) DEFAULT 'Transfer',
                    payment_method VARCHAR(50) DEFAULT 'UPI',
                    device_info VARCHAR(255) DEFAULT 'PaySim Web Client',
                    ip_address VARCHAR(45) DEFAULT '127.0.0.1',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS payment_requests (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    request_code VARCHAR(50) UNIQUE NOT NULL,
                    requester_id INTEGER NOT NULL,
                    payer_id INTEGER NULL,
                    requester_upi VARCHAR(50) NOT NULL,
                    payer_upi VARCHAR(50) NOT NULL,
                    amount DECIMAL(15,2) NOT NULL,
                    note TEXT,
                    status VARCHAR(20) DEFAULT 'pending',
                    expires_at DATETIME NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS notifications (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    title VARCHAR(150) NOT NULL,
                    message TEXT NOT NULL,
                    type VARCHAR(30) DEFAULT 'info',
                    is_read INTEGER DEFAULT 0,
                    action_url VARCHAR(255) DEFAULT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                );

                CREATE TABLE IF NOT EXISTS audit_logs (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NULL,
                    action VARCHAR(100) NOT NULL,
                    entity_type VARCHAR(50) NOT NULL,
                    entity_id VARCHAR(50) NULL,
                    ip_address VARCHAR(45) DEFAULT '127.0.0.1',
                    user_agent TEXT,
                    payload TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS system_settings (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    setting_key VARCHAR(100) UNIQUE NOT NULL,
                    setting_value TEXT NOT NULL,
                    description VARCHAR(255) NULL,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );
            ";
            $this->pdo->exec($schema);
            $this->seedInitialData();
        }
    }

    private function seedInitialData(): void {
        // Seed default users if empty
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM users");
        $count = (int) ($stmt->fetch()['count'] ?? 0);
        if ($count === 0) {
            $pass1 = password_hash('pay@123', PASSWORD_DEFAULT);
            $pin1  = password_hash('123456', PASSWORD_DEFAULT);
            $pass2 = password_hash('demo@123', PASSWORD_DEFAULT);
            $pin2  = password_hash('123456', PASSWORD_DEFAULT);

            $this->pdo->exec("
                INSERT INTO users (full_name, username, email, phone, password_hash, upi_id, upi_pin_hash, role)
                VALUES 
                ('Suprakash Ghosh', 'suprakash', 'suprakash@paysim.com', '+91 9876543210', '{$pass1}', 'suprakash@paysim', '{$pin1}', 'user'),
                ('Demo User', 'demo', 'demo@paysim.com', '+91 9876500000', '{$pass2}', 'demo@paysim', '{$pin2}', 'user');

                INSERT INTO accounts (user_id, account_number, account_type, balance)
                VALUES 
                (1, 'ACC100019283', 'wallet', 24580.75),
                (2, 'ACC100019284', 'wallet', 15000.00);

                INSERT INTO bank_accounts (user_id, bank_name, account_number, ifsc_code, branch, is_primary, balance, color_gradient)
                VALUES
                (1, 'State Bank of India', '•••• 4829', 'SBIN0001234', 'Main Branch, Kolkata', 1, 142500.50, 'linear-gradient(135deg, #1e3c72 0%, #2a5298 100%)'),
                (1, 'HDFC Bank', '•••• 9102', 'HDFC0004567', 'Park Street, Kolkata', 0, 89200.00, 'linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%)'),
                (1, 'ICICI Bank', '•••• 1109', 'ICIC0008910', 'Salt Lake, Kolkata', 0, 23150.25, 'linear-gradient(135deg, #833ab4 0%, #fd1d1d 50%, #fcb045 100%)');
            ");
        }
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->pdo;
    }

    public function getDriverName(): string {
        return $this->driver;
    }

    public function isSqlite(): bool {
        return $this->driver === 'sqlite';
    }
}
