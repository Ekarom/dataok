<?php

// FILE KONEKSI DATABASE

// File ini akan diperbarui otomatis oleh installer (instal.php)

// Jangan ubah manual kecuali Anda tahu apa yang Anda lakukan



$db_host = "localhost";

$db_user = "databaseusername";

$db_pass = "databasepassword";

$database = "db_dnet"; // Default if session not set



if (session_status() == PHP_SESSION_NONE) {

    session_start();

}

if (isset($_SESSION['database_asli']) && !empty($_SESSION['database_asli'])) {

    $database = $_SESSION['database_asli'];

}



// 1. Cek apakah konfigurasi masih kosong

if (empty($db_name)) {

    die("Error: Nama database belum disetting.");

}



// 2. Koneksi menggunakan MySQLi (Object Oriented)

// 2. Koneksi menggunakan MySQLi (Object Oriented)

// Gunakan @ untuk suppress warning

$sqlconn = @new mysqli($db_host, $db_user, $db_pass, $database);

$db_selected = true;



// Cek error koneksi awal

if ($sqlconn->connect_errno) {

    // Jika error karena database tidak diketahui (Code 1049)

    if ($sqlconn->connect_errno == 1049) {

        // 1. Bersihkan Session

        if (session_status() != PHP_SESSION_NONE) {

            unset($_SESSION['database_asli']);

            setcookie('database_asli', '', time() - 3600, "/"); 

        }



        // 2. Konek ke server tanpa database

        $sqlconn = @new mysqli($db_host, $db_user, $db_pass);

        $db_selected = false;

        

        if (!$sqlconn->connect_errno) {

             // 3. Cari database yang valid

             // Prioritas: 

             // 1. Database Default Config ($db_name awal) - biasanya dnet_ad2025

             // 2. pnet_pd (format baru)

             // 3. dnet (format lama)

             

             $found_db = "";

             $default_db_config = "db_dnet"; // Hardcoded default from top of file



             // Cek Default DB

             $res = $sqlconn->query("SHOW DATABASES LIKE '$default_db_config'");

             if ($res && $res->num_rows > 0) {

                 $found_db = $default_db_config;

             }



             // Coba pnet_pd jika belum

             if (empty($found_db)) {

                 $res = $sqlconn->query("SHOW DATABASES LIKE 'pnet_pd%'");

                 if ($res && $res->num_rows > 0) {

                     $row = $res->fetch_array();

                     $found_db = $row[0];

                 }

             }

             

             // Coba dnet jika belum

             if (empty($found_db)) {

                 $res = $sqlconn->query("SHOW DATABASES LIKE 'dnet%'");

                 if ($res && $res->num_rows > 0) {

                     // Hindari mengambil dnet_ad2025 lagi jika sudah dicek di atas (tapi query LIKE dnet% akan menangkapnya)

                     // Ambil yang pertama

                     $row = $res->fetch_array();

                     $found_db = $row[0];

                 }

             }



             // 4. Jika ketemu, select database tersebut

             if (!empty($found_db)) {

                 if ($sqlconn->select_db($found_db)) {

                     $db_name = $found_db;

                     $db_selected = true;

                     // Opsional: Simpan ke session agar refresh aman

                     if (session_status() != PHP_SESSION_NONE) {

                        $_SESSION['database_asli'] = $db_name;

                     }

                 }

             }

        }

    }

}



// 3. Cek Error Koneksi Database

if ($sqlconn->connect_errno) {

    die("Koneksi Database Gagal: " . $sqlconn->connect_error . " (Cek file konek.php)");

}



// 4. Set charset

if (!mysqli_set_charset($sqlconn, "utf8mb4")) {

    die("Error setting charset utf8mb4: " . mysqli_error($sqlconn));

}



// 5. Mengambil data profil sekolah (HANYA JIKA DB TERPILIH)

$sklogo = ""; 

$skback = ""; 

$namasek = "";



if ($db_selected) {

    $sql = mysqli_query($sqlconn, "SELECT * FROM profils LIMIT 1");

    if ($sql) {

        if (mysqli_num_rows($sql) > 0) {

            $xadm = mysqli_fetch_array($sql);

            if ($xadm) {

                $sklogo = isset($xadm['logo_sekolah']) ? $xadm['logo_sekolah'] : "";

                $skback = isset($xadm['background_login']) ? $xadm['background_login'] : "";

                $namasek = isset($xadm['nsekolah']) ? $xadm['nsekolah'] : "";

            }

        }

        mysqli_free_result($sql);

    }

}



// 6. Mengambil data konfigurasi tahun aktif dari tabel dbset (HANYA JIKA DB TERPILIH)

$db_tahun = "";

$db_aktif = "";



if ($db_selected) {

    $sqldb = mysqli_query($sqlconn, "SELECT * FROM dbset LIMIT 1");

    if ($sqldb) {

        if (mysqli_num_rows($sqldb) > 0) {

            $xdb = mysqli_fetch_array($sqldb);

            $db_tahun = isset($xdb['tahun']) ? $xdb['tahun'] : "";

            $db_aktif = isset($xdb['aktif']) ? $xdb['aktif'] : "";

        }

    }

}



// 7. Expose nama database untuk ditampilkan di UI

$dbname = $db_name;

?>

