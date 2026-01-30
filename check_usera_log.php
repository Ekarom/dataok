<?php
$conn = mysqli_connect("localhost", "root", "", "dnet_ad2025");
if (!$conn) die("Connection failed: " . mysqli_connect_error());

$result = mysqli_query($conn, "DESCRIBE usera_log");
while ($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
mysqli_close($conn);
?>
