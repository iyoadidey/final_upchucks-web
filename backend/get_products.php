<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once 'config.php';

$response = array(
    'success' => false,
    'message' => '',
    'products' => array()
);

try {
    // Create connection
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    // Check connection
    if ($conn->connect_error) {
        $response['message'] = "Connection failed: " . $conn->connect_error;
        echo json_encode($response);
        exit();
    }

    $sql = "SELECT * FROM products ORDER BY created_at DESC";
    $result = $conn->query($sql);

    if ($result === false) {
        $response['message'] = "Query failed: " . $conn->error;
    } else {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $response['products'][] = $row;
            }
            $response['success'] = true;
            $response['message'] = "Products retrieved successfully";
        } else {
            $response['success'] = true;
            $response['message'] = "No products found";
        }
    }

    $conn->close();
} catch (Exception $e) {
    $response['message'] = "Error: " . $e->getMessage();
}

echo json_encode($response); 