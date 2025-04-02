<?php
require_once '../includes/auth.php';
require_auth();
if (!is_admin()) {
    header('Location: /student/dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ecobots Admin - <?php echo htmlspecialchars($title ?? 'Dashboard'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .sidebar {
            transition: all 0.3s;
        }
        @media (max-width: 768px) {
            .sidebar {
                position: absolute;
                z-index: 100;
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="sidebar bg-green-800 text-white w-64 space-y-6 py-7 px-2 fixed inset-y-0 left-0">
            <div class="flex items-center space-x-2 px-4">
                <i class="fas fa-recycle text-2xl"></i>
                <span class="text-2xl font-extrabold">Ecobots</span>
                <button class="md:hidden ml-auto text-white focus:outline-none" onclick="toggleSidebar()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <nav>
                <a href="/admin/dashboard.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-green-700 <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-green-700' : ''; ?>">
                    <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                </a>
                <a href="/admin/inventory.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-green-700 <?php echo basename($_SERVER['PHP_SELF']) == 'inventory.php' ? 'bg-green-700' : ''; ?>">
                    <i class="fas fa-boxes mr-2"></i>Inventory
                </a>
                <a href="/admin/rewards.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-green-700 <?php echo basename($_SERVER['PHP_SELF']) == 'rewards.php' ? 'bg-green-700' : ''; ?>">
                    <i class="fas fa-gift mr-2"></i>Rewards
                </a>
                <a href="/admin/transactions.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-green-700 <?php echo basename($_SERVER['PHP_SELF']) == 'transactions.php' ? 'bg-green-700' : ''; ?>">
                    <i class="fas fa-exchange-alt mr-2"></i>Transactions
                </a>
                <a href="/logout.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-green-700">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </a>
            </nav>
        </div>

        <!-- Mobile sidebar toggle -->
        <button class="md:hidden fixed top-4 left-4 z-50 bg-green-700 text-white p-2 rounded" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Main content -->
        <div class="flex-1 md:ml-64">
            <div class="p-4">
                <div class="bg-white rounded-lg shadow p-6">
                    <h1 class="text-2xl font-bold text-gray-800 mb-6"><?php echo htmlspecialchars($title ?? 'Dashboard'); ?></h1>
<script>
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('open');
}
</script>