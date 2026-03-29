<?php

define('DB_HOST', 'localhost');

define('DB_USER', 'root');

define('DB_PASS', '');

define('DB_NAME', 'dnet2025');

define('SITE_NAME', 'data');



// Koneksi Global

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {

    die('Koneksi Gagal: ' . mysqli_connect_error());

}

?>
