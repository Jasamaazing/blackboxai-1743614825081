<?php
$title = 'Dashboard';
require_once '../includes/admin_header.php';
require_once '../includes/db.php';

// Get inventory stats
$inventory = $pdo->query("SELECT * FROM inventory LIMIT 1")->fetch();
$total_students = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_transactions = $pdo->query("SELECT COUNT(*) FROM transactions")->fetchColumn();

// Get data for charts
$weekly_deposits = $pdo->query("
    SELECT DATE(timestamp) as date, SUM(bottles_inserted) as bottles 
    FROM transactions 
    WHERE timestamp >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(timestamp)
    ORDER BY date
")->fetchAll();

$reward_distribution = $pdo->query("
    SELECT f.name, COUNT(t.reward_claimed) as claims
    FROM food_rewards f
    LEFT JOIN transactions t ON f.reward_id = t.reward_claimed
    GROUP BY f.reward_id
")->fetchAll();
?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-green-50 p-6 rounded-lg shadow">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                <i class="fas fa-recycle text-xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Total Bottles</p>
                <p class="text-2xl font-semibold text-gray-800"><?= number_format($inventory['bottle_count']) ?></p>
            </div>
        </div>
    </div>

    <div class="bg-blue-50 p-6 rounded-lg shadow">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                <i class="fas fa-dollar-sign text-xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Total Profit</p>
                <p class="text-2xl font-semibold text-gray-800">$<?= number_format($inventory['profit_earned'], 2) ?></p>
            </div>
        </div>
    </div>

    <div class="bg-purple-50 p-6 rounded-lg shadow">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                <i class="fas fa-users text-xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Registered Students</p>
                <p class="text-2xl font-semibold text-gray-800"><?= number_format($total_students) ?></p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-4">Bottle Deposits (Last 7 Days)</h2>
        <canvas id="depositsChart" height="300"></canvas>
    </div>

    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-4">Reward Distribution</h2>
        <canvas id="rewardsChart" height="300"></canvas>
    </div>
</div>

<script>
// Weekly deposits chart
const depositsCtx = document.getElementById('depositsChart').getContext('2d');
const depositsChart = new Chart(depositsCtx, {
    type: 'line',
    data: {
        labels: [<?php 
            foreach ($weekly_deposits as $day) {
                echo '"' . date('D', strtotime($day['date'])) . '",';
            }
        ?>],
        datasets: [{
            label: 'Bottles Deposited',
            data: [<?php 
                foreach ($weekly_deposits as $day) {
                    echo $day['bottles'] . ',';
                }
            ?>],
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            borderColor: 'rgba(16, 185, 129, 1)',
            borderWidth: 2,
            tension: 0.3,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Reward distribution chart
const rewardsCtx = document.getElementById('rewardsChart').getContext('2d');
const rewardsChart = new Chart(rewardsCtx, {
    type: 'doughnut',
    data: {
        labels: [<?php 
            foreach ($reward_distribution as $reward) {
                echo '"' . $reward['name'] . '",';
            }
        ?>],
        datasets: [{
            data: [<?php 
                foreach ($reward_distribution as $reward) {
                    echo $reward['claims'] . ',';
                }
            ?>],
            backgroundColor: [
                'rgba(16, 185, 129, 0.8)',
                'rgba(99, 102, 241, 0.8)',
                'rgba(245, 158, 11, 0.8)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'right'
            }
        }
    }
});
</script>

<?php include '../includes/admin_footer.php'; ?>