<?php
include 'c:/wamp64/www/data/cfg/konek.php';
$res = mysqli_query($sqlconn, 'SELECT * FROM login_attempts');
if ($res) {
    echo "Rows in login_attempts:\n";
    while($row = mysqli_fetch_assoc($res)) {
        print_r($row);
    }
} else {
    echo "Error: " . mysqli_error($sqlconn) . "\n";
}
?>
