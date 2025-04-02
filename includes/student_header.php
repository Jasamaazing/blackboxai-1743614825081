<?php
require_once '../includes/auth.php';
require_auth();
if (!is_student()) {
    header('Location: /admin/dashboard.php');
    exit();
}

// Get student data
global $pdo;
$stmt = $pdo->prepare("SELECT * FROM users WHERE student_number = ?");
$stmt->execute([$_SESSION['student_number']]);
$student = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ecobots - <?php echo htmlspecialchars($title ?? 'Dashboard'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0fdf4;
        }
        .progress-bar {
            height: 1rem;
            background-color: #e5e7eb;
        }
        .progress-fill {
            height: 100%;
            background-color: #10b981;
            transition: width 0.5s ease;
        }
    </style>
</head>
<body>
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-green-800 text-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-recycle text-2xl"></i>
                        </div>
                        <div class="hidden md:block">
                            <div class="ml-10 flex items-baseline space-x-4">
                                <a href="/student/dashboard.php" class="px-3 py-2 rounded-md text-sm font-medium <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-green-700' : 'hover:bg-green-700'; ?>">Dashboard</a>
                                <a href="/student/profile.php" class="px-3 py-2 rounded-md text-sm font-medium <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'bg-green-700' : 'hover:bg-green-700'; ?>">Profile</a>
                                <a href="/student/rewards.php" class="px-3 py-2 rounded-md text-sm font-medium <?php echo basename($_SERVER['PHP_SELF']) == 'rewards.php' ? 'bg-green-700' : 'hover:bg-green-700'; ?>">Rewards</a>
                            </div>
                        </div>
                    </div>
                    <div class="hidden md:block">
                        <div class="ml-4 flex items-center md:ml-6">
                            <span class="text-sm font-medium mr-4"><?= htmlspecialchars($_SESSION['student_number']) ?></span>
                            <a href="/logout.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-green-700">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($title ?? 'Dashboard'); ?></h1>
                <?php if (isset($student['profile_picture'])): ?>
                    <img src="<?= htmlspecialchars($student['profile_picture']) ?>" alt="Profile" class="w-10 h-10 rounded-full">
                <?php else: ?>
                    <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center">
                        <i class="fas fa-user text-gray-500"></i>
                    </div>
                <?php endif; ?>
            </div>