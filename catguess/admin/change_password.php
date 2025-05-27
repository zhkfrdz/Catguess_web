<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if(!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$success = '';
$error = '';

// Process form submission
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = trim($_POST["current_password"]);
    $new_password = trim($_POST["new_password"]);
    $confirm_password = trim($_POST["confirm_password"]);
    
    // Validate input
    if(empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "Please fill all fields.";
    } elseif($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } elseif(strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Get current user
        $user = getUserById($conn, $_SESSION['user_id']);
        
        if($user) {
            // Verify current password
            if(password_verify($current_password, $user["password"])) {
                // Hash the new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Update password
                $sql = "UPDATE users SET password = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "si", $hashed_password, $_SESSION['user_id']);
                
                if(mysqli_stmt_execute($stmt)) {
                    $success = "Password changed successfully!";
                } else {
                    $error = "Something went wrong. Please try again later.";
                }
                
                mysqli_stmt_close($stmt);
            } else {
                $error = "Current password is incorrect.";
            }
        } else {
            $error = "User not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Cat Guess Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .password-form {
            max-width: 500px;
            margin: 0 auto;
            background-color: #222;
            padding: 20px;
            border-radius: 5px;
        }
        .password-form .form-group {
            margin-bottom: 15px;
        }
        .password-form label {
            display: block;
            margin-bottom: 5px;
            color: #ccc;
        }
        .password-form input {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #333;
            background-color: #1a1a1a;
            color: #fff;
        }
        .password-form button {
            width: 100%;
            padding: 10px;
            background-color: #FF6B4A;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 10px;
        }
        .password-form button:hover {
            background-color: #e55a3c;
        }
    </style>
</head>
<body>
    <div class="container admin-panel">
        <div class="admin-header">
            <h1>CHANGE PASSWORD</h1>
            <div class="admin-nav">
                <a href="index.php">Dashboard</a>
                <a href="users.php">Users</a>
                <a href="stats.php">Game Stats</a>
                <a href="highscores.php">Highscores</a>
                <a href="change_password.php" class="active">Change Password</a>
                <a href="logout.php" style="background-color: #555;">Logout</a>
                <a href="../index.php">Back to Site</a>
            </div>
        </div>
        
        <?php if(!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if(!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="game-container">
            <h2 style="margin-bottom: 20px; color: #FF6B4A; text-align: center;">Change Admin Password</h2>
            
            <div class="password-form">
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
                        <button type="submit">Change Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
