<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if user is already logged in
if(isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';

// Process registration form
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);
    
    // Validate input
    if(empty($username) || empty($password) || empty($confirm_password)) {
        $error = "Please fill all fields.";
    } elseif(strlen($username) < 3 || strlen($username) > 20) {
        $error = "Username must be between 3 and 20 characters.";
    } elseif(strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif($password != $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if username already exists
        $user = getUserByUsername($conn, $username);
        
        if($user) {
            $error = "Username already exists.";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Prepare an insert statement
            $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ss", $username, $hashed_password);
            
            if(mysqli_stmt_execute($stmt)) {
                $success = "Registration successful! You can now login.";
            } else {
                $error = "Something went wrong. Please try again later.";
            }
            
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Cat Guess</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="images/logo.png" alt="Cat Guess Logo">
        </div>
        <h1>REGISTER</h1>
        <p class="subtitle">Create an account to play Cat Guess</p>
        
        <?php if(!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if(!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="form-container">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>    
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn primary-btn">Register</button>
                </div>
            </form>
        </div>
        
        <p style="margin-top: 20px;">Already have an account? <a href="login.php" style="color: #FF6B4A;">Login here</a></p>
        <p><a href="index.php" style="color: #888;">Back to Home</a></p>
    </div>
</body>
</html>
