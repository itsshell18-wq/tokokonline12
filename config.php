
config_php = """<?php
// config.php - Konfigurasi Database dan Sistem
session_start();

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Sesuaikan dengan password MySQL Anda
define('DB_NAME', 'toko_online');

// Koneksi Database
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Koneksi gagal: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
}

// Fungsi helper
define('BASE_URL', 'http://localhost/toko-online/');
define('UPLOAD_PATH', 'uploads/');

function db() {
    return Database::getInstance()->getConnection();
}

function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function generateOrderCode() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

function flashMessage($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function showFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return "<div class='alert alert-{$flash['type']}'>{$flash['message']}</div>";
    }
    return '';
}

// CSRF Protection
function generateCSRF() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
"""

with open('/mnt/kimi/output/config.php', 'w', encoding='utf-8') as f:
    f.write(config_php)

print("✅ File config.php berhasil dibuat!")
print("\n📋 Fitur Config:")
print("- Koneksi PDO dengan error handling")
print("- Singleton pattern untuk efisiensi")
print("- Helper functions (redirect, formatRupiah, dll)")
print("- CSRF Protection")
print("- Session management")
