<?php
$conn = new mysqli('localhost', 'root', '', '');
$res = $conn->query('SHOW DATABASES');
while($row = $res->fetch_row()) {
    echo $row[0] . "\n";
}
?>
