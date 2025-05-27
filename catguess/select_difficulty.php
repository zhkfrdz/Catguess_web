<?php
require_once 'includes/session_config.php';
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if user is logged in
if(!isLoggedIn()) {
    redirect('login.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Difficulty - Cat Guess</title>
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
            display: flex;
            flex-direction: column;
            gap: 20px;
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
        
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        
        .logo {
            max-width: 80px;
            margin: 0 auto 20px;
        }
        
        .logo img {
            width: 100%;
            height: auto;
        }
        
        .title {
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            letter-spacing: 2px;
            margin-bottom: 30px;
            color: #FF6B4A;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .difficulty-card {
            border: 2px solid #333;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-decoration: none;
            color: white;
            transition: all 0.3s ease;
            background-color: #222;
        }
        
        .difficulty-card:hover {
            background-color: rgba(255, 107, 74, 0.1);
            border-color: #FF6B4A;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255, 107, 74, 0.2);
        }
        
        .difficulty-info {
            text-align: left;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .difficulty-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .difficulty-range {
            color: #FF6B4A;
            font-size: 14px;
        }
        
        .difficulty-number {
            background-color: #FF6B4A;
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        
        .back-btn {
            background-color: #333;
            color: white;
            border-radius: 50px;
            padding: 14px 0;
            display: flex;
            justify-content: center;
            align-items: center;
            text-decoration: none;
            font-weight: bold;
            margin-top: 20px;
            transition: all 0.3s ease;
            gap: 8px;
        }
        
        .back-btn:hover {
            background-color: #444;
            transform: translateY(-2px);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <img src="images/logo.png" alt="Cat Guess Logo">
            </div>
        </div>
        
        <div class="title">CHOOSE LEVEL</div>
        
        <a href="play.php?difficulty=easy" class="difficulty-card">
            <div class="difficulty-info">
                <div class="difficulty-name">EASY</div>
                <div class="difficulty-range">1-10 range</div>
            </div>
            <div class="difficulty-number">1</div>
        </a>
        
        <a href="play.php?difficulty=medium" class="difficulty-card">
            <div class="difficulty-info">
                <div class="difficulty-name">MEDIUM</div>
                <div class="difficulty-range">1-30 range</div>
            </div>
            <div class="difficulty-number">2</div>
        </a>
        
        <a href="play.php?difficulty=hard" class="difficulty-card">
            <div class="difficulty-info">
                <div class="difficulty-name">HARD</div>
                <div class="difficulty-range">1-60 range</div>
            </div>
            <div class="difficulty-number">3</div>
        </a>
        
        <a href="play.php?difficulty=impossible" class="difficulty-card">
            <div class="difficulty-info">
                <div class="difficulty-name">IMPOSSIBLE</div>
                <div class="difficulty-range">1-100 range</div>
            </div>
            <div class="difficulty-number">4</div>
        </a>
        
        <a href="index.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> BACK TO MENU
        </a>
    </div>
</body>
</html>
