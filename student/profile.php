<?php
$title = 'Profile Settings';
require_once '../includes/student_header.php';
require_once '../includes/db.php';

$student_number = $_SESSION['student_number'];
$error = '';
$success = '';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($csrf_token)) {
        $error = 'Invalid CSRF token';
    } elseif (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'All password fields are required';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match';
    } else {
        // Verify current password
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE student_number = ?");
        $stmt->execute([$student_number]);
        $user = $stmt->fetch();

        if ($user && password_verify($current_password, $user['password_hash'])) {
            // Update password
            $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
            $update_stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE student_number = ?");
            $update_stmt->execute([$new_hash, $student_number]);
            
            $success = 'Password changed successfully';
        } else {
            $error = 'Current password is incorrect';
        }
    }
}

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_photo'])) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (verify_csrf_token($csrf_token) && isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['profile_picture']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $upload_dir = '../uploads/profile_pictures/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $student_number . '_' . time() . '.' . $file_ext;
            $destination = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $destination)) {
                $image_url = '/uploads/profile_pictures/' . $filename;
                
                $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE student_number = ?");
                if ($stmt->execute([$image_url, $student_number])) {
                    $success = 'Profile picture updated successfully';
                    // Refresh student data
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE student_number = ?");
                    $stmt->execute([$student_number]);
                    $student = $stmt->fetch();
                } else {
                    $error = 'Failed to update profile in database';
                }
            } else {
                $error = 'Failed to upload image';
            }
        } else {
            $error = 'Only JPG, PNG, and GIF images are allowed';
        }
    } else {
        $error = 'Please select a valid image file';
    }
}
?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-4">Change Password</h2>
        
        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded text-sm">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded text-sm">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            
            <div class="space-y-4">
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                </div>
                
                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <input type="password" id="new_password" name="new_password" required minlength="8"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                </div>
                
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                </div>
                
                <div class="pt-2">
                    <button type="submit" name="change_password"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Change Password
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-4">Profile Picture</h2>
        
        <div class="flex flex-col items-center">
            <?php if (isset($student['profile_picture'])): ?>
                <img src="<?= htmlspecialchars($student['profile_picture']) ?>" alt="Profile" class="w-32 h-32 rounded-full mb-4">
            <?php else: ?>
                <div class="w-32 h-32 rounded-full bg-gray-200 flex items-center justify-center mb-4">
                    <i class="fas fa-user text-gray-400 text-4xl"></i>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" class="w-full">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Upload New Photo</label>
                        <div class="mt-1 flex items-center">
                            <input type="file" name="profile_picture" accept="image/*"
                                class="w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-md file:border-0
                                file:text-sm file:font-semibold
                                file:bg-green-50 file:text-green-700
                                hover:file:bg-green-100">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">JPG, PNG or GIF (Max 2MB)</p>
                    </div>
                    
                    <div class="pt-2">
                        <button type="submit" name="upload_photo"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Update Photo
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/student_footer.php'; ?>