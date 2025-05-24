<?php
header('Content-Type: application/json');
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $firstName = trim($data['firstName']);
    $lastName = trim($data['lastName']);
    $email = trim($data['email']);
    $password = $data['password'];
    
    // Validate input
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit();
    }
    
    // Validate TIP email
    if (!preg_match('/^[a-zA-Z0-9._%+-]+@tip\.edu\.ph$/', $email)) {
        echo json_encode(['success' => false, 'message' => 'Please use your TIP email address (@tip.edu.ph)']);
        exit();
    }
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit();
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $firstName, $lastName, $email, $hashed_password);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Registration successful']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?> 