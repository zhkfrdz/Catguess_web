<?php
require_once 'includes/session_config.php';
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if user is already logged in
if(isLoggedIn()) {
    redirect('index.php');
}

$error = '';

// Process login form
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    
    // Validate credentials
    if(empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        // Prepare a select statement
        $user = getUserByUsername($conn, $username);
        
        if($user) {
            // Verify the password
            if(password_verify($password, $user["password"])) {
                // Password is correct, store data in session variables
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["is_admin"] = $user["is_admin"];
                
                // Redirect user to welcome page
                if($user["is_admin"] == 1) {
                    redirect("admin/index.php");
                } else {
                    redirect("index.php");
                }
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cat Guess</title>
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
            max-width: 400px;
            background-color: #1a1a1a;
            border-radius: 10px;
            padding: 40px;
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
        
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .logo img {
            max-width: 80px;
            height: auto;
        }
        
        h1 {
            color: #FF6B4A;
            text-align: center;
            margin-bottom: 5px;
            font-size: 28px;
            letter-spacing: 1px;
        }
        
        .subtitle {
            text-align: center;
            color: #aaa;
            margin-top: 0;
            margin-bottom: 25px;
            font-size: 16px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: bold;
            animation: fadeIn 0.5s;
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.2);
            border: 1px solid #dc3545;
            color: #f5a9b0;
        }
        
        .form-container {
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #aaa;
            font-weight: bold;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .form-control {
            width: 100%;
            padding: 14px;
            border-radius: 5px;
            border: 1px solid #333;
            background-color: #222;
            color: #fff;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #FF6B4A;
            box-shadow: 0 0 0 2px rgba(255, 107, 74, 0.25);
            outline: none;
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
            width: 100%;
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
        
        .links {
            text-align: center;
        }
        
        .links a {
            color: #FF6B4A;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: bold;
        }
        
        .links a:hover {
            color: #e55a3c;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: #888 !important;
            font-weight: normal !important;
        }
        
        .back-link:hover {
            color: #FF6B4A !important;
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
        <h1>LOGIN</h1>
        <p class="subtitle">Sign in to play Cat Guess</p>
        
        <?php if(!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="form-container">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Enter your username" required>
                </div>    
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn primary-btn"><i class="fas fa-sign-in-alt"></i> Login</button>
                </div>
            </form>
        </div>
        
        <div class="links">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
            <p><a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Home</a></p>
        </div>
    </div>
</body>
</html>
