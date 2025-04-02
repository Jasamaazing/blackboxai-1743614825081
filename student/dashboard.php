<?php
$title = 'Dashboard';
require_once '../includes/student_header.php';
require_once '../includes/db.php';

// Get student's total bottles and recent transactions
$student_number = $_SESSION['student_number'];
$total_bottles = 0;
$recent_transactions = [];
$rewards = [];

try {
    // Get total bottles
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(bottles_inserted), 0) FROM transactions WHERE student_number = ?");
    $stmt->execute([$student_number]);
    $total_bottles = $stmt->fetchColumn() ?: 0;

    // Get recent transactions
    $stmt = $pdo->prepare("
        SELECT t.*, f.name as reward_name 
        FROM transactions t
        LEFT JOIN food_rewards f ON t.reward_claimed = f.reward_id
        WHERE t.student_number = ?
        ORDER BY t.timestamp DESC
        LIMIT 5
    ");
    $stmt->execute([$student_number]);
    $recent_transactions = $stmt->fetchAll();

    // Get available rewards
    $rewards = $pdo->query("
        SELECT * FROM food_rewards 
        WHERE stock_A > 0 OR stock_B > 0
        ORDER BY price_in_bottles
    ")->fetchAll();
} catch (PDOException $e) {
    error_log("Dashboard Error: " . $e->getMessage());
}
?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-4">Your Bottle Count</h2>
        <div class="text-center">
            <div class="text-5xl font-bold text-green-600 mb-2"><?= $total_bottles ?></div>
            <p class="text-gray-500">bottles recycled</p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow md:col-span-2">
        <h2 class="text-lg font-semibold mb-4">Available Rewards</h2>
        <?php if (empty($rewards)): ?>
            <p class="text-gray-500">No rewards available at the moment.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php foreach ($rewards as $reward): ?>
                    <div class="border rounded-lg p-4 <?= $total_bottles >= $reward['price_in_bottles'] ? 'border-green-300 bg-green-50' : 'border-gray-200' ?>">
                        <h3 class="font-medium text-lg"><?= htmlspecialchars($reward['name']) ?></h3>
                        <p class="text-gray-600 text-sm mb-2"><?= htmlspecialchars($reward['description']) ?></p>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium">
                                Price: <span class="text-green-600"><?= $reward['price_in_bottles'] ?> bottles</span>
                            </span>
                            <?php if ($total_bottles >= $reward['price_in_bottles']): ?>
                                <a href="claim_reward.php?reward_id=<?= $reward['reward_id'] ?>" 
                                   class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                                    Claim
                                </a>
                            <?php else: ?>
                                <span class="text-sm text-gray-500">
                                    Need <?= $reward['price_in_bottles'] - $total_bottles ?> more
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="bg-white p-6 rounded-lg shadow mb-6">
    <h2 class="text-lg font-semibold mb-4">Recent Activity</h2>
    <?php if (empty($recent_transactions)): ?>
        <p class="text-gray-500">No recent activity.</p>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($recent_transactions as $transaction): ?>
                <div class="border-b border-gray-100 pb-4 last:border-0 last:pb-0">
                    <div class="flex justify-between">
                        <div>
                            <span class="font-medium"><?= $transaction['bottles_inserted'] ?> bottles</span>
                            <?php if ($transaction['reward_claimed']): ?>
                                <span class="text-gray-500"> → claimed <?= htmlspecialchars($transaction['reward_name']) ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="text-sm text-gray-500">
                            <?= date('M j, g:i A', strtotime($transaction['timestamp'])) ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="mt-4 text-right">
            <a href="transactions.php" class="text-sm text-green-600 hover:text-green-800">View All Activity &rarr;</a>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/student_footer.php'; ?>