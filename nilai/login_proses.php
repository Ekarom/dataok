<?php
/**
 * Pengolah Login - Sinkronisasi Final dengan siswa.sql
 */

include "../cfg/konek.php";
require_once "../cfg/recaptcha_config.php";
session_start();
ob_start();

$skradm = isset($_POST['skradm']) ? trim($_POST['skradm']) : '';
$passz = isset($_POST['skrpass']) ? trim($_POST['skrpass']) : '';
$database_name = isset($_POST['database_name']) ? trim($_POST['database_name']) : '';

// 1. Validasi Dasar
if (empty($skradm) || empty($passz) || empty($database_name)) {
    header("Location: login.php?salah=9");
    exit();
}

// 2. Pilih Database
if (!mysqli_select_db($sqlconn, $database_name)) {
    header("Location: login.php?salah=2");
    exit();
}

$user_safe = mysqli_real_escape_string($sqlconn, $skradm);
$login_success = false;
$u = null;

// 3. Cek Admin (usera)
$q_admin = mysqli_query($sqlconn, "SELECT *, 'admin' as role FROM usera WHERE (userid = '$user_safe' OR username = '$user_safe') AND status='1'");
if ($q_admin && mysqli_num_rows($q_admin) > 0) {
    $u = mysqli_fetch_assoc($q_admin);
    if (password_verify($passz, $u['password']) || $u['password'] === md5($passz) || $u['password'] === $passz) {
        $login_success = true;
    }
}

// 4. Cek Siswa (Sesuai kolom 'pd', 'nis', 'nisn' di siswa.sql)
if (!$login_success) {
    $q_siswa = mysqli_query($sqlconn, "SELECT *, 'siswa' as role FROM siswa WHERE nis = '$user_safe' OR nisn = '$user_safe'");
    if ($q_siswa && mysqli_num_rows($q_siswa) > 0) {
        $u = mysqli_fetch_assoc($q_siswa);
        // Password siswa adalah NIS atau NISN (sesuai file sql)
        if ($passz === trim($u['nis']) || $passz === trim($u['nisn'])) {
            $login_success = true;
        }
    }
}

// 5. Penanganan Sesi
if ($login_success) {
    session_regenerate_id(true);
    $_SESSION['idu'] = session_id();
    $_SESSION['skradm'] = $skradm;
    $_SESSION['user_role'] = $u['role'];
    $_SESSION['database_asli'] = $database_name;
    
    // Sinkronisasi: Ambil dari kolom 'pd' jika 'nama' tidak ada (khusus siswa)
    $_SESSION['nama'] = $u['nama'] ?? $u['pd'] ?? $skradm;
    
    if($u['role'] === 'siswa') $_SESSION['nis_login'] = $u['nis'];

    header('Location: ./index.php');
    exit();
} else {
    header("Location: login.php?salah=1");
    exit();
}