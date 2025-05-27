<?php
require_once '../includes/session_config.php';
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if admin is already logged in
if(isLoggedIn() && isAdmin()) {
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
            // Verify the password and check if user is admin
            if(password_verify($password, $user["password"]) && $user["is_admin"] == 1) {
                // Password is correct and user is admin, store data in session variables
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["is_admin"] = $user["is_admin"];
                
                // Redirect admin to dashboard
                redirect("index.php");
            } else {
                $error = "Invalid admin credentials or insufficient privileges.";
            }
        } else {
            $error = "Invalid admin credentials.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Cat Guess</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-color: var(--darker-bg);
            background-image: linear-gradient(to bottom right, rgba(255, 107, 74, 0.05), rgba(0, 0, 0, 0.1));
        }
        
        .admin-login-container {
            width: 100%;
            max-width: 400px;
            background-color: var(--dark-bg);
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: fadeIn 0.5s ease-out;
            position: relative;
            overflow: hidden;
        }
        
        .admin-login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(to right, var(--primary-color), var(--danger-color));
        }
        
        .admin-login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .admin-login-header h1 {
            color: var(--primary-color);
            margin-bottom: 10px;
            font-size: 28px;
            letter-spacing: 1px;
        }
        
        .admin-login-header p {
            color: var(--muted-text);
            font-size: 16px;
        }
        
        .admin-login-form .form-group {
            margin-bottom: 25px;
        }
        
        .admin-login-form label {
            display: block;
            margin-bottom: 8px;
            color: var(--muted-text);
            font-weight: bold;
            font-size: 14px;
        }
        
        .admin-login-form input {
            width: 100%;
            padding: 14px;
            border-radius: 5px;
            border: 1px solid var(--border-color);
            background-color: var(--medium-bg);
            color: var(--light-text);
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .admin-login-form input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(255, 107, 74, 0.25);
            outline: none;
        }
        
        .admin-login-form button {
            width: 100%;
            padding: 14px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            transition: all 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }
        
        .admin-login-form button:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .back-link {
            text-align: center;
            margin-top: 30px;
        }
        
        .back-link a {
            color: var(--muted-text);
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .back-link a:hover {
            color: var(--primary-color);
        }
        
        .logo {
            margin-bottom: 20px;
            text-align: center;
        }
        
        .logo img {
            max-width: 80px;
            height: auto;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="admin-login-container">
        <div class="logo">
            <img src="../images/logo.png" alt="Cat Guess Logo">
        </div>
        
        <div class="admin-login-header">
            <h1>ADMIN LOGIN</h1>
            <p>Access Admin Dashboard</p>
        </div>
        
        <?php if(!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="admin-login-form">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Admin Username</label>
                    <input type="text" name="username" placeholder="Enter admin username" required>
                </div>    
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Admin Password</label>
                    <input type="password" name="password" placeholder="Enter admin password" required>
                </div>
                <div class="form-group">
                    <button type="submit"><i class="fas fa-sign-in-alt"></i> Login to Dashboard</button>
                </div>
            </form>
        </div>
        
        <div class="back-link">
            <a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Main Site</a>
        </div>
    </div>
</body>
</html>
