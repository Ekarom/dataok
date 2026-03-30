<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$database = "dnet2025"; // Default from config.php

$server = "localhost";
$username = "root";
$password = "";

echo "Testing connection to $database...\n";
try {
    $conn = new mysqli($server, $username, $password, $database);
    if ($conn->connect_error) {
        echo "Connection Error: " . $conn->connect_error . "\n";
    } else {
        echo "Connected successfully.\n";
        $query = "SELECT DISTINCT smt FROM tapel ORDER BY smt ASC";
        $result = $conn->query($query);
        if (!$result) {
            echo "Query Error: " . $conn->error . "\n";
        } else {
            echo "Rows found: " . $result->num_rows . "\n";
            while ($row = $result->fetch_assoc()) {
                echo " - Semester: " . $row['smt'] . "\n";
            }
        }
        $conn->close();
    }
} catch (Throwable $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
