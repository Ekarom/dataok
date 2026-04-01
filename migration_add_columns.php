<?php
include "cfg/konek.php";

echo "Starting migration...\n";

// List of columns to add
$personalColumns = [
    "tempat_lahir VARCHAR(100)",
    "tgl_lahir DATE",
    "nik VARCHAR(20)",
    "agama VARCHAR(20)",
    "alamat TEXT",
    "no_hp VARCHAR(20)",
    "email VARCHAR(100)",
    "nama_ayah VARCHAR(100)",
    "nama_ibu VARCHAR(100)"
];

$gradeColumns = [];
$subjects = ['pkn', 'ind', 'mtk', 'ipa', 'ips', 'eng'];
for ($s = 1; $s <= 5; $s++) {
    foreach ($subjects as $sub) {
        $gradeColumns[] = "{$sub}_{$s} DOUBLE DEFAULT 0";
    }
}

$allNewColumns = array_merge($personalColumns, $gradeColumns);

foreach ($allNewColumns as $colDef) {
    preg_match('/^(\w+)/', $colDef, $matches);
    $colName = $matches[1];
    
    // Check if column exists
    $check = mysqli_query($sqlconn, "SHOW COLUMNS FROM siswa LIKE '$colName'");
    if (mysqli_num_rows($check) == 0) {
        $sql = "ALTER TABLE siswa ADD COLUMN $colDef";
        if (mysqli_query($sqlconn, $sql)) {
            echo "Added column: $colName\n";
        } else {
            echo "Error adding $colName: " . mysqli_error($sqlconn) . "\n";
        }
    } else {
        echo "Column $colName already exists. Skipping.\n";
    }
}

echo "Migration completed.\n";
?>
