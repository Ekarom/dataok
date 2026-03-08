<?php
include "cfg/konek.php";

if ($sqlconn->connect_error) {
    die("Connection failed: " . $sqlconn->connect_error);
}

echo "Connected successfully to database.<br>";

$sql = "CREATE TABLE IF NOT EXISTS login_attempts (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    attempt_time INT(11) NOT NULL
)";

if ($sqlconn->query($sql) === TRUE) {
    echo "Table login_attempts created successfully or already exists.<br>";
} else {
    echo "Error creating table: " . $sqlconn->error . "<br>";
}

// Test Insert
$ip = '127.0.0.1';
$time = time();
$sql_insert = "INSERT INTO login_attempts (ip_address, attempt_time) VALUES ('$ip', '$time')";

if ($sqlconn->query($sql_insert) === TRUE) {
    echo "Test insert successful.<br>";
} else {
    echo "Error inserting test data: " . $sqlconn->error . "<br>";
}

// Check data
$result = $sqlconn->query("SELECT * FROM login_attempts");
if ($result->num_rows > 0) {
    echo "Data found in table:<br>";
    while($row = $result->fetch_assoc()) {
        echo "id: " . $row["id"]. " - IP: " . $row["ip_address"]. " - Time: " . $row["attempt_time"]. "<br>";
    }
} else {
    echo "0 results in table.<br>";
}

$sqlconn->close();
?>
