<?php
// SQLite database connection
define('DB_PATH', __DIR__ . '/../db/ecobots.db');

try {
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Create tables if they don't exist
    // Check if tables exist before creating
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('users', $tables)) {
        $pdo->exec(file_get_contents(__DIR__ . '/../db/ecobots.sqlite.sql'));
    } else {
        // Check if we need to alter existing users table
        $columns = $pdo->query("PRAGMA table_info(users)")->fetchAll();
        $columnNames = array_column($columns, 'name');
        
        // Check if we need to alter existing users table
        if (!in_array('first_name', $columnNames)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN first_name TEXT NOT NULL DEFAULT ''");
        }
        if (!in_array('last_name', $columnNames)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN last_name TEXT NOT NULL DEFAULT ''");
        }
        if (!in_array('email', $columnNames)) {
            try {
                $pdo->exec("ALTER TABLE users ADD COLUMN email TEXT NOT NULL DEFAULT ''");
                $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_users_email ON users(email)");
            } catch (PDOException $e) {
                error_log("Database schema update error: " . $e->getMessage());
            }
        }
    }
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Helper function to generate CSRF token
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Helper function to verify CSRF token
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>