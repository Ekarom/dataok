<?php
include "cfg/konek.php";
$res = mysqli_query($sqlconn, "SELECT * FROM usera_log ORDER BY id DESC LIMIT 5");
if ($res) {
    while($row = mysqli_fetch_assoc($res)) {
        print_r($row);
    }
} else {
    echo "Query failed: " . mysqli_error($sqlconn);
}
?>
