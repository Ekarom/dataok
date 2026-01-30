<?php
include "cfg/konek.php";
$sql = "ALTER TABLE prestasi MODIFY COLUMN pdf VARCHAR(500) NOT NULL";
if (mysqli_query($sqlconn, $sql)) {
    echo "SUCCESS: Column 'pdf' expanded to 500 characters.";
} else {
    echo "ERROR: " . mysqli_error($sqlconn);
}
?>
