<?php
include "cfg/konek.php";
$r = mysqli_query($sqlconn, "DESCRIBE siswa");
while($f = mysqli_fetch_assoc($r)) echo $f['Field'] . "\n";
?>
