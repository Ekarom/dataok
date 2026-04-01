<?php
include "cfg/konek.php";
if ($sqlconn) {
    mysqli_select_db($sqlconn, 'dnet_ad2025');
    $res = mysqli_query($sqlconn, "SELECT userid FROM usera WHERE userid = 'ME'");
    echo "User 'ME' in dnet_ad2025: " . mysqli_num_rows($res) . "\n";
} else {
    echo "Connection failed.\n";
}
?>
