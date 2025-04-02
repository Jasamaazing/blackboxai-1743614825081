<?php
$title = 'Transaction History';
require_once '../includes/admin_header.php';
require_once '../includes/db.php';

// Handle filters
$where = [];
$params = [];

if (!empty($_GET['student_number'])) {
    $where[] = "t.student_number LIKE ?";
    $params[] = '%' . $_GET['student_number'] . '%';
}

if (!empty($_GET['date_from'])) {
    $where[] = "t.timestamp >= ?";
    $params[] = $_GET['date_from'] . ' 00:00:00';
}

if (!empty($_GET['date_to'])) {
    $where[] = "t.timestamp <= ?";
    $params[] = $_GET['date_to'] . ' 23:59:59';
}

$where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total count for pagination
$total_count = $pdo->prepare("
    SELECT COUNT(*) 
    FROM transactions t
    LEFT JOIN food_rewards f ON t.reward_claimed = f.reward_id
    $where_clause
")->execute($params)->fetchColumn();

// Pagination
$per_page = 20;
$total_pages = ceil($total_count / $per_page);
$current_page = isset($_GET['page']) ? max(1, min($total_pages, (int)$_GET['page'])) : 1;
$offset = ($current_page - 1) * $per_page;

// Get transactions
$transactions = $pdo->prepare("
    SELECT t.*, f.name as reward_name 
    FROM transactions t
    LEFT JOIN food_rewards f ON t.reward_claimed = f.reward_id
    $where_clause
    ORDER BY t.timestamp DESC
    LIMIT $per_page OFFSET $offset
")->execute($params)->fetchAll();
?>

<div class="bg-white p-6 rounded-lg shadow">
    <h2 class="text-xl font-semibold mb-6">Transaction History</h2>
    
    <!-- Filter Form -->
    <form method="GET" class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="student_number" class="block text-sm font-medium text-gray-700 mb-1">Student Number</label>
                <input type="text" id="student_number" name="student_number" value="<?= htmlspecialchars($_GET['student_number'] ?? '') ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
            </div>
            
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                <input type="date" id="date_from" name="date_from" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
            </div>
            
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                <input type="date" id="date_to" name="date_to" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    Filter
                </button>
                <a href="transactions.php" class="ml-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-md shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    Reset
                </a>
            </div>
        </div>
    </form>
    
    <!-- Transactions Table -->
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
                <?php if (empty($transactions)): ?>
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No transactions found</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($transactions as $transaction): ?>
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
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="mt-6 flex items-center justify-between">
        <div class="text-sm text-gray-700">
            Showing <span class="font-medium"><?= $offset + 1 ?></span> to <span class="font-medium"><?= min($offset + $per_page, $total_count) ?></span> of <span class="font-medium"><?= $total_count ?></span> results
        </div>
        <div class="flex space-x-2">
            <?php if ($current_page > 1): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>" class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Previous</a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium <?= $i == $current_page ? 'bg-green-100 border-green-500 text-green-600' : 'text-gray-700 hover:bg-gray-50' ?>"><?= $i ?></a>
            <?php endfor; ?>
            
            <?php if ($current_page < $total_pages): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page + 1])) ?>" class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Next</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/admin_footer.php'; ?>