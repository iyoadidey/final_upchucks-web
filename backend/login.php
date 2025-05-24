<?php
header('Content-Type: application/json');
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $email = trim($data['email']);
    $password = $data['password'];
    
    // Validate input
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit();
    }
    
    // Prepare a select statement
    $stmt = $conn->prepare("SELECT id, first_name, last_name, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            // Start session
            session_start();
            
            // Store data in session variables
            $_SESSION["loggedin"] = true;
            $_SESSION["id"] = $user['id'];
            $_SESSION["name"] = $user['first_name'] . " " . $user['last_name'];
            
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'name' => $_SESSION["name"]
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid password']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?> 