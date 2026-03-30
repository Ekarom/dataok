<?php
// get_semester.php

error_reporting(0); // Prevent warnings from polluting JSON output
header('Content-Type: application/json');

if (isset($_POST['database_name'])) {
    
    $database = $_POST['database_name'];

    // 1. Input Validation: Only allow alphanumeric and underscores
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $database)) {
        echo json_encode(['error' => 'Format database tidak valid']);
        exit;
    }

    $server = "localhost";
    $username = "root";
    $password = "";

    try {
        // 2. Robust Connection (Handles PHP 8.1+ exceptions)
        $conn = new mysqli($server, $username, $password, $database);

        if ($conn->connect_error) {
            throw new Exception("Koneksi gagal: " . $conn->connect_error);
        }

        // 3. Query Semesters with table check
        $query = "SELECT DISTINCT smt FROM tapel ORDER BY smt ASC";
        $result = $conn->query($query);

        if (!$result) {
            throw new Exception("Tabel tapel tidak ditemukan di database '" . $database . "'");
        }

        $semesters = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $semesters[] = $row['smt'];
            }
        }

        echo json_encode($semesters);
        $conn->close();

    } catch (Throwable $e) {
        // Log specifically for debugging
        error_log("get_semester.php Error: " . $e->getMessage());
        
        echo json_encode(['error' => $e->getMessage()]);
        if (isset($conn) && $conn instanceof mysqli && $conn->ping()) {
            $conn->close();
        }
        exit;
    }
}
else {
    echo json_encode(['error' => 'Nama database tidak disertakan']);
}
?>
