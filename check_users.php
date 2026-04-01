<?php
include "cfg/konek.php";
$res = mysqli_query($sqlconn, "SELECT * FROM usera LIMIT 5");
while($row = mysqli_fetch_assoc($res)) {
    // Only show necessary fields for debugging
    unset($row['password']); // Don't show full password hash for privacy
    print_r($row);
}
?>
