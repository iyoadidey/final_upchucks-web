<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once 'config.php';

$response = ["success" => false, "message" => "Unknown error."];

if (!isset($_SESSION['id'])) {
    $response['message'] = 'Not logged in.';
    echo json_encode($response);
    exit();
}

// Check required fields
if (empty($_POST['name']) || empty($_POST['price'])) {
    $response['message'] = 'Missing required fields.';
    echo json_encode($response);
    exit();
}

$name = $_POST['name'];
$price = $_POST['price'];
$image_path = null;
$seller_id = intval($_SESSION['id']);

// Handle image upload if present
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    $filename = uniqid() . '_' . basename($_FILES['image']['name']);
    $target_file = $upload_dir . $filename;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        $image_path = 'uploads/' . $filename;
    } else {
        $response['message'] = 'Failed to upload image.';
        echo json_encode($response);
        exit();
    }
}

// Create connection
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    $response['message'] = 'Database connection failed: ' . $conn->connect_error;
    echo json_encode($response);
    exit();
}

$stmt = $conn->prepare("INSERT INTO products (name, price, image_path, seller_id) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sdsi", $name, $price, $image_path, $seller_id);
if ($stmt->execute()) {
    $response['success'] = true;
    $response['message'] = 'Product added successfully!';
} else {
    $response['message'] = 'Database error: ' . $stmt->error;
}
$stmt->close();
$conn->close();
echo json_encode($response); 