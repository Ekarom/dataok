<?php
/**
 * Pengolah Login - Sinkronisasi Final dengan siswa.sql
 * Support: reCAPTCHA (cURL) & Login Attempts Tracking
 */

include "../cfg/konek.php";
include "../cfg/logger.php";
require_once "../cfg/recaptcha_config.php";
ob_start();

$skradm = isset($_POST['skradm']) ? trim($_POST['skradm']) : '';
$passz = isset($_POST['skrpass']) ? trim($_POST['skrpass']) : '';
$database_name = isset($_POST['database_name']) ? trim($_POST['database_name']) : '';
$captcha = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';

// 0. Deteksi IP untuk Lockout
$ip_address = $_SERVER['REMOTE_ADDR'];
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip_address = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
}
$ip_safe = mysqli_real_escape_string($sqlconn, $ip_address);

// 1. Validasi Dasar
if (empty($skradm) || empty($passz) || empty($database_name)) {
    header("Location: login.php?salah=9");
    exit();
}

// 2. Verifikasi reCAPTCHA via cURL
$verify_url = "https://www.google.com/recaptcha/api/siteverify";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $verify_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'secret'   => $recaptcha_secret_key,
    'response' => $captcha,
    'remoteip' => $ip_address
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Ignore SSL errors for local development
$response = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

$response_keys = json_decode($response, true);

if (!$response_keys["success"]) {
    write_log("LOGIN_FAIL", "reCAPTCHA validation failed for user: $skradm", $sqlconn);
    header("Location: login.php?salah=4"); // 4: reCAPTCHA gagal
    exit();
}

// 3. Pilih Database
if (!mysqli_select_db($sqlconn, $database_name)) {
    header("Location: login.php?salah=2");
    exit();
}

$user_safe = mysqli_real_escape_string($sqlconn, $skradm);
$login_success = false;
$u = null;

// 4. Cek Admin (usera)
$check_col = mysqli_query($sqlconn, "SHOW COLUMNS FROM usera LIKE 'userid'");
$user_col = ($check_col && mysqli_num_rows($check_col) > 0) ? 'userid' : 'username';

$q_admin = mysqli_query($sqlconn, "SELECT *, 'admin' as role FROM usera WHERE $user_col = '$user_safe' AND status='1'");
if ($q_admin && mysqli_num_rows($q_admin) > 0) {
    $u = mysqli_fetch_assoc($q_admin);
    if (password_verify($passz, $u['password']) || $u['password'] === md5($passz) || $u['password'] === $passz) {
        $login_success = true;
    }
}

// 5. Cek Siswa (NIS sebagai User, NISN sebagai Password)
if (!$login_success) {
    $q_siswa = mysqli_query($sqlconn, "SELECT *, 'siswa' as role FROM siswa WHERE nis = '$user_safe'");
    if ($q_siswa && mysqli_num_rows($q_siswa) > 0) {
        $u = mysqli_fetch_assoc($q_siswa);
        // Password siswa adalah NISN
        if ($passz === trim($u['nisn'])) {
            $login_success = true;
        }
    }
}

// 6. Penanganan Login & Tracking Attempts
if ($login_success) {
    // Reset failed attempts on success
    mysqli_query($sqlconn, "DELETE FROM login_attempts WHERE ip_address = '$ip_safe'");
    
    write_log("LOGIN_SUCCESS", "User $skradm logged in as " . ($u['role'] ?? 'unknown'), $sqlconn);

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
    // Log failed attempt
    write_log("LOGIN_FAIL", "Invalid credentials for user: $skradm", $sqlconn);

    $check_attempt = mysqli_query($sqlconn, "SELECT * FROM login_attempts WHERE ip_address = '$ip_safe'");
    if ($check_attempt && mysqli_num_rows($check_attempt) > 0) {
        $attempt_data = mysqli_fetch_assoc($check_attempt);
        $new_count = $attempt_data['attempts'] + 1;
        mysqli_query($sqlconn, "UPDATE login_attempts SET attempts = '$new_count', last_attempt_time = NOW(), userid = '$user_safe' WHERE ip_address = '$ip_safe'");
        $remaining = 3 - $new_count;
    } else {
        mysqli_query($sqlconn, "INSERT INTO login_attempts (ip_address, attempts, last_attempt_time, userid) VALUES ('$ip_safe', 1, NOW(), '$user_safe')");
        $remaining = 2;
    }

    $redirect_url = "login.php?salah=1&attempts=" . ($remaining > 0 ? $remaining : 0);
    if ($remaining <= 0) {
        $redirect_url = "login.php?salah=3&t=300"; // Block for 5 minutes (300 sec)
    }
    header("Location: $redirect_url");
    exit();
}
