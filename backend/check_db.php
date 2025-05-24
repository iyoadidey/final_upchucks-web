<?php
require_once 'config.php';

try {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Create users table if it doesn't exist
    $sql_users = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($sql_users)) {
        throw new Exception("Error creating users table: " . $conn->error);
    }

    // Create products table if it doesn't exist
    $sql_products = "CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        image_path VARCHAR(255),
        seller_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (seller_id) REFERENCES users(id)
    )";
    
    if (!$conn->query($sql_products)) {
        throw new Exception("Error creating products table: " . $conn->error);
    }

    // Create cart table if it doesn't exist
    $sql_cart = "CREATE TABLE IF NOT EXISTS cart (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (product_id) REFERENCES products(id)
    )";
    
    if (!$conn->query($sql_cart)) {
        throw new Exception("Error creating cart table: " . $conn->error);
    }

    // Create orders table if it doesn't exist
    $sql_orders = "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        status VARCHAR(50) NOT NULL DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    
    if (!$conn->query($sql_orders)) {
        throw new Exception("Error creating orders table: " . $conn->error);
    }

    // Create order_items table if it doesn't exist
    $sql_order_items = "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id),
        FOREIGN KEY (product_id) REFERENCES products(id)
    )";
    
    if (!$conn->query($sql_order_items)) {
        throw new Exception("Error creating order_items table: " . $conn->error);
    }

    echo "All tables created successfully!\n";
    
    // Show all tables and their structure
    $tables = ['users', 'products', 'cart', 'orders', 'order_items'];
    foreach ($tables as $table) {
        $result = $conn->query("DESCRIBE " . $table);
        if ($result) {
            echo "\nTable '" . $table . "' structure:\n";
            while ($row = $result->fetch_assoc()) {
                echo $row['Field'] . " - " . $row['Type'] . "\n";
            }
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?> 