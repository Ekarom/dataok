<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "cfg/konek.php";

$nis_to_check = "9696";
$name_to_check = "ABDULLAH IBNU BATUTA";

echo "<h2>Checking for NIS: $nis_to_check</h2>";
$q1 = mysqli_query($sqlconn, "SELECT * FROM nilai WHERE TRIM(nis) = '$nis_to_check'");
if (mysqli_num_rows($q1) > 0) {
    echo "Found by NIS!<br>";
    print_r(mysqli_fetch_assoc($q1));
} else {
    echo "NOT Found by NIS.<br>";
}

echo "<h2>Checking for Name: $name_to_check</h2>";
$q2 = mysqli_query($sqlconn, "SELECT * FROM nilai WHERE TRIM(pd) LIKE '%$name_to_check%'");
if (mysqli_num_rows($q2) > 0) {
    echo "Found by Name!<br>";
    while($row = mysqli_fetch_assoc($q2)) {
        echo "Match: " . $row['pd'] . " (NIS: " . $row['nis'] . ")<br>";
    }
} else {
    echo "NOT Found by Name.<br>";
}

echo "<h2>Sample data from 'nilai' table</h2>";
$q3 = mysqli_query($sqlconn, "SELECT pd, nis FROM nilai LIMIT 10");
while($row = mysqli_fetch_assoc($q3)) {
    echo "PD: [" . $row['pd'] . "] | NIS: [" . $row['nis'] . "]<br>";
}
?>
