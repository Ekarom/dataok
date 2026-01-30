<?php
include "cfg/konek.php";

echo "--- Checking 'prestasi' table schema ---\n";
$res = mysqli_query($sqlconn, "DESCRIBE prestasi");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        echo "Field: {$row['Field']} | Type: {$row['Type']} | Null: {$row['Null']} | Default: {$row['Default']}\n";
    }
} else {
    echo "Error describing table: " . mysqli_error($sqlconn) . "\n";
}

echo "\n--- Checking folders ---\n";
$year = date('Y');
$dirs = ["file/prestasi/", "file/prestasi/$year/"];
foreach ($dirs as $dir) {
    if (file_exists($dir)) {
        echo "$dir: EXISTS " . (is_writable($dir) ? "(Writable)" : "(Not Writable)") . "\n";
    } else {
        echo "$dir: NOT FOUND\n";
    }
}
?>
