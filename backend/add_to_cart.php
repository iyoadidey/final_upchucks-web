<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once 'config.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user_id = intval($_SESSION['id']);
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'Missing product id']);
    exit();
}

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connection failed']);
    exit();
}

$stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $cart_id = $row['id'];
    $quantity = $row['quantity'] + 1;
    $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $update->bind_param("ii", $quantity, $cart_id);
    $update->execute();
    $update->close();
} else {
    $insert = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
    $insert->bind_param("ii", $user_id, $product_id);
    $insert->execute();
    $insert->close();
}

$stmt->close();
$conn->close();
echo json_encode(['success' => true]); 