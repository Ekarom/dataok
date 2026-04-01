<?php
include "cfg/konek.php";
$query = mysqli_query($sqlconn, "DESCRIBE profils");
if ($query) {
    while ($row = mysqli_fetch_assoc($query)) {
        echo $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "Error: " . mysqli_error($sqlconn);
}
?>
