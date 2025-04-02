<?php
require_once 'db.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_type']);
}

// Check if user is admin
function is_admin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

// Check if user is student
function is_student() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'student';
}

// Redirect to login if not authenticated
function require_auth() {
    if (!is_logged_in()) {
        header('Location: /login.php');
        exit();
    }
}

// Redirect admin to dashboard if already logged in
function redirect_if_authenticated() {
    if (is_logged_in()) {
        if (is_admin()) {
            header('Location: /admin/dashboard.php');
        } else {
            header('Location: /student/dashboard.php');
        }
        exit();
    }
}

// Authenticate student
function authenticate_student($student_number, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE student_number = ?");
    $stmt->execute([$student_number]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_type'] = 'student';
        $_SESSION['student_number'] = $student_number;
        return true;
    }
    return false;
}

// Authenticate admin
function authenticate_admin($username, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['user_type'] = 'admin';
        $_SESSION['admin_id'] = $admin['admin_id'];
        return true;
    }
    return false;
}

// Logout function
function logout() {
    $_SESSION = array();
    session_destroy();
}

// Create new student account
function create_student_account($student_number, $password) {
    global $pdo;
    
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (student_number, password_hash) VALUES (?, ?)");
    return $stmt->execute([$student_number, $password_hash]);
}
?>