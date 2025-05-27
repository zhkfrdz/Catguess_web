<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if user is logged in
if(!isLoggedIn()) {
    redirect('login.php');
}

// Get difficulty from query string, default to easy
$difficulty = isset($_GET['difficulty']) ? $_GET['difficulty'] : 'easy';

// Get top highscores for the selected difficulty
$highscores = getTopHighscores($conn, $difficulty);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Highscores - Cat Guess</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="images/logo.png" alt="Cat Guess Logo">
        </div>
        <h1>HIGHSCORES</h1>
        <p class="subtitle">Top players for <?php echo ucfirst($difficulty); ?> difficulty</p>
        
        <div class="difficulty-selector" style="margin-bottom: 20px;">
            <a href="highscores.php?difficulty=easy" class="btn <?php echo $difficulty == 'easy' ? 'primary-btn' : 'outline-btn'; ?>" style="display: inline-block; width: auto; margin-right: 10px;">Easy</a>
            <a href="highscores.php?difficulty=medium" class="btn <?php echo $difficulty == 'medium' ? 'primary-btn' : 'outline-btn'; ?>" style="display: inline-block; width: auto; margin-right: 10px;">Medium</a>
            <a href="highscores.php?difficulty=hard" class="btn <?php echo $difficulty == 'hard' ? 'primary-btn' : 'outline-btn'; ?>" style="display: inline-block; width: auto; margin-right: 10px;">Hard</a>
            <a href="highscores.php?difficulty=impossible" class="btn <?php echo $difficulty == 'impossible' ? 'primary-btn' : 'outline-btn'; ?>" style="display: inline-block; width: auto;">Impossible</a>
        </div>
        
        <div class="game-container">
            <?php if(count($highscores) > 0): ?>
                <table class="admin-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Player</th>
                            <th>Score</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($highscores as $index => $score): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($score['username']); ?></td>
                                <td><?php echo $score['score']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($score['date_achieved'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No highscores yet for this difficulty. Be the first to play!</p>
            <?php endif; ?>
        </div>
        
        <div style="margin-top: 20px;">
            <a href="index.php" class="btn outline-btn">Back to Menu</a>
        </div>
    </div>
</body>
</html>
