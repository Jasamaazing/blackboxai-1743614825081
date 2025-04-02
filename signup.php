<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $studentNumber = trim($_POST['student_number']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Validate inputs
    if (empty($firstName) || empty($lastName) || empty($email) || 
        empty($studentNumber) || empty($password) || empty($confirmPassword)) {
        $error = 'All fields are required';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } else {
        try {
            // Check if student number or email already exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE student_number = ? OR email = ?");
            $stmt->execute([$studentNumber, $email]);
            
            if ($stmt->fetch()) {
                $error = 'Student number or email already exists';
            } else {
                // Create new student account
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                try {
                    // First verify the table structure exists
                    $requiredColumns = ['student_number', 'first_name', 'last_name', 'email', 'password_hash'];
                    $columns = $pdo->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_COLUMN, 1);
                    
                    foreach ($requiredColumns as $col) {
                        if (!in_array($col, $columns)) {
                            throw new PDOException("Missing required column: $col");
                        }
                    }

                    $stmt = $pdo->prepare("INSERT INTO users (student_number, first_name, last_name, email, password_hash) VALUES (?, ?, ?, ?, ?)");
                    if ($stmt->execute([$studentNumber, $firstName, $lastName, $email, $passwordHash])) {
                        $success = 'Account created successfully! You can now login.';
                    } else {
                        throw new PDOException("Failed to execute statement");
                    }
                } catch (PDOException $e) {
                    error_log("Signup Error: " . $e->getMessage());
                    if (strpos($e->getMessage(), 'no such column') !== false) {
                        $error = 'System configuration error. Please contact support.';
                    } elseif (strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
                        $error = 'This email or student number is already registered';
                    } else {
                        $error = 'System error during registration. Please try again.';
                    }
                }
            }
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            $error = 'Database error. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Sign Up | Ecobots</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
            <h1 class="text-2xl font-bold text-center mb-6">Student Sign Up</h1>
            
            <?php if ($error): ?>
                <div class="mb-4 p-3 bg-red-100 text-red-700 rounded"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="mb-4 p-3 bg-green-100 text-green-700 rounded"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                        <input type="text" id="first_name" name="first_name" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                        <input type="text" id="last_name" name="last_name" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" id="email" name="email" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                
                <div class="mb-4">
                    <label for="student_number" class="block text-sm font-medium text-gray-700 mb-1">Student Number</label>
                    <input type="text" id="student_number" name="student_number" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required minlength="8"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                        <button type="button" onclick="togglePassword('password')" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="mb-6">
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <div class="relative">
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                        <button type="button" onclick="togglePassword('confirm_password')" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                    <script>
                        function togglePassword(id) {
                            const input = document.getElementById(id);
                            const icon = input.nextElementSibling.querySelector('i');
                            if (input.type === 'password') {
                                input.type = 'text';
                                icon.classList.replace('fa-eye', 'fa-eye-slash');
                            } else {
                                input.type = 'password';
                                icon.classList.replace('fa-eye-slash', 'fa-eye');
                            }
                        }
                    </script>
                </div>
                
                <button type="submit" class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    Create Account
                </button>
            </form>
            
            <div class="mt-4 text-center">
                <p class="text-sm text-gray-600">Already have an account? <a href="login.php" class="text-green-600 hover:text-green-800">Log in here</a></p>
            </div>
        </div>
    </div>
</body>
</html>