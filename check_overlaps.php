<?php
include "cfg/konek.php";
$res = mysqli_query($sqlconn, "SELECT userid FROM usera WHERE userid IN (SELECT nis FROM siswa)");
if ($res) {
    while($row = mysqli_fetch_assoc($res)) {
        echo "Found potentially overlapping ID: " . $row['userid'] . "\n";
    }
}
?>
