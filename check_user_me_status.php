<?php
include "cfg/konek.php";
if ($sqlconn) {
    mysqli_select_db($sqlconn, 'dnet_ad2025');
    $res = mysqli_query($sqlconn, "SELECT status FROM usera WHERE userid = 'ME'");
    $row = mysqli_fetch_assoc($res);
    echo "Status of user 'ME' in dnet_ad2025: " . $row['status'] . "\n";
} else {
    echo "Connection failed.\n";
}
?>
