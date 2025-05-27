<?php
require_once 'includes/session_config.php';
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if user is logged in
if(!isLoggedIn()) {
    redirect('login.php');
}

// Initialize variables
$difficulty = isset($_GET['difficulty']) ? $_GET['difficulty'] : 'easy';
$difficultyMax = getDifficultyMax($difficulty);
$message = '';
$hint_message = '';
$game_over = false;
$won = false;

// Check if difficulty has changed
$difficulty_changed = isset($_SESSION['current_difficulty']) && $_SESSION['current_difficulty'] != $difficulty;

// Update current difficulty
$_SESSION['current_difficulty'] = $difficulty;

// Initialize session variables if not set or if difficulty changed
if(!isset($_SESSION['target_number']) || $difficulty_changed) {
    $_SESSION['target_number'] = generateTargetNumber($difficulty);
    $_SESSION['hearts'] = 3;
    
    // Set initial hints based on difficulty
    if($difficulty == 'easy') {
        $_SESSION['hints'] = 3;
    } elseif($difficulty == 'medium') {
        $_SESSION['hints'] = 5;
    } elseif($difficulty == 'hard') {
        $_SESSION['hints'] = 10;
    } else { // impossible
        $_SESSION['hints'] = 15;
    }
    
    $_SESSION['has_guessed'] = false;
    $_SESSION['correct_guesses'] = 0; // Track correct guesses for hint rewards
}

// Process form submission
if($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if it's a guess with empty input
    if(isset($_POST['guess']) && empty($_POST['guess_number'])) {
        $hint_message = "Input a Number Dummy";
    }
    // Check if it's a new game request
    elseif(isset($_POST['new_game'])) {
        // Reset game
        $_SESSION['target_number'] = generateTargetNumber($difficulty);
        $_SESSION['hearts'] = 3;
        $_SESSION['has_guessed'] = false;
        // Don't reset hints or correct_guesses counter to maintain progress
        $message = '';
    }
    // Check if it's a hint request
    elseif(isset($_POST['hint'])) {
        if($_SESSION['hints'] > 0 && $_SESSION['has_guessed']) {
            $_SESSION['hints']--;
            
            // Update hints used in database
            updateUserStats($conn, $_SESSION['user_id'], $difficulty, 'hints_used');
            
            // Generate a random hint
            $rand = rand(1, 3);
            if($rand == 1) {
                // Range hint
                $range = $difficultyMax / 4;
                $lowerBound = (floor($_SESSION['target_number'] / $range) * $range) + 1;
                $upperBound = $lowerBound + $range - 1;
                $hint_message = "The number is between $lowerBound and $upperBound";
            } elseif($rand == 2) {
                // Even/Odd hint
                $hint_message = ($_SESSION['target_number'] % 2 == 0) ? "The number is even" : "The number is odd";
            } else {
                // Divisibility hint
                $divisor = rand(2, 5);
                $hint_message = ($_SESSION['target_number'] % $divisor == 0) ? 
                    "The number is divisible by $divisor" : 
                    "The number is not divisible by $divisor";
            }
        } elseif(!$_SESSION['has_guessed']) {
            $hint_message = "Guess First";
        } else {
            $hint_message = "No hints remaining!";
        }
    }
    // Check if it's a give up requestss
    elseif(isset($_POST['give_up'])) {
        $message = "You gave up! The number was " . $_SESSION['target_number'];
        $game_over = true;
        
        // Only update stats if they've actually started playing
        if($_SESSION['has_guessed']) {
            // Update games lost in database
            updateUserStats($conn, $_SESSION['user_id'], $difficulty, 'games_lost');
        }
        
        // Reset for next game
        $_SESSION['target_number'] = generateTargetNumber($difficulty);
        $_SESSION['hearts'] = 3;
        $_SESSION['has_guessed'] = false;
        // Don't reset hints or correct_guesses counter to maintain progress
    }
    // Process guess
    elseif(isset($_POST['guess'])) {
        $guess = intval($_POST['guess_number']);
        
        // First guess of the game
        if(!$_SESSION['has_guessed']) {
            $_SESSION['has_guessed'] = true;
            // Update games played in database
            updateUserStats($conn, $_SESSION['user_id'], $difficulty, 'games_played');
        }
        
        if($guess == $_SESSION['target_number']) {
            $message = "Congratulations! You guessed the number!";
            $won = true;
            
            // Update games won in database
            updateUserStats($conn, $_SESSION['user_id'], $difficulty, 'games_won');
            
            // Add to highscores
            addHighscore($conn, $_SESSION['user_id'], $difficulty, $_SESSION['hearts']);
            
            // Increment correct guesses counter
            $_SESSION['correct_guesses']++;
            
            // Award hints based on difficulty and correct guesses
            if($difficulty == 'easy' && $_SESSION['correct_guesses'] % 3 == 0) {
                // Easy: +1 hint every 3 correct guesses
                $_SESSION['hints']++;
                $hint_reward = true;
            } elseif($difficulty == 'medium' && $_SESSION['correct_guesses'] % 2 == 0) {
                // Medium: +1 hint every 2 correct guesses
                $_SESSION['hints']++;
                $hint_reward = true;
            } elseif($difficulty == 'hard' && $_SESSION['correct_guesses'] % 2 == 0) {
                // Hard: +1 hint every 2 correct guesses
                $_SESSION['hints']++;
                $hint_reward = true;
            } elseif($difficulty == 'impossible' && rand(1, 2) == 1) {
                // Impossible: 50% chance of +1 hint for each correct guess
                $_SESSION['hints']++;
                $hint_reward = true;
            } else {
                $hint_reward = false;
            }
            
            // Generate a new number but keep the game going
            $_SESSION['target_number'] = generateTargetNumber($difficulty);
            $_SESSION['hearts'] = 3; // Reset hearts to full
            $_SESSION['has_guessed'] = false;
            
            // Set a success message to display
            if($hint_reward) {
                $success_message = "Correct! The number was $guess. You earned a hint!";
            } else {
                $success_message = "Correct! The number was $guess";
            }
        } else {
            // Wrong guess
            $_SESSION['hearts']--;
            
            if($_SESSION['hearts'] <= 0) {
                $message = "Game over! You ran out of hearts. The number was " . $_SESSION['target_number'];
                $game_over = true;
                
                // Update games lost in database
                updateUserStats($conn, $_SESSION['user_id'], $difficulty, 'games_lost');
                
                // Reset for next game
                $_SESSION['target_number'] = generateTargetNumber($difficulty);
                $_SESSION['hearts'] = 3;
                $_SESSION['has_guessed'] = false;
                // Don't reset hints or correct_guesses counter to maintain progress
            } else {
                // Still have hearts left
                $message = getRandomHintMessage($guess, $_SESSION['target_number']);
                // Set direction indicator
                if($guess < $_SESSION['target_number']) {
                    $_SESSION['direction'] = "Too low";
                } else {
                    $_SESSION['direction'] = "Too high";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Play - Cat Guess</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #000000;
            color: #ffffff;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-image: linear-gradient(to bottom right, rgba(255, 107, 74, 0.05), rgba(0, 0, 0, 0.1));
        }
        
        .container {
            max-width: 500px;
            width: 100%;
            padding: 30px;
            text-align: center;
            background-color: #1a1a1a;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: fadeIn 0.5s ease-out;
            position: relative;
            overflow: hidden;
        }
        
        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(to right, #FF6B4A, #ff3c1f);
        }
        
        .level-indicator {
            background-color: #FF6B4A;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 20px;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            letter-spacing: 1px;
        }
        
        .hearts {
            margin-bottom: 20px;
            font-size: 24px;
        }
        
        .heart {
            color: #FF4A4A;
            margin: 0 5px;
            filter: drop-shadow(0 2px 3px rgba(0, 0, 0, 0.3));
            transition: transform 0.2s ease;
        }
        
        .heart:hover {
            transform: scale(1.1);
        }
        
        .help-icon {
            width: 30px;
            height: 30px;
            background-color: #FF6B4A;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 20px;
            font-weight: bold;
            cursor: pointer;
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 100;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }
        
        .help-icon:hover {
            transform: scale(1.1);
            background-color: #e55a3c;
        }
        
        .tooltip {
            visibility: hidden;
            width: 280px;
            background-color: #333;
            color: #fff;
            text-align: center;
            padding: 10px;
            border-radius: 6px;
            position: absolute;
            z-index: 1;
            top: 55px;
            right: 0;
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .tooltip::after {
            content: "";
            position: absolute;
            bottom: 100%;
            right: 15px;
            margin-left: -5px;
            border-width: 8px;
            border-style: solid;
            border-color: transparent transparent #333 transparent;
        }
        
        .help-icon:hover .tooltip {
            visibility: visible;
            opacity: 1;
        }
        
        .hearts {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        
        .heart {
            color: #FF4A4A;
            font-size: 30px;
            margin: 0 5px;
        }
        
        .heart.empty {
            color: #444;
            filter: none;
        }
        
        .cat-container {
            position: relative;
            margin: 30px auto;
            width: 200px;
            height: 150px;
            transition: transform 0.3s ease;
        }
        
        .cat-container img {
            filter: drop-shadow(0 5px 15px rgba(0, 0, 0, 0.4));
            transition: transform 0.3s ease;
        }
        
        .cat-container img:hover {
            transform: scale(1.05);
        }
        
        .speech-bubble {
            position: absolute;
            top: -50px;
            right: -90px;
            background: url('images/bubble.png') no-repeat;
            background-size: 100% 100%;
            color: black;
            width: 195px;
            height: 120px;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 15px;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            animation: fadeIn 0.5s ease-out;
            z-index: 2;
        }
        
        .range-text {
            color: #FF6B4A;
            margin: 20px 0;
            font-size: 16px;
        }
        
        .hints-counter {
            margin: 10px 0;
            font-size: 16px;
        }
        
        input[type="number"] {
            width: 100%;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 2px solid #333;
            background-color: #222;
            color: white;
            font-size: 18px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.2);
        }
        
        input[type="number"]:focus {
            border-color: #FF6B4A;
            outline: none;
            box-shadow: 0 0 0 2px rgba(255, 107, 74, 0.25), inset 0 1px 3px rgba(0, 0, 0, 0.2);
        }
        
        .button-container {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .btn {
            flex: 1;
            padding: 15px 0;
            border-radius: 50px;
            border: none;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
            letter-spacing: 1px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        
        .btn:active {
            transform: translateY(1px);
        }
        
        .guess-btn {
            background-color: #FF6B4A;
            color: white;
        }
        
        .guess-btn:hover {
            background-color: #e55a3c;
        }
        
        .hint-btn {
            background-color: #4A90FF;
            color: white;
        }
        
        .hint-btn:hover {
            background-color: #3a80ef;
        }
        
        .give-up-btn {
            background-color: #FF4A4A;
            color: white;
            width: 100%;
            margin-top: 10px;
        }
        
        .hints-counter {
            color: #FF6B4A;
            margin: 15px 0;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 1px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }
        
        .direction-indicator {
            color: #aaa;
            margin: 10px 0;
            font-size: 14px;
            font-style: italic;
        }
        
        .message {
            margin: 20px 0;
            padding: 15px;
            background-color: rgba(255, 107, 74, 0.1);
            border-radius: 8px;
            border-left: 3px solid #FF6B4A;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="help-icon">?
        <div class="tooltip">
            <?php if($difficulty == 'easy'): ?>
                Easy: +1 hint every 3 correct guesses
            <?php elseif($difficulty == 'medium'): ?>
                Medium: +1 hint every 2 correct guesses
            <?php elseif($difficulty == 'hard'): ?>
                Hard: +1 hint every 2 correct guesses
            <?php else: ?>
                Impossible: 50% chance of +1 hint for each correct guess
            <?php endif; ?>
        </div>
    </div>
    
    <div class="container">
        <div class="level-indicator"><?php echo strtoupper($difficulty); ?> LEVEL</div>
        
        <div class="hearts">
            <?php for($i = 0; $i < 3; $i++): ?>
                <?php if($i < $_SESSION['hearts']): ?>
                    <span class="heart"><i class="fas fa-heart"></i></span>
                <?php else: ?>
                    <span class="heart empty"><i class="fas fa-heart"></i></span>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
        
        <div class="cat-container">
            <?php if(isset($success_message)): ?>
                <div class="speech-bubble"><?php echo $success_message; ?></div>
            <?php elseif(!empty($hint_message)): ?>
                <div class="speech-bubble"><?php echo $hint_message; ?></div>
            <?php elseif(!empty($message) && !$game_over): ?>
                <div class="speech-bubble"><?php echo $message; ?></div>
            <?php endif; ?>
            <div>
                <img src="images/<?php echo $difficulty; ?>.png" alt="Cat" style="width: 150px;">
            </div>
        </div>
            
        <div class="hints-counter">HINTS: <?php echo $_SESSION['hints']; ?></div>
        
        <?php if($game_over): ?>
            <div class="message"><?php echo $message; ?></div>
            <form method="post" action="">
                <button type="submit" name="new_game" class="btn guess-btn">PLAY AGAIN</button>
            </form>
            <a href="select_difficulty.php" class="btn" style="background-color: #333; color: white; border-radius: 50px; padding: 15px 0; display: block; text-decoration: none; margin-top: 10px;">BACK TO MENU</a>
        <?php else: ?>
            <form method="post" action="">
                <input type="number" name="guess_number" id="guess_number" min="1" max="<?php echo $difficultyMax; ?>" placeholder="Enter your guess (1-<?php echo $difficultyMax; ?>)">
                
                <div class="button-container">
                    <button type="submit" name="guess" class="btn guess-btn"><i class="fas fa-check-circle"></i> GUESS</button>
                    <button type="submit" name="hint" class="btn hint-btn"><i class="fas fa-lightbulb"></i> HINT</button>
                </div>
            </form>
            
            <div>
                <?php if($_SESSION['has_guessed']): ?>
                <form method="post" action="">
                    <button type="submit" name="give_up" class="btn give-up-btn"><i class="fas fa-flag"></i> GIVE UP</button>
                </form>
                <?php else: ?>
                <a href="select_difficulty.php" class="btn" style="background-color: #333; color: white; border-radius: 50px; padding: 15px 0; display: block; text-decoration: none;"><i class="fas fa-arrow-left"></i> BACK TO MENU</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Auto-hide speech bubble after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const speechBubble = document.querySelector('.speech-bubble');
            if (speechBubble) {
                setTimeout(function() {
                    speechBubble.style.display = 'none';
                }, 5000);
            }
        });
    </script>
</body>
</html>
