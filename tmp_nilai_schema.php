<?php
include "cfg/konek.php";
$q = mysqli_query($sqlconn, "DESCRIBE nilai");
while($r = mysqli_fetch_assoc($q)) {
    echo $r['Field'] . " - " . $r['Type'] . "\n";
}
?>
