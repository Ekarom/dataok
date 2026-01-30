<?php
$conn = mysqli_connect("localhost", "root", "", "dnet_ad2025");
if (!$conn) die("Connection failed: " . mysqli_connect_error());

echo "Checking tables in dnet_ad2025:\n";

$tables = ['usera_log', 'activity_logs', 'activity_log'];
foreach ($tables as $t) {
    $res = mysqli_query($conn, "SHOW TABLES LIKE '$t'");
    if (mysqli_num_rows($res) > 0) {
        echo "- Table '$t' exists.\n";
        $columns = mysqli_query($conn, "DESCRIBE `$t`");
        while ($col = mysqli_fetch_assoc($columns)) {
            echo "  |-- " . $col['Field'] . " (" . $col['Type'] . ")\n";
        }
    } else {
        echo "- Table '$t' does not exist.\n";
    }
}

mysqli_close($conn);
?>
