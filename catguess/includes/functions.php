<?php
// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

// Redirect to a specific page
function redirect($page) {
    header("Location: $page");
    exit;
}

// Display error message
function displayError($message) {
    return "<div class='alert alert-danger'>$message</div>";
}

// Display success message
function displaySuccess($message) {
    return "<div class='alert alert-success'>$message</div>";
}

// Get user by ID
function getUserById($conn, $id) {
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Get user by username
function getUserByUsername($conn, $username) {
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Get user stats
function getUserStats($conn, $user_id, $difficulty = null) {
    if ($difficulty) {
        $sql = "SELECT * FROM game_stats WHERE user_id = ? AND difficulty = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "is", $user_id, $difficulty);
    } else {
        $sql = "SELECT * FROM game_stats WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $stats = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $stats[] = $row;
    }
    return $stats;
}

// Update user stats
function updateUserStats($conn, $user_id, $difficulty, $field, $value = 1) {
    // Check if stats record exists
    $sql = "SELECT id FROM game_stats WHERE user_id = ? AND difficulty = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "is", $user_id, $difficulty);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        // Update existing record
        $row = mysqli_fetch_assoc($result);
        $sql = "UPDATE game_stats SET $field = $field + ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $value, $row['id']);
    } else {
        // Create new record
        $sql = "INSERT INTO game_stats (user_id, difficulty, $field) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "isi", $user_id, $difficulty, $value);
    }
    return mysqli_stmt_execute($stmt);
}

// Add highscore - only if it's better than the previous best score
function addHighscore($conn, $user_id, $difficulty, $score) {
    // First check if user already has a highscore for this difficulty
    $check_sql = "SELECT MAX(score) as best_score FROM highscores WHERE user_id = ? AND difficulty = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "is", $user_id, $difficulty);
    mysqli_stmt_execute($check_stmt);
    $result = mysqli_stmt_get_result($check_stmt);
    $row = mysqli_fetch_assoc($result);
    
    // Only add the new score if it's better than the previous best or if there's no previous score
    if (!$row['best_score'] || $score > $row['best_score']) {
        $sql = "INSERT INTO highscores (user_id, difficulty, score) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "isi", $user_id, $difficulty, $score);
        return mysqli_stmt_execute($stmt);
    }
    
    return false; // No new highscore was added
}

// Get top highscores - only one entry per player (their highest score)
// If scores are tied, the most recent score is ranked higher
function getTopHighscores($conn, $difficulty, $limit = 10) {
    // First, get a list of unique user_ids with their highest scores
    $sql = "SELECT user_id, MAX(score) as max_score 
            FROM highscores 
            WHERE difficulty = ? 
            GROUP BY user_id 
            ORDER BY max_score DESC 
            LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $difficulty, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $highscores = [];
    
    // For each user, get their highest score record
    while ($row = mysqli_fetch_assoc($result)) {
        $user_id = $row['user_id'];
        $max_score = $row['max_score'];
        
        // Get the specific record with this score (including date and other details)
        $detail_sql = "SELECT h.*, u.username 
                      FROM highscores h 
                      JOIN users u ON h.user_id = u.id 
                      WHERE h.user_id = ? AND h.score = ? AND h.difficulty = ? 
                      ORDER BY h.date_achieved DESC 
                      LIMIT 1";
        
        $detail_stmt = mysqli_prepare($conn, $detail_sql);
        mysqli_stmt_bind_param($detail_stmt, "iis", $user_id, $max_score, $difficulty);
        mysqli_stmt_execute($detail_stmt);
        $detail_result = mysqli_stmt_get_result($detail_stmt);
        
        if ($detail_row = mysqli_fetch_assoc($detail_result)) {
            $highscores[] = $detail_row;
        }
    }
    
    // Sort the highscores array by score (descending) and date (descending) for ties
    usort($highscores, function($a, $b) {
        // If scores are different, sort by score (descending)
        if ($a['score'] != $b['score']) {
            return $b['score'] - $a['score'];
        }
        
        // If scores are tied, sort by date (most recent first)
        return strtotime($b['date_achieved']) - strtotime($a['date_achieved']);
    });
    
    // Limit the results if needed
    if (count($highscores) > $limit) {
        $highscores = array_slice($highscores, 0, $limit);
    }
    
    return $highscores;
}

// Generate a random number based on difficulty
function generateTargetNumber($difficulty) {
    switch ($difficulty) {
        case 'easy':
            return rand(1, 10);
        case 'medium':
            return rand(1, 30);
        case 'hard':
            return rand(1, 60);
        case 'impossible':
            return rand(1, 100);
        default:
            return rand(1, 10);
    }
}

// Get difficulty max number
function getDifficultyMax($difficulty) {
    switch ($difficulty) {
        case 'easy':
            return 10;
        case 'medium':
            return 30;
        case 'hard':
            return 60;
        case 'impossible':
            return 100;
        default:
            return 10;
    }
}

// Get random hint message
function getRandomHintMessage($guess, $target) {
    $tooLowMessages = [
        "That guess? Basement-level dumb.",
        "You dig numbers now?",
        "Even worms look down on that guess.",
        "That's not a guess, that's a stumble.",
        "You're not aiming low. You're aiming nowhere.",
        "You guessed dirt.",
        "Numbers go up, not down into shame.",
        "My grandma guesses higher—and she's playing chess.",
        "Even gravity's confused by that drop.",
        "That guess belongs in a sunken ship."
    ];

    $tooHighMessages = [
        "Relax, you're not launching a rocket.",
        "Guessing high doesn't mean you're smart.",
        "You overshot like your GPA claims.",
        "Trying to hit a number or Mars?",
        "You aimed for the stars and missed Earth.",
        "Even clouds think you're too much.",
        "That guess just filed for orbit.",
        "Hope you packed a parachute.",
        "NASA called—they want their number back.",
        "You guessed so high, the number's scared."
    ];

    if ($guess < $target) {
        return $tooLowMessages[array_rand($tooLowMessages)];
    } else {
        return $tooHighMessages[array_rand($tooHighMessages)];
    }
}

// Get all users (for admin)
function getAllUsers($conn) {
    $sql = "SELECT * FROM users ORDER BY id ASC";
    $result = mysqli_query($conn, $sql);
    
    $users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
    return $users;
}

// Get all game stats (for admin)
function getAllGameStats($conn) {
    $sql = "SELECT gs.*, u.username FROM game_stats gs 
            JOIN users u ON gs.user_id = u.id 
            ORDER BY gs.id ASC";
    $result = mysqli_query($conn, $sql);
    
    $stats = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $stats[] = $row;
    }
    return $stats;
}

// Get all highscores (for admin) - only the best score for each user/difficulty combination
function getAllHighscores($conn, $difficulty = 'all') {
    // This query gets only the highest score for each user/difficulty combination
    $sql = "SELECT h.*, u.username FROM highscores h 
            JOIN users u ON h.user_id = u.id 
            JOIN (
                SELECT user_id, difficulty, MAX(score) as max_score
                FROM highscores
                GROUP BY user_id, difficulty
            ) h2 ON h.user_id = h2.user_id AND h.difficulty = h2.difficulty AND h.score = h2.max_score";
    
    // Add difficulty filter if specified
    if ($difficulty != 'all') {
        $sql .= " WHERE h.difficulty = '" . mysqli_real_escape_string($conn, $difficulty) . "'";
    }
    
    $sql .= " GROUP BY h.user_id, h.difficulty
            ORDER BY h.score DESC";
    $result = mysqli_query($conn, $sql);
    
    $highscores = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $highscores[] = $row;
    }
    return $highscores;
}
?>
