<?php
$title = 'Inventory Management';
require_once '../includes/admin_header.php';
require_once '../includes/db.php';

// Handle form submission to update inventory
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_inventory'])) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (verify_csrf_token($csrf_token)) {
        $bottle_count = (int)$_POST['bottle_count'];
        $profit_earned = (float)$_POST['profit_earned'];
        
        $stmt = $pdo->prepare("UPDATE inventory SET bottle_count = ?, profit_earned = ?");
        $stmt->execute([$bottle_count, $profit_earned]);
        
        $_SESSION['success_message'] = 'Inventory updated successfully';
        header('Location: inventory.php');
        exit();
    } else {
        $error = 'Invalid CSRF token';
    }
}

// Get current inventory data
$inventory = $pdo->query("SELECT * FROM inventory LIMIT 1")->fetch();
?>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
        <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<div class="bg-white p-6 rounded-lg shadow">
    <h2 class="text-xl font-semibold mb-6">Update Inventory</h2>
    
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="bottle_count" class="block text-sm font-medium text-gray-700 mb-1">Bottle Count</label>
                <input type="number" id="bottle_count" name="bottle_count" 
                    value="<?php echo htmlspecialchars($inventory['bottle_count']); ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500" required>
            </div>
            
            <div>
                <label for="profit_earned" class="block text-sm font-medium text-gray-700 mb-1">Profit Earned ($)</label>
                <input type="number" step="0.01" id="profit_earned" name="profit_earned" 
                    value="<?php echo htmlspecialchars($inventory['profit_earned']); ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500" required>
            </div>
        </div>
        
        <div class="flex justify-end">
            <button type="submit" name="update_inventory"
                class="px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                Update Inventory
            </button>
        </div>
    </form>
</div>

<div class="bg-white p-6 rounded-lg shadow mt-6">
    <h2 class="text-xl font-semibold mb-6">Recent Transactions</h2>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student Number</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bottles</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reward</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php
                $transactions = $pdo->query("
                    SELECT t.*, f.name as reward_name 
                    FROM transactions t
                    LEFT JOIN food_rewards f ON t.reward_claimed = f.reward_id
                    ORDER BY t.timestamp DESC
                    LIMIT 10
                ")->fetchAll();
                
                foreach ($transactions as $transaction):
                ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($transaction['student_number']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $transaction['bottles_inserted'] ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= $transaction['reward_claimed'] ? htmlspecialchars($transaction['reward_name']) : 'None' ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= date('M j, Y g:i A', strtotime($transaction['timestamp'])) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="mt-4 text-right">
        <a href="transactions.php" class="text-sm text-green-600 hover:text-green-800">View All Transactions &rarr;</a>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>