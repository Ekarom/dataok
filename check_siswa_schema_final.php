<?php
include "cfg/konek.php";
if ($sqlconn) {
    echo "Columns for 'siswa' table:\n";
    $res = mysqli_query($sqlconn, "SHOW COLUMNS FROM siswa");
    if ($res) {
        while($row = mysqli_fetch_assoc($res)) {
            echo $row['Field'] . "\n";
        }
    } else {
        echo "Query failed: " . mysqli_error($sqlconn) . "\n";
    }
} else {
    echo "Connection failed.\n";
}
?>
