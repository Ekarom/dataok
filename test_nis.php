<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "cfg/konek.php";
if (!$sqlconn) die("Connection failed: " . mysqli_connect_error());

$q = mysqli_query($sqlconn, "SELECT id, pd, nis FROM siswa LIMIT 5");
if (!$q) die("Error in siswa query: " . mysqli_error($sqlconn));

echo "<h2>SISWA</h2>";
while ($row = mysqli_fetch_assoc($q)) {
    echo "ID: " . $row['id'] . " | PD: " . $row['pd'] . " | NIS: [" . $row['nis'] . "]<br>\n";
}

echo "<hr>";

$qn = mysqli_query($sqlconn, "SELECT id, pd, nis FROM nilai LIMIT 5");
if (!$qn) die("Error in nilai query: " . mysqli_error($sqlconn));

echo "<h2>NILAI</h2>";
while ($row = mysqli_fetch_assoc($qn)) {
    echo "ID: " . $row['id'] . " | PD: " . $row['pd'] . " | NIS: [" . $row['nis'] . "]<br>\n";
}
?>
