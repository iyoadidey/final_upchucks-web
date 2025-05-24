<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";

echo "<h2>Database Connection Test</h2>";

// Test database connection
try {
    $conn = new mysqli($servername, $username, $password);
    echo "<p style='color: green;'>✅ Successfully connected to MySQL server</p>";
    
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS upchucks_db";
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>✅ Database 'upchucks_db' created or already exists</p>";
        
        // Select the database
        $conn->select_db("upchucks_db");
        
        // Create users table if it doesn't exist
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color: green;'>✅ Table 'users' created or already exists</p>";
            
            // Show table structure
            $result = $conn->query("DESCRIBE users");
            echo "<h3>Table Structure:</h3>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr style='background-color: #f2f2f2;'>";
            echo "<th>Field</th>";
            echo "<th>Type</th>";
            echo "<th>Null</th>";
            echo "<th>Key</th>";
            echo "<th>Default</th>";
            echo "</tr>";
            
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: red;'>❌ Error creating table: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Error creating database: " . $conn->error . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Connection failed: " . $e->getMessage() . "</p>";
}

$conn->close();
?> 