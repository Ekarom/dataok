<?php
include "cfg/konek.php";
$res = mysqli_query($sqlconn, "SHOW TABLES");
while ($row = mysqli_fetch_array($res)) {
    echo $row[0] . "\n";
}
?>
