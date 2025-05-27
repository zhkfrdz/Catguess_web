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
    // Delete highscore
    if(isset($_POST['delete_highscore'])) {
        $highscore_id = intval($_POST['highscore_id']);
        
        // Delete highscore
        $sql = "DELETE FROM highscores WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $highscore_id);
        
        if(mysqli_stmt_execute($stmt)) {
            $success = "Highscore deleted successfully!";
        } else {
            $error = "Something went wrong. Please try again later.";
        }
        
        mysqli_stmt_close($stmt);
    }
    
    // Reset all highscores
    if(isset($_POST['reset_all'])) {
        // Delete all highscores
        $sql = "TRUNCATE TABLE highscores";
        
        if(mysqli_query($conn, $sql)) {
            $success = "All highscores reset successfully!";
        } else {
            $error = "Something went wrong. Please try again later.";
        }
    }
}

// Get selected difficulty filter (default to showing all)
$difficulty_filter = isset($_GET['difficulty']) ? $_GET['difficulty'] : 'all';

// Get all highscores with optional difficulty filter
$highscores = getAllHighscores($conn, $difficulty_filter);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Highscores - Cat Guess</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <div class="container admin-panel">
        <div class="admin-header">
            <h1>MANAGE HIGHSCORES</h1>
            <div class="admin-nav">
                <a href="index.php">Dashboard</a>
                <a href="users.php">Users</a>
                <a href="stats.php">Game Stats</a>
                <a href="highscores.php" class="active">Highscores</a>
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
            <div class="header-actions">
                <h2><?php echo $difficulty_filter == 'all' ? 'All' : ucfirst($difficulty_filter); ?> Highscores</h2>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" onsubmit="return confirm('Are you sure you want to reset ALL highscores? This cannot be undone!');">
                    <button type="submit" name="reset_all" class="btn btn-danger">
                        <i class="fas fa-trash-alt"></i> Reset All Highscores
                    </button>
                </form>
            </div>
            
            <div class="filter-buttons">
                <a href="?difficulty=all" class="<?php echo $difficulty_filter == 'all' ? 'active' : ''; ?>">
                    All
                </a>
                <a href="?difficulty=easy" class="<?php echo $difficulty_filter == 'easy' ? 'active' : ''; ?>">
                    <i class="fas fa-star"></i> Easy
                </a>
                <a href="?difficulty=medium" class="<?php echo $difficulty_filter == 'medium' ? 'active' : ''; ?>">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i> Medium
                </a>
                <a href="?difficulty=hard" class="<?php echo $difficulty_filter == 'hard' ? 'active' : ''; ?>">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i> Hard
                </a>
                <a href="?difficulty=impossible" class="<?php echo $difficulty_filter == 'impossible' ? 'active' : ''; ?>">
                    <i class="fas fa-skull"></i> Impossible
                </a>
            </div>
            
            <?php if(count($highscores) > 0): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Difficulty</th>
                            <th>Score</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($highscores as $highscore): ?>
                            <tr>
                                <td><?php echo $highscore['id']; ?></td>
                                <td><?php echo htmlspecialchars($highscore['username']); ?></td>
                                <td><?php echo ucfirst($highscore['difficulty']); ?></td>
                                <td><?php echo $highscore['score']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($highscore['date_achieved'])); ?></td>
                                <td>
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" onsubmit="return confirm('Are you sure you want to delete this highscore?');">
                                        <input type="hidden" name="highscore_id" value="<?php echo $highscore['id']; ?>">
                                        <button type="submit" name="delete_highscore" class="btn btn-danger">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No highscores available.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
