<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'config.php';

$response = array(
    'database_connection' => false,
    'database_exists' => false,
    'users_table_exists' => false,
    'error' => null
);

try {
    // Test basic connection
    $test_conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD);
    $response['database_connection'] = true;

    // Check if database exists
    $result = $test_conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");
    $response['database_exists'] = ($result && $result->num_rows > 0);

    // Select the database
    if ($response['database_exists']) {
        $test_conn->select_db(DB_NAME);
        
        // Check if users table exists
        $result = $test_conn->query("SHOW TABLES LIKE 'users'");
        $response['users_table_exists'] = ($result && $result->num_rows > 0);

        // If users table doesn't exist, create it
        if (!$response['users_table_exists']) {
            $sql = "CREATE TABLE users (
                id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                first_name VARCHAR(50) NOT NULL,
                last_name VARCHAR(50) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )";
            
            if ($test_conn->query($sql)) {
                $response['users_table_exists'] = true;
                $response['message'] = 'Users table created successfully';
            } else {
                throw new Exception('Error creating users table: ' . $test_conn->error);
            }
        }
    } else {
        // Create database if it doesn't exist
        if ($test_conn->query("CREATE DATABASE " . DB_NAME)) {
            $response['database_exists'] = true;
            $response['message'] = 'Database created successfully';
        } else {
            throw new Exception('Error creating database: ' . $test_conn->error);
        }
    }

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
} finally {
    if (isset($test_conn)) {
        $test_conn->close();
    }
}

echo json_encode($response, JSON_PRETTY_PRINT);
?> 