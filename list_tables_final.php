<?php
include "cfg/konek.php";
$res = mysqli_query($sqlconn, "SHOW TABLES");
if ($res) {
    while($row = mysqli_fetch_array($res)) {
        echo $row[0] . "\n";
    }
} else {
    echo "Query failed: " . mysqli_error($sqlconn);
}
?>
