<?php
require_once '../includes/admin_header.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!is_admin()) {
    header('Location: /login.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($password)) {
        $error = 'Username and password are required';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        try {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO admin (username, password_hash) VALUES (?, ?)");
            if ($stmt->execute([$username, $password_hash]) && $stmt->rowCount() > 0) {
                $success = 'Admin account created successfully';
                error_log("Admin created: $username");
            } else {
                $error = 'Failed to create admin account';
                error_log("Admin creation failed: " . implode(" ", $stmt->errorInfo()));
            }
        } catch (PDOException $e) {
            $error = 'Failed to create admin: ' . (strpos($e->getMessage(), 'UNIQUE') ? 'Username already exists' : 'Database error');
            error_log("PDOException: " . $e->getMessage());
        }
    }
}
?>

<div class="container mt-5">
    <h2>Create New Admin</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
        </div>
        <button type="submit" class="btn btn-primary">Create Admin</button>
    </form>
</div>

<?php require_once '../includes/admin_footer.php'; ?>