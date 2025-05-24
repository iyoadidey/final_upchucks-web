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
$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;

if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'Missing order id']);
    exit();
}

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connection failed']);
    exit();
}

// Only allow cancel if the order belongs to the user and is not already cancelled
$stmt = $conn->prepare("UPDATE orders SET status = 'Cancelled' WHERE id = ? AND user_id = ? AND status != 'Cancelled'");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();
$conn->close();

if ($affected > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Order not found or already cancelled']);
} 