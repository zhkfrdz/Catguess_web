<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if user is logged in
if(!isLoggedIn()) {
    redirect('login.php');
}

// Get user stats for all difficulties
$easy_stats = getUserStats($conn, $_SESSION['user_id'], 'easy');
$medium_stats = getUserStats($conn, $_SESSION['user_id'], 'medium');
$hard_stats = getUserStats($conn, $_SESSION['user_id'], 'hard');
$impossible_stats = getUserStats($conn, $_SESSION['user_id'], 'impossible');

// Initialize stats arrays if empty
$easy = count($easy_stats) > 0 ? $easy_stats[0] : [
    'games_played' => 0,
    'games_won' => 0,
    'games_lost' => 0,
    'hints_used' => 0
];

$medium = count($medium_stats) > 0 ? $medium_stats[0] : [
    'games_played' => 0,
    'games_won' => 0,
    'games_lost' => 0,
    'hints_used' => 0
];

$hard = count($hard_stats) > 0 ? $hard_stats[0] : [
    'games_played' => 0,
    'games_won' => 0,
    'games_lost' => 0,
    'hints_used' => 0
];

$impossible = count($impossible_stats) > 0 ? $impossible_stats[0] : [
    'games_played' => 0,
    'games_won' => 0,
    'games_lost' => 0,
    'hints_used' => 0
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stats - Cat Guess</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="images/logo.png" alt="Cat Guess Logo">
        </div>
        <h1>YOUR STATS</h1>
        <p class="subtitle">Game statistics for <?php echo htmlspecialchars($_SESSION['username']); ?></p>
        
        <div class="game-container">
            <h2 style="margin-bottom: 20px; color: #FF6B4A;">Easy Level</h2>
            <table class="admin-table" style="width: 100%; margin-bottom: 30px;">
                <tr>
                    <td>Games Played</td>
                    <td><?php echo $easy['games_played']; ?></td>
                </tr>
                <tr>
                    <td>Games Won</td>
                    <td><?php echo $easy['games_won']; ?></td>
                </tr>
                <tr>
                    <td>Games Lost</td>
                    <td><?php echo $easy['games_lost']; ?></td>
                </tr>
                <tr>
                    <td>Win Rate</td>
                    <td><?php echo $easy['games_played'] > 0 ? round(($easy['games_won'] / $easy['games_played']) * 100, 1) . '%' : '0%'; ?></td>
                </tr>
                <tr>
                    <td>Hints Used</td>
                    <td><?php echo $easy['hints_used']; ?></td>
                </tr>
            </table>
            
            <h2 style="margin-bottom: 20px; color: #FF6B4A;">Medium Level</h2>
            <table class="admin-table" style="width: 100%; margin-bottom: 30px;">
                <tr>
                    <td>Games Played</td>
                    <td><?php echo $medium['games_played']; ?></td>
                </tr>
                <tr>
                    <td>Games Won</td>
                    <td><?php echo $medium['games_won']; ?></td>
                </tr>
                <tr>
                    <td>Games Lost</td>
                    <td><?php echo $medium['games_lost']; ?></td>
                </tr>
                <tr>
                    <td>Win Rate</td>
                    <td><?php echo $medium['games_played'] > 0 ? round(($medium['games_won'] / $medium['games_played']) * 100, 1) . '%' : '0%'; ?></td>
                </tr>
                <tr>
                    <td>Hints Used</td>
                    <td><?php echo $medium['hints_used']; ?></td>
                </tr>
            </table>
            
            <h2 style="margin-bottom: 20px; color: #FF6B4A;">Hard Level</h2>
            <table class="admin-table" style="width: 100%; margin-bottom: 30px;">
                <tr>
                    <td>Games Played</td>
                    <td><?php echo $hard['games_played']; ?></td>
                </tr>
                <tr>
                    <td>Games Won</td>
                    <td><?php echo $hard['games_won']; ?></td>
                </tr>
                <tr>
                    <td>Games Lost</td>
                    <td><?php echo $hard['games_lost']; ?></td>
                </tr>
                <tr>
                    <td>Win Rate</td>
                    <td><?php echo $hard['games_played'] > 0 ? round(($hard['games_won'] / $hard['games_played']) * 100, 1) . '%' : '0%'; ?></td>
                </tr>
                <tr>
                    <td>Hints Used</td>
                    <td><?php echo $hard['hints_used']; ?></td>
                </tr>
            </table>
            
            <h2 style="margin-bottom: 20px; color: #FF6B4A;">Impossible Level</h2>
            <table class="admin-table" style="width: 100%;">
                <tr>
                    <td>Games Played</td>
                    <td><?php echo $impossible['games_played']; ?></td>
                </tr>
                <tr>
                    <td>Games Won</td>
                    <td><?php echo $impossible['games_won']; ?></td>
                </tr>
                <tr>
                    <td>Games Lost</td>
                    <td><?php echo $impossible['games_lost']; ?></td>
                </tr>
                <tr>
                    <td>Win Rate</td>
                    <td><?php echo $impossible['games_played'] > 0 ? round(($impossible['games_won'] / $impossible['games_played']) * 100, 1) . '%' : '0%'; ?></td>
                </tr>
                <tr>
                    <td>Hints Used</td>
                    <td><?php echo $impossible['hints_used']; ?></td>
                </tr>
            </table>
        </div>
        
        <div style="margin-top: 20px;">
            <a href="index.php" class="btn outline-btn">Back to Menu</a>
        </div>
    </div>
</body>
</html>
