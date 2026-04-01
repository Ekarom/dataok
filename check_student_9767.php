<?php
include "cfg/konek.php";
$res = mysqli_query($sqlconn, "SELECT * FROM siswa WHERE nis = '9767'");
print_r(mysqli_fetch_assoc($res));
?>
