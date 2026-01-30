<?php
// 1. Connect ke database
$conn = mysqli_connect("localhost", "p171_arsip171", "smpn171*" , "test");
// 2. Pilih database
$namadb = $_COOKIE['set'];
date_default_timezone_set("Asia/Jakarta");
mysqli_select_db($conn, $namadb);
$mode = "lokal"; // pilih 'lokal' atau 'pusat'
$tapel = $_COOKIE['sktahun'];
?>
