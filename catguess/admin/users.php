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
    // Add new user
    if(isset($_POST['add_user'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $is_admin = isset($_POST['is_admin']) ? 1 : 0;
        
        // Validate input
        if(empty($username) || empty($password)) {
            $error = "Please fill all fields.";
        } elseif(strlen($username) < 3 || strlen($username) > 20) {
            $error = "Username must be between 3 and 20 characters.";
        } elseif(strlen($password) < 6) {
            $error = "Password must be at least 6 characters.";
        } else {
            // Check if username already exists
            $user = getUserByUsername($conn, $username);
            
            if($user) {
                $error = "Username already exists.";
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Prepare an insert statement
                $sql = "INSERT INTO users (username, password, is_admin) VALUES (?, ?, ?)";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ssi", $username, $hashed_password, $is_admin);
                
                if(mysqli_stmt_execute($stmt)) {
                    $success = "User added successfully!";
                } else {
                    $error = "Something went wrong. Please try again later.";
                }
                
                mysqli_stmt_close($stmt);
            }
        }
    }
    
    // Delete user
    if(isset($_POST['delete_user'])) {
        $user_id = intval($_POST['user_id']);
        
        // Check if user exists
        $user = getUserById($conn, $user_id);
        
        if($user) {
            // Don't allow deleting the current admin
            if($user_id == $_SESSION['user_id']) {
                $error = "You cannot delete your own account.";
            } else {
                // Delete user's game stats and highscores first
                mysqli_query($conn, "DELETE FROM game_stats WHERE user_id = $user_id");
                mysqli_query($conn, "DELETE FROM highscores WHERE user_id = $user_id");
                
                // Delete user
                $sql = "DELETE FROM users WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $user_id);
                
                if(mysqli_stmt_execute($stmt)) {
                    $success = "User deleted successfully!";
                } else {
                    $error = "Something went wrong. Please try again later.";
                }
                
                mysqli_stmt_close($stmt);
            }
        } else {
            $error = "User not found.";
        }
    }
    
    // Toggle admin status
    if(isset($_POST['toggle_admin'])) {
        $user_id = intval($_POST['user_id']);
        
        // Check if user exists
        $user = getUserById($conn, $user_id);
        
        if($user) {
            // Don't allow removing admin status from current admin
            if($user_id == $_SESSION['user_id'] && $user['is_admin'] == 1) {
                $error = "You cannot remove your own admin status.";
            } else {
                // Toggle admin status
                $new_status = $user['is_admin'] == 1 ? 0 : 1;
                
                $sql = "UPDATE users SET is_admin = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ii", $new_status, $user_id);
                
                if(mysqli_stmt_execute($stmt)) {
                    $success = "User admin status updated successfully!";
                } else {
                    $error = "Something went wrong. Please try again later.";
                }
                
                mysqli_stmt_close($stmt);
            }
        } else {
            $error = "User not found.";
        }
    }
}

// Get all users
$users = getAllUsers($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Cat Guess</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <div class="container admin-panel">
        <div class="admin-header">
            <h1>MANAGE USERS</h1>
            <div class="admin-nav">
                <a href="index.php">Dashboard</a>
                <a href="users.php" class="active">Users</a>
                <a href="stats.php">Game Stats</a>
                <a href="highscores.php">Highscores</a>
                <a href="change_password.php">Change Password</a>
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
            <h2 style="margin-bottom: 20px; color: #FF6B4A;">Add New User</h2>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="margin-bottom: 30px;">
                <div style="display: flex; gap: 10px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Username</label>
                        <input type="text" name="username" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>
                    <div class="form-group" style="flex: 0.5; display: flex; align-items: flex-end;">
                        <label style="display: flex; align-items: center;">
                            <input type="checkbox" name="is_admin" style="margin-right: 5px;"> Admin
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <button type="submit" name="add_user" class="btn primary-btn">Add User</button>
                </div>
            </form>
            
            <h2 style="margin-bottom: 20px; color: #FF6B4A;">All Users</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Created</th>
                        <th>Admin</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td><?php echo $user['is_admin'] ? 'Yes' : 'No'; ?></td>
                            <td>
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" name="toggle_admin" class="btn" style="background-color: #007bff; color: white; padding: 5px 10px; margin-right: 5px; font-size: 14px;">
                                        <?php echo $user['is_admin'] ? 'Remove Admin' : 'Make Admin'; ?>
                                    </button>
                                </form>
                                
                                <?php if($user['id'] != $_SESSION['user_id']): ?>
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="delete_user" class="btn" style="background-color: #dc3545; color: white; padding: 5px 10px; font-size: 14px;">
                                            Delete
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
