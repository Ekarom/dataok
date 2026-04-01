<?php
include "cfg/konek.php";
if ($sqlconn) {
    mysqli_select_db($sqlconn, 'dnet_ad2025');
    echo "Columns for 'prestasi' table:\n";
    $res = mysqli_query($sqlconn, "SHOW COLUMNS FROM prestasi");
    if ($res) {
        while($row = mysqli_fetch_array($res)) {
            echo $row[0] . "\n";
        }
    } else {
        echo "Query failed: " . mysqli_error($sqlconn);
    }
}
?>
