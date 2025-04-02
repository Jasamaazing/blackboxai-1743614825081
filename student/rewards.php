<?php
require_once '../includes/student_header.php';
require_once '../includes/db.php';

$title = "Available Rewards";

// Get student's bottle count
$student_number = $_SESSION['student_number'];
$total_bottles = 0;
try {
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(bottles_inserted), 0) FROM transactions WHERE student_number = ?");
    $stmt->execute([$student_number]);
    $total_bottles = $stmt->fetchColumn() ?: 0;
} catch (PDOException $e) {
    error_log("Rewards Error: " . $e->getMessage());
}

// Get available rewards
$rewards = [];
try {
    $rewards = $pdo->query("
        SELECT * FROM food_rewards 
        WHERE stock_A > 0 OR stock_B > 0
        ORDER BY price_in_bottles
    ")->fetchAll();
} catch (PDOException $e) {
    error_log("Rewards Error: " . $e->getMessage());
}
?>

<div class="bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-6">Available Rewards</h1>
    
    <?php if (empty($rewards)): ?>
        <p class="text-gray-500">No rewards available at the moment.</p>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
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

<?php include '../includes/student_footer.php'; ?>