<?php
$conn = new mysqli("localhost", "root", "");
$result = $conn->query("SHOW DATABASES LIKE 'dnet_ad%'");
if($row = $result->fetch_array()) {
    $db = $row[0];
    $conn->select_db($db);
    echo "DB: $db\n";
    $res = $conn->query("DESCRIBE siswa");
    if($res) {
        while ($r = $res->fetch_assoc()) {
            echo $r['Field'] . " - " . $r['Type'] . "\n";
        }
    } else {
        echo "No 'siswa' table in $db\n";
    }
} else {
    echo "No dnet_ad database found\n";
}
?>
