<?php
include "cfg/konek.php";
$r = mysqli_query($sqlconn, "SELECT * FROM siswa LIMIT 1");
$f = mysqli_fetch_assoc($r);
print_r($f);
?>
