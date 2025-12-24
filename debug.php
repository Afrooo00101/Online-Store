<?php
// debug.php - Test database connection
echo "Testing database connection...<br>";

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'online_store';

// Test connection
$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    echo "Connection failed: " . $conn->connect_error . "<br>";
} else {
    echo "Connected to MySQL server successfully!<br>";
    
    // Check if database exists
    $result = $conn->query("SHOW DATABASES LIKE '$db'");
    if ($result->num_rows > 0) {
        echo "Database '$db' exists!<br>";
    } else {
        echo "Database '$db' does not exist. Creating...<br>";
        if ($conn->query("CREATE DATABASE $db")) {
            echo "Database created successfully!<br>";
        } else {
            echo "Error creating database: " . $conn->error . "<br>";
        }
    }
    
    $conn->close();
}
?>