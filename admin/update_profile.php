<?php
require_once '../includes/admin_header.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!is_admin()) {
    header('Location: /login.php');
    exit();
}

$error = '';
$success = '';

// Get current admin info
$stmt = $pdo->prepare("SELECT username, profile_picture FROM admin WHERE admin_id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $target_dir = "../uploads/profile_pictures/";
    $file_extension = pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION);
    $target_file = $target_dir . "admin_" . $_SESSION['admin_id'] . "_" . time() . "." . $file_extension;
    
    // Validate image
    $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
    if ($check === false) {
        $error = "File is not an image.";
    } elseif ($_FILES["profile_picture"]["size"] > 500000) {
        $error = "File is too large (max 500KB).";
    } elseif (!in_array(strtolower($file_extension), ['jpg', 'jpeg', 'png', 'gif'])) {
        $error = "Only JPG, JPEG, PNG & GIF files are allowed.";
    } elseif (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
        try {
            // Delete old profile picture if exists
            if ($admin['profile_picture'] && file_exists($admin['profile_picture'])) {
                unlink($admin['profile_picture']);
            }
            
            $stmt = $pdo->prepare("UPDATE admin SET profile_picture = ? WHERE admin_id = ?");
            $stmt->execute([$target_file, $_SESSION['admin_id']]);
            $success = "Profile picture updated successfully!";
            header("Refresh:0"); // Refresh to show new image
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    } else {
        $error = "Sorry, there was an error uploading your file.";
    }
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden md:max-w-2xl">
        <div class="p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Update Profile</h2>
            </div>

            <?php if ($error): ?>
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <div class="flex flex-col items-center mb-6">
                <div class="relative mb-4">
                    <?php if ($admin['profile_picture']): ?>
                        <img src="<?= htmlspecialchars($admin['profile_picture']) ?>" alt="Profile Picture" class="w-32 h-32 rounded-full object-cover border-4 border-green-500">
                    <?php else: ?>
                        <div class="w-32 h-32 rounded-full bg-gray-200 flex items-center justify-center border-4 border-green-500">
                            <i class="fas fa-user text-5xl text-gray-400"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <form method="post" enctype="multipart/form-data" class="w-full">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="profile_picture">
                            Upload New Profile Picture
                        </label>
                        <input type="file" name="profile_picture" id="profile_picture" accept="image/*" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Update Profile Picture
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>