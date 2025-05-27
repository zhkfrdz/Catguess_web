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
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['clean_highscores'])) {
    // This query keeps only the highest score for each user/difficulty combination
    $sql = "
    DELETE h1 FROM highscores h1
    LEFT JOIN (
        SELECT user_id, difficulty, MAX(score) as max_score
        FROM highscores
        GROUP BY user_id, difficulty
    ) h2 ON h1.user_id = h2.user_id AND h1.difficulty = h2.difficulty AND h1.score = h2.max_score
    WHERE h2.max_score IS NULL OR h1.id NOT IN (
        SELECT MIN(id) 
        FROM highscores 
        WHERE user_id = h1.user_id AND difficulty = h1.difficulty AND score = h2.max_score
        GROUP BY user_id, difficulty
    )";
    
    if(mysqli_query($conn, $sql)) {
        $success = "Duplicate highscores cleaned successfully! Only the best score for each user/difficulty combination is kept.";
    } else {
        $error = "Something went wrong. Please try again later. Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clean Highscores - Cat Guess</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <div class="container admin-panel">
        <div class="admin-header">
            <h1>CLEAN DUPLICATE HIGHSCORES</h1>
            <div class="admin-nav">
                <a href="index.php">Dashboard</a>
                <a href="users.php">Users</a>
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
            <h2 style="margin-bottom: 20px; color: #FF6B4A;">Clean Duplicate Highscores</h2>
            
            <div style="background-color: #222; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                <p>This tool will remove duplicate highscores and keep only the best score for each user/difficulty combination.</p>
                <p>This is useful if you have multiple entries for the same user in the highscores table.</p>
                <p><strong>Note:</strong> This action cannot be undone. Make sure you want to proceed.</p>
            </div>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" onsubmit="return confirm('Are you sure you want to clean duplicate highscores? This cannot be undone!');">
                <button type="submit" name="clean_highscores" class="btn" style="background-color: #FF6B4A; color: white; padding: 10px 20px; font-size: 16px; width: 100%;">
                    Clean Duplicate Highscores
                </button>
            </form>
            
            <div style="margin-top: 20px; text-align: center;">
                <a href="highscores.php" class="btn" style="background-color: #333; color: white; padding: 10px 20px; display: inline-block; text-decoration: none;">
                    Back to Highscores
                </a>
            </div>
        </div>
    </div>
</body>
</html>
