<?php
$conn = mysqli_connect("localhost", "root", "", "dnet_ad2025");
if (!$conn) die("Connection failed: " . mysqli_connect_error());

echo "Standardizing 'usera_log' table...\n";

// Helper function to check column
function hasColumn($conn, $table, $column) {
    $res = mysqli_query($conn, "SHOW COLUMNS FROM `$table` LIKE '$column'");
    return mysqli_num_rows($res) > 0;
}

$table = "usera_log";

// Rename if they exist with old names
if (hasColumn($conn, $table, 'user') && !hasColumn($conn, $table, 'user_id')) {
    mysqli_query($conn, "ALTER TABLE `$table` CHANGE `user` `user_id` VARCHAR(100)");
    echo "- Renamed 'user' to 'user_id'.\n";
}
if (hasColumn($conn, $table, 'nama') && !hasColumn($conn, $table, 'user_name')) {
    mysqli_query($conn, "ALTER TABLE `$table` CHANGE `nama` `user_name` VARCHAR(100)");
    echo "- Renamed 'nama' to 'user_name'.\n";
}
if (hasColumn($conn, $table, 'waktu') && !hasColumn($conn, $table, 'timestamp')) {
    mysqli_query($conn, "ALTER TABLE `$table` CHANGE `waktu` `timestamp` DATETIME DEFAULT CURRENT_TIMESTAMP");
    echo "- Renamed 'waktu' to 'timestamp'.\n";
}
if (hasColumn($conn, $table, 'info') && !hasColumn($conn, $table, 'details')) {
    mysqli_query($conn, "ALTER TABLE `$table` CHANGE `info` `details` TEXT");
    echo "- Renamed 'info' to 'details'.\n";
}

// Add missing columns
$to_add = [
    'action' => "VARCHAR(50) AFTER `user_name`",
    'url' => "VARCHAR(255) AFTER `details`",
    'user_agent' => "TEXT AFTER `ip`",
    'duration' => "VARCHAR(50) AFTER `user_agent`"
];

foreach ($to_add as $col => $def) {
    if (!hasColumn($conn, $table, $col)) {
        mysqli_query($conn, "ALTER TABLE `$table` ADD `$col` $def");
        echo "- Added column '$col'.\n";
    }
}

// Ensure activity_logs also exists and is standardized
echo "\nEnsuring 'activity_logs' is standardized...\n";
$sql_act = "CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(50),
    user_name VARCHAR(100),
    action VARCHAR(50),
    details TEXT,
    url VARCHAR(255),
    ip VARCHAR(45),
    user_agent TEXT,
    duration VARCHAR(50),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if (mysqli_query($conn, $sql_act)) {
    echo "- 'activity_logs' is ready.\n";
}

mysqli_close($conn);
?>
