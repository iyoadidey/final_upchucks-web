<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['loggedin' => false]);
    exit;
}

// Return user data if logged in
$user_id = isset($_SESSION["id"]) ? $_SESSION["id"] : null;
echo json_encode([
    'loggedin' => true,
    'name' => $_SESSION["name"],
    'user_id' => $user_id
]);
?> 