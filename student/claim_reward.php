<?php
require_once '../includes/student_header.php';
require_once '../includes/db.php';

$student_number = $_SESSION['student_number'];
$reward_id = $_GET['reward_id'] ?? null;

// Verify reward exists and get details
$reward = $pdo->prepare("SELECT * FROM food_rewards WHERE reward_id = ?")->execute([$reward_id])->fetch();

if (!$reward) {
    $_SESSION['error_message'] = 'Invalid reward selected';
    header('Location: rewards.php');
    exit();
}

// Get student's total bottles
$total_bottles = $pdo->prepare("
    SELECT COALESCE(SUM(bottles_inserted), 0) 
    FROM transactions 
    WHERE student_number = ?
")->execute([$student_number])->fetchColumn();

// Check if student has enough bottles
if ($total_bottles < $reward['price_in_bottles']) {
    $_SESSION['error_message'] = 'You do not have enough bottles to claim this reward';
    header('Location: rewards.php');
    exit();
}

// Handle reward claim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (verify_csrf_token($csrf_token)) {
        try {
            $pdo->beginTransaction();
            
            // Record the transaction
            $stmt = $pdo->prepare("
                INSERT INTO transactions 
                (student_number, bottles_inserted, reward_claimed) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([
                $student_number,
                -$reward['price_in_bottles'], // Negative value for redemption
                $reward_id
            ]);
            
            $pdo->commit();
            
            $_SESSION['success_message'] = 'Reward claimed successfully!';
            header('Location: dashboard.php');
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error_message'] = 'Error claiming reward: ' . $e->getMessage();
            header('Location: rewards.php');
            exit();
        }
    } else {
        $_SESSION['error_message'] = 'Invalid CSRF token';
        header('Location: rewards.php');
        exit();
    }
}
?>

<div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow">
    <h2 class="text-xl font-semibold mb-4">Confirm Reward Claim</h2>
    
    <div class="mb-6 p-4 border border-green-200 rounded-lg bg-green-50">
        <h3 class="font-medium text-lg"><?= htmlspecialchars($reward['name']) ?></h3>
        <p class="text-gray-600 text-sm mb-2"><?= htmlspecialchars($reward['description']) ?></p>
        <p class="text-sm">
            <span class="font-medium">Price:</span> 
            <span class="text-green-600"><?= $reward['price_in_bottles'] ?> bottles</span>
        </p>
    </div>
    
    <div class="mb-6">
        <p class="text-sm text-gray-600 mb-2">Your current bottle balance: <span class="font-medium"><?= $total_bottles ?></span></p>
        <p class="text-sm text-gray-600">Balance after claim: <span class="font-medium"><?= $total_bottles - $reward['price_in_bottles'] ?></span></p>
    </div>
    
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        
        <div class="flex justify-between">
            <a href="rewards.php" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                Cancel
            </a>
            <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                Confirm Claim
            </button>
        </div>
    </form>
</div>

<?php include '../includes/student_footer.php'; ?>