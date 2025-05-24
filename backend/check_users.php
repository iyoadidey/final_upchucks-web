<?php
require_once 'config.php';

// Check if database connection is successful
if ($conn) {
    echo "<h2>Database Connection Status:</h2>";
    echo "<p style='color: green;'>✅ Connected successfully to MySQL database</p>";
    
    // Select all users from the database
    $sql = "SELECT id, first_name, last_name, email, created_at FROM users";
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        echo "<h2>Registered Users:</h2>";
        if (mysqli_num_rows($result) > 0) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr style='background-color: #f2f2f2;'>";
            echo "<th>ID</th>";
            echo "<th>First Name</th>";
            echo "<th>Last Name</th>";
            echo "<th>Email</th>";
            echo "<th>Registration Date</th>";
            echo "</tr>";
            
            while($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['first_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['last_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No users registered yet.</p>";
        }
    } else {
        echo "<p style='color: red;'>Error: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Connection failed: " . mysqli_connect_error() . "</p>";
}

$conn->close();
?> 