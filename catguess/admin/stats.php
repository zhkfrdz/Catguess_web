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
    // Reset stats
    if(isset($_POST['reset_stats'])) {
        $user_id = intval($_POST['user_id']);
        
        // Check if user exists
        $user = getUserById($conn, $user_id);
        
        if($user) {
            // Delete user's game stats
            $sql = "DELETE FROM game_stats WHERE user_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            
            if(mysqli_stmt_execute($stmt)) {
                $success = "User stats reset successfully!";
            } else {
                $error = "Something went wrong. Please try again later.";
            }
            
            mysqli_stmt_close($stmt);
        } else {
            $error = "User not found.";
        }
    }
}

// Get all game stats
$stats = getAllGameStats($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Stats - Cat Guess</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <div class="container admin-panel">
        <div class="admin-header">
            <h1>GAME STATISTICS</h1>
            <div class="admin-nav">
                <a href="index.php">Dashboard</a>
                <a href="users.php">Users</a>
                <a href="stats.php" class="active">Game Stats</a>
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
            <h2 style="margin-bottom: 20px; color: #FF6B4A;">All Game Statistics</h2>
            
            <?php if(count($stats) > 0): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Difficulty</th>
                            <th>Games Played</th>
                            <th>Games Won</th>
                            <th>Games Lost</th>
                            <th>Win Rate</th>
                            <th>Hints Used</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($stats as $stat): ?>
                            <tr>
                                <td><?php echo $stat['id']; ?></td>
                                <td><?php echo htmlspecialchars($stat['username']); ?></td>
                                <td><?php echo ucfirst($stat['difficulty']); ?></td>
                                <td><?php echo $stat['games_played']; ?></td>
                                <td><?php echo $stat['games_won']; ?></td>
                                <td><?php echo $stat['games_lost']; ?></td>
                                <td><?php echo $stat['games_played'] > 0 ? round(($stat['games_won'] / $stat['games_played']) * 100, 1) . '%' : '0%'; ?></td>
                                <td><?php echo $stat['hints_used']; ?></td>
                                <td>
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" onsubmit="return confirm('Are you sure you want to reset stats for this user?');">
                                        <input type="hidden" name="user_id" value="<?php echo $stat['user_id']; ?>">
                                        <button type="submit" name="reset_stats" class="btn" style="background-color: #dc3545; color: white; padding: 5px 10px; font-size: 14px;">
                                            Reset
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No game statistics available.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
