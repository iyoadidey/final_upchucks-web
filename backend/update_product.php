<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once 'config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$seller_id = intval($_SESSION['id']);
$product_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$price = isset($_POST['price']) ? floatval($_POST['price']) : 0;

// Debug information
error_log("Seller ID: " . $seller_id);
error_log("Product ID: " . $product_id);
error_log("Name: " . $name);
error_log("Price: " . $price);

if (!$product_id || !$name || !$price) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

try {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }

    // First verify the product belongs to the seller
    $stmt = $conn->prepare("SELECT seller_id FROM products WHERE id = ?");
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $product_id);
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if (!$product) {
        throw new Exception('Product not found');
    }

    if ($product['seller_id'] != $seller_id) {
        throw new Exception('Unauthorized to edit this product');
    }

    // Update product name and price
    $update_stmt = $conn->prepare("UPDATE products SET name = ?, price = ? WHERE id = ? AND seller_id = ?");
    if (!$update_stmt) {
        throw new Exception('Prepare update failed: ' . $conn->error);
    }
    
    $update_stmt->bind_param("sdii", $name, $price, $product_id, $seller_id);
    if (!$update_stmt->execute()) {
        throw new Exception('Update failed: ' . $update_stmt->error);
    }
    
    if ($update_stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made to the product']);
    }
    
    $update_stmt->close();

} catch (Exception $e) {
    error_log("Error in update_product.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?> 