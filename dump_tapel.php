<?php
include 'cfg/konek.php';
$res = mysqli_query($sqlconn, "SELECT * FROM tapel");
while($row = mysqli_fetch_assoc($res)) {
    print_r($row);
}
?>
