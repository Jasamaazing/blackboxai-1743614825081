<?php
$title = 'Rewards Management';
require_once '../includes/admin_header.php';
require_once '../includes/db.php';

// Handle form submission to update rewards
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (verify_csrf_token($csrf_token)) {
        if (isset($_POST['update_rewards'])) {
            // Update existing rewards
            foreach ($_POST['rewards'] as $reward_id => $reward_data) {
                $stmt = $pdo->prepare("UPDATE food_rewards SET 
                    name = ?, 
                    description = ?, 
                    stock_A = ?, 
                    stock_B = ?, 
                    price_in_bottles = ? 
                    WHERE reward_id = ?");
                
                $stmt->execute([
                    $reward_data['name'],
                    $reward_data['description'],
                    (int)$reward_data['stock_A'],
                    (int)$reward_data['stock_B'],
                    (int)$reward_data['price_in_bottles'],
                    $reward_id
                ]);
            }
            $_SESSION['success_message'] = 'Rewards updated successfully';
        } elseif (isset($_POST['add_reward'])) {
            // Add new reward
            $stmt = $pdo->prepare("INSERT INTO food_rewards 
                (name, description, stock_A, stock_B, price_in_bottles) 
                VALUES (?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $_POST['new_name'],
                $_POST['new_description'],
                (int)$_POST['new_stock_A'],
                (int)$_POST['new_stock_B'],
                (int)$_POST['new_price']
            ]);
            $_SESSION['success_message'] = 'New reward added successfully';
        }
        
        header('Location: rewards.php');
        exit();
    } else {
        $error = 'Invalid CSRF token';
    }
}

// Get all rewards
$rewards = $pdo->query("SELECT * FROM food_rewards ORDER BY reward_id")->fetchAll();
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
    <h2 class="text-xl font-semibold mb-6">Manage Rewards</h2>
    
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reward Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock A</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock B</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price (Bottles)</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($rewards as $reward): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="text" name="rewards[<?= $reward['reward_id'] ?>][name]" 
                                value="<?= htmlspecialchars($reward['name']) ?>"
                                class="w-full px-2 py-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500" required>
                        </td>
                        <td class="px-6 py-4">
                            <textarea name="rewards[<?= $reward['reward_id'] ?>][description]"
                                class="w-full px-2 py-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500" rows="2"><?= htmlspecialchars($reward['description']) ?></textarea>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="number" name="rewards[<?= $reward['reward_id'] ?>][stock_A]" 
                                value="<?= $reward['stock_A'] ?>"
                                class="w-20 px-2 py-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500" min="0" required>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="number" name="rewards[<?= $reward['reward_id'] ?>][stock_B]" 
                                value="<?= $reward['stock_B'] ?>"
                                class="w-20 px-2 py-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500" min="0" required>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="number" name="rewards[<?= $reward['reward_id'] ?>][price_in_bottles]" 
                                value="<?= $reward['price_in_bottles'] ?>"
                                class="w-20 px-2 py-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500" min="1" required>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="flex justify-end mt-6">
            <button type="submit" name="update_rewards"
                class="px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                Update Rewards
            </button>
        </div>
    </form>
</div>

<div class="bg-white p-6 rounded-lg shadow mt-6">
    <h2 class="text-xl font-semibold mb-6">Add New Reward</h2>
    
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="new_name" class="block text-sm font-medium text-gray-700 mb-1">Reward Name</label>
                <input type="text" id="new_name" name="new_name" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500" required>
            </div>
            
            <div>
                <label for="new_description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <input type="text" id="new_description" name="new_description" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
            </div>
            
            <div>
                <label for="new_stock_A" class="block text-sm font-medium text-gray-700 mb-1">Stock A</label>
                <input type="number" id="new_stock_A" name="new_stock_A" min="0" value="0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500" required>
            </div>
            
            <div>
                <label for="new_stock_B" class="block text-sm font-medium text-gray-700 mb-1">Stock B</label>
                <input type="number" id="new_stock_B" name="new_stock_B" min="0" value="0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500" required>
            </div>
            
            <div>
                <label for="new_price" class="block text-sm font-medium text-gray-700 mb-1">Price (Bottles)</label>
                <input type="number" id="new_price" name="new_price" min="1" value="10"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500" required>
            </div>
        </div>
        
        <div class="flex justify-end mt-6">
            <button type="submit" name="add_reward"
                class="px-4 py-2 bg-blue-600 text-white rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Add Reward
            </button>
        </div>
    </form>
</div>

<?php include '../includes/admin_footer.php'; ?>