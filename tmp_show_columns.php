<?php
include "cfg/konek.php";
$res = mysqli_query($sqlconn, "SHOW COLUMNS FROM siswa");
while ($row = mysqli_fetch_assoc($res)) {
    echo $row['Field'] . "\n";
}
?>
