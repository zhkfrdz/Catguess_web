<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if user is logged in
if(!isLoggedIn()) {
    redirect('login.php');
}

$success = '';
$error = '';

// Process form submission
if($_SERVER["REQUEST_METHOD"] == "POST") {
    // Change password
    if(isset($_POST['change_password'])) {
        $current_password = trim($_POST['current_password']);
        $new_password = trim($_POST['new_password']);
        $confirm_password = trim($_POST['confirm_password']);
        
        // Get user data
        $user = getUserById($conn, $_SESSION['user_id']);
        
        // Verify current password
        if(password_verify($current_password, $user['password'])) {
            // Check if new passwords match
            if($new_password == $confirm_password) {
                // Check password length
                if(strlen($new_password) >= 6) {
                    // Hash the new password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    
                    // Update password in database
                    $sql = "UPDATE users SET password = ? WHERE id = ?";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "si", $hashed_password, $_SESSION['user_id']);
                    
                    if(mysqli_stmt_execute($stmt)) {
                        $success = "Password updated successfully!";
                    } else {
                        $error = "Something went wrong. Please try again.";
                    }
                } else {
                    $error = "New password must be at least 6 characters long.";
                }
            } else {
                $error = "New passwords do not match.";
            }
        } else {
            $error = "Current password is incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Cat Guess</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="images/logo.png" alt="Cat Guess Logo">
        </div>
        <h1>SETTINGS</h1>
        <p class="subtitle">Manage your account</p>
        
        <?php if(!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if(!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="form-container">
            <h2 style="margin-bottom: 20px; color: #FF6B4A;">Change Password</h2>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <div class="form-group">
                    <button type="submit" name="change_password" class="btn primary-btn">Update Password</button>
                </div>
            </form>
        </div>
        
        <div style="margin-top: 20px;">
            <a href="index.php" class="btn outline-btn">Back to Menu</a>
        </div>
    </div>
</body>
</html>
