<?php
include "cfg/konek.php";
$res = mysqli_query($sqlconn, "SELECT * FROM siswa LIMIT 1");
if ($res) {
    print_r(mysqli_fetch_assoc($res));
}
?>
