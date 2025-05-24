<?php
header("Content-Type: application/json; charset=UTF-8");
require_once 'config.php';

$response = array();

// Test database connection
try {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    if ($conn->connect_error) {
        $response['connection'] = "Failed: " . $conn->connect_error;
    } else {
        $response['connection'] = "Success";
        
        // Check if products table exists
        $result = $conn->query("SHOW TABLES LIKE 'products'");
        $response['products_table_exists'] = ($result->num_rows > 0);
        
        // Try to get all products
        $result = $conn->query("SELECT * FROM products");
        if ($result) {
            $products = array();
            while($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
            $response['products'] = $products;
            $response['products_count'] = count($products);
        } else {
            $response['products_error'] = $conn->error;
        }
    }
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response, JSON_PRETTY_PRINT);
?> 