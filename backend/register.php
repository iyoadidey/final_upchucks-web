<?php
// Allow requests from any origin during development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

require_once 'config.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON data received');
        }
        
        if (!isset($data['firstName']) || !isset($data['lastName']) || !isset($data['email']) || !isset($data['password'])) {
            throw new Exception('Missing required fields');
        }
        
        $firstName = trim($data['firstName']);
        $lastName = trim($data['lastName']);
        $email = trim($data['email']);
        $password = $data['password'];
        
        // Validate input
        if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
            throw new Exception('All fields are required');
        }
        
        // Validate TIP email
        if (!preg_match('/^[a-zA-Z0-9._%+-]+@tip\.edu\.ph$/', $email)) {
            throw new Exception('Please use your TIP email address (@tip.edu.ph)');
        }
        
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if (!$stmt) {
            throw new Exception('Database prepare error: ' . $conn->error);
        }
        
        $stmt->bind_param("s", $email);
        if (!$stmt->execute()) {
            throw new Exception('Database execute error: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            throw new Exception('Email already exists');
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception('Database prepare error: ' . $conn->error);
        }
        
        $stmt->bind_param("ssss", $firstName, $lastName, $email, $hashed_password);
        if (!$stmt->execute()) {
            throw new Exception('Registration failed: ' . $stmt->error);
        }
        
        echo json_encode(['success' => true, 'message' => 'Registration successful']);
        
    } catch (Exception $e) {
        error_log('Registration error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
        if (isset($conn)) {
            $conn->close();
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?> 