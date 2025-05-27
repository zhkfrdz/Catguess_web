<?php
// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'catguess123');
define('DB_NAME', 'catguess_db');

// Attempt to connect to MySQL database
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);

// Check connection
if(!$conn) {
    die("ERROR: Could not connect to MySQL. " . mysqli_connect_error());
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if(mysqli_query($conn, $sql)) {
    // Select the database
    mysqli_select_db($conn, DB_NAME);
    
    // Create users table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        is_admin TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    mysqli_query($conn, $sql);
    
    // Create game_stats table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS game_stats (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        difficulty VARCHAR(10) NOT NULL,
        games_played INT(11) NOT NULL DEFAULT 0,
        games_won INT(11) NOT NULL DEFAULT 0,
        games_lost INT(11) NOT NULL DEFAULT 0,
        hints_used INT(11) NOT NULL DEFAULT 0,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    mysqli_query($conn, $sql);
    
    // Create highscores table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS highscores (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        difficulty VARCHAR(10) NOT NULL,
        score INT(11) NOT NULL,
        date_achieved TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    mysqli_query($conn, $sql);
    
    // Create admin user if it doesn't exist
    $admin_check = "SELECT id FROM users WHERE username = 'admin'";
    $result = mysqli_query($conn, $admin_check);
    if(mysqli_num_rows($result) == 0) {
        // Create admin user with password 'admin123'
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password, is_admin) VALUES ('admin', '$admin_password', 1)";
        mysqli_query($conn, $sql);
    }
} else {
    die("ERROR: Could not create database. " . mysqli_error($conn));
}

// Define global settings
define('SITE_NAME', 'Cat Guess');
define('SITE_URL', 'http://localhost/catguess');

?>
