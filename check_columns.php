<?php
include "cfg/konek.php";
$res = mysqli_query($sqlconn, "SHOW COLUMNS FROM usera");
while($row = mysqli_fetch_assoc($res)) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}
?>
