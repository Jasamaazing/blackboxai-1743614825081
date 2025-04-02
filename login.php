<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Redirect if already logged in
redirect_if_authenticated();

// Check for admin login request
$isAdminLogin = isset($_GET['admin']) && $_GET['admin'] == 1;

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Validate CSRF token
    if (!verify_csrf_token($csrf_token)) {
        $error = 'Invalid CSRF token';
    } elseif (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        // Check login type
        if ($isAdminLogin) {
            if (authenticate_admin($username, $password)) {
                header('Location: /admin/dashboard.php');
                exit();
            }
        } else {
            if (authenticate_student($username, $password)) {
                header('Location: /student/dashboard.php');
                exit();
            }
        }
        
        // If we get here, authentication failed
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ecobots - <?= $isAdminLogin ? 'Admin' : 'Student' ?> Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0fdf4;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md p-8 space-y-8 bg-white rounded-lg shadow-md">
        <div class="text-center">
            <h1 class="text-3xl font-bold text-green-600">Ecobots</h1>
            <p class="mt-2 text-gray-600"><?= $isAdminLogin ? 'Admin Portal' : 'Student Portal' ?></p>
        </div>

        <?php if ($error): ?>
            <div class="p-4 text-sm text-red-700 bg-red-100 rounded-lg">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <div class="rounded-md shadow-sm space-y-4">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700"><?= $isAdminLogin ? 'Admin Username' : 'Student Number' ?></label>
                    <input id="username" name="username" type="text" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                        placeholder="<?= $isAdminLogin ? 'Enter admin username' : 'Enter student number' ?>">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <div class="relative">
                        <input id="password" name="password" type="password" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                            placeholder="Enter your password">
                        <button type="button" onclick="togglePassword('password')" 
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
            </div>

            <div>
                <button type="submit"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    Sign in
                </button>
            </div>
        </form>

        <div class="mt-4 text-center space-y-2">
            <?php if ($isAdminLogin): ?>
                <p class="text-sm text-gray-600">Student login? <a href="login.php" class="text-green-600 hover:text-green-800">Click here</a></p>
            <?php else: ?>
                <p class="text-sm text-gray-600">Admin login? <a href="login.php?admin=1" class="text-green-600 hover:text-green-800">Click here</a></p>
                <p class="text-sm text-gray-600">Don't have an account? <a href="signup.php" class="text-green-600 hover:text-green-800">Sign up here</a></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
