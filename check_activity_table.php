<?php
include "cfg/konek.php";
$res = mysqli_query($sqlconn, "SHOW TABLES LIKE 'activity'");
echo mysqli_num_rows($res);
?>
