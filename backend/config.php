<?php
// Database configuration
$is_production = false; // Set to true when deploying to production

if ($is_production) {
    // Production database settings
    $db_host = getenv('DB_HOST') ?: 'your_production_host';
    $db_name = getenv('DB_NAME') ?: 'your_production_db';
    $db_user = getenv('DB_USER') ?: 'your_production_user';
    $db_pass = getenv('DB_PASS') ?: 'your_production_password';
} else {
    // Local development settings
    $db_host = 'localhost';
    $db_name = 'upchucks_db';
    $db_user = 'root';
    $db_pass = '';
}

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to ensure proper encoding
$conn->set_charset("utf8mb4");

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS " . $db_name;
if (mysqli_query($conn, $sql)) {
    mysqli_select_db($conn, $db_name);
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!mysqli_query($conn, $sql)) {
        die("Error creating table: " . mysqli_error($conn));
    }

    // Create products table
    $sql = "CREATE TABLE IF NOT EXISTS products (
        id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(40) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        image_path VARCHAR(255),
        seller_id INT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (seller_id) REFERENCES users(id)
    )";

    if (!mysqli_query($conn, $sql)) {
        die("Error creating table: " . mysqli_error($conn));
    }
} else {
    die("Error creating database: " . mysqli_error($conn));
}

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', ''); // default for XAMPP
define('DB_NAME', 'upchucks_db');
?> 