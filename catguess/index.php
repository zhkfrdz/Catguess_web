<?php
require_once 'includes/session_config.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cat Guess - Number Guessing Game</title>
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
            width: 100%;
            max-width: 500px;
            background-color: #1a1a1a;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: fadeIn 0.5s ease-out;
            position: relative;
            overflow: hidden;
            text-align: center;
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
        
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .logo img {
            max-width: 100px;
            height: auto;
            transition: transform 0.3s ease;
        }
        
        .logo img:hover {
            transform: scale(1.05);
        }
        
        h1 {
            color: #FF6B4A;
            text-align: center;
            margin-bottom: 5px;
            font-size: 36px;
            letter-spacing: 2px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .subtitle {
            text-align: center;
            color: #aaa;
            margin-top: 0;
            margin-bottom: 30px;
            font-size: 18px;
        }
        
        .menu {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .btn {
            padding: 14px;
            border-radius: 50px;
            border: none;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            transition: all 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        
        .primary-btn {
            background-color: #FF6B4A;
            color: white;
        }
        
        .primary-btn:hover {
            background-color: #e55a3c;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .outline-btn {
            background-color: transparent;
            color: #FF6B4A;
            border: 2px solid #FF6B4A;
        }
        
        .outline-btn:hover {
            background-color: rgba(255, 107, 74, 0.1);
            transform: translateY(-2px);
        }
        
        .login-prompt {
            margin: 20px 0;
        }
        
        .login-prompt p {
            margin-bottom: 20px;
            color: #aaa;
            font-size: 18px;
        }
        
        .login-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="images/logo.png" alt="Cat Guess Logo">
        </div>
        <h1>CAT GUESS</h1>
        <p class="subtitle">Test your luck and skills!</p>

        <?php if(isset($_SESSION['username'])): ?>
            <div class="menu">
                <a href="select_difficulty.php" class="btn primary-btn"><i class="fas fa-play"></i> Play</a>
                <a href="highscores.php" class="btn primary-btn"><i class="fas fa-trophy"></i> Highscores</a>
                <a href="stats.php" class="btn primary-btn"><i class="fas fa-chart-bar"></i> Stats</a>
                <a href="settings.php" class="btn primary-btn"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php" class="btn outline-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        <?php else: ?>
            <div class="login-prompt">
                <p>Please login to play the game</p>
                <div class="login-buttons">
                    <a href="login.php" class="btn primary-btn"><i class="fas fa-sign-in-alt"></i> Login</a>
                    <a href="register.php" class="btn outline-btn"><i class="fas fa-user-plus"></i> Register</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <script src="js/script.js"></script>
</body>
</html>
