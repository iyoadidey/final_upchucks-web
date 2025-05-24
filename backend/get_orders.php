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
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connection failed']);
    exit();
}

// Get orders (exclude cancelled)
$sql = "SELECT * FROM orders WHERE user_id = ? AND status != 'Cancelled' ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders_result = $stmt->get_result();

$orders = [];
while ($order = $orders_result->fetch_assoc()) {
    $order_id = $order['id'];
    // Get items for this order
    $items_stmt = $conn->prepare("SELECT oi.*, p.name, p.image_path FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
    $items_stmt->bind_param("i", $order_id);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();
    $items = [];
    while ($item = $items_result->fetch_assoc()) {
        $items[] = $item;
    }
    $items_stmt->close();
    $order['items'] = $items;
    $orders[] = $order;
}
$stmt->close();
$conn->close();

echo json_encode(['success' => true, 'orders' => $orders]); 