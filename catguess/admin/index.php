<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if(!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

// Get stats for dashboard
$total_users = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM users"));
$total_games = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM game_stats"));
$total_highscores = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM highscores"));

// Get latest users
$latest_users = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC LIMIT 5");

// Get top players
$top_players = mysqli_query($conn, "SELECT u.username, COUNT(h.id) as score_count, MAX(h.score) as highest_score 
                                    FROM users u 
                                    JOIN highscores h ON u.id = h.user_id 
                                    GROUP BY u.id 
                                    ORDER BY score_count DESC 
                                    LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Cat Guess</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <div class="container admin-panel">
        <div class="admin-header">
            <h1>ADMIN DASHBOARD</h1>
            <div class="admin-nav">
                <a href="index.php" class="active">Dashboard</a>
                <a href="users.php">Users</a>
                <a href="stats.php">Game Stats</a>
                <a href="highscores.php">Highscores</a>
                <a href="change_password.php">Change Password</a>
                <a href="logout.php" style="background-color: #555;">Logout</a>
                <a href="../index.php">Back to Site</a>
            </div>
        </div>
        
        <div class="game-container">
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; margin-bottom: 30px; gap: 20px;">
                <div class="stats-card" style="flex: 1; min-width: 200px;">
                    <h3><i class="fas fa-users"></i> Total Users</h3>
                    <p><?php echo $total_users; ?></p>
                </div>
                <div class="stats-card" style="flex: 1; min-width: 200px;">
                    <h3><i class="fas fa-gamepad"></i> Total Games</h3>
                    <p><?php echo $total_games; ?></p>
                </div>
                <div class="stats-card" style="flex: 1; min-width: 200px;">
                    <h3><i class="fas fa-trophy"></i> Total Highscores</h3>
                    <p><?php echo $total_highscores; ?></p>
                </div>
            </div>
            
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 20px;">
                <div style="flex: 1; min-width: 300px;">
                    <div class="header-actions">
                        <h2>Latest Users</h2>
                    </div>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Joined</th>
                                <th>Admin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($user = mysqli_fetch_assoc($latest_users)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <?php if($user['is_admin']): ?>
                                            <span style="color: var(--primary-color);"><i class="fas fa-check-circle"></i> Yes</span>
                                        <?php else: ?>
                                            <span style="color: var(--muted-text);">No</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <div style="flex: 1; min-width: 300px;">
                    <div class="header-actions">
                        <h2>Top Players</h2>
                    </div>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Games</th>
                                <th>Highest Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($player = mysqli_fetch_assoc($top_players)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($player['username']); ?></td>
                                    <td><?php echo $player['score_count']; ?></td>
                                    <td><strong style="color: var(--primary-color);"><?php echo $player['highest_score']; ?></strong></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
