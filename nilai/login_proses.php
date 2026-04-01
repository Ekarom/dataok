<?php
/**
 * Pengolah Login - Sinkronisasi Final dengan 2FA & reCAPTCHA Bypass
 * Support: reCAPTCHA (Conditional for Admins), Login Attempts & Two-Factor Authentication
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

// 2. Pilih Database (Wajib untuk Identifikasi)
if (!mysqli_select_db($sqlconn, $database_name)) {
    header("Location: login.php?salah=2");
    exit();
}

$user_safe = mysqli_real_escape_string($sqlconn, $skradm);
$u = null;

// 3. IDENTIFIKASI USER (SEBELUM reCAPTCHA - UNTUK BYPASS)
// Cek Admin (usera)
$check_col = mysqli_query($sqlconn, "SHOW COLUMNS FROM usera LIKE 'userid'");
$user_col = ($check_col && mysqli_num_rows($check_col) > 0) ? 'userid' : 'username';
$q_admin = mysqli_query($sqlconn, "SELECT *, 'admin' as role FROM usera WHERE $user_col = '$user_safe' AND status='1'");

if ($q_admin && mysqli_num_rows($q_admin) > 0) {
    $u = mysqli_fetch_assoc($q_admin);
} else {
    // Cek Siswa
    $q_siswa = mysqli_query($sqlconn, "SELECT *, 'siswa' as role FROM siswa WHERE nis = '$user_safe'");
    if ($q_siswa && mysqli_num_rows($q_siswa) > 0) {
        $u = mysqli_fetch_assoc($q_siswa);
    }
}

// User tidak ditemukan? Gagal lebih awal
if (!$u) {
    write_log("LOGIN_FAIL", "User not found: $skradm", $sqlconn);
    // Kita tetap harus lewat tracking attempts (di bawah)
}

// 4. VERIFIKASI reCAPTCHA (KHUSUS ADMIN)
if ($u && $u['role'] === 'admin') {
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
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $response_keys = json_decode($response, true);
    curl_close($ch);

    if (!$response_keys["success"]) {
        $err_codes = isset($response_keys['error-codes']) ? implode(', ', $response_keys['error-codes']) : 'unknown error';
        write_log("LOGIN_FAIL", "reCAPTCHA failed for admin: $skradm ($err_codes)", $sqlconn);
        header("Location: login.php?salah=4");
        exit();
    }
}

// 5. PENGECEKAN PASSWORD (CREDENTIALS)
$login_success = false;
if ($u) {
    if ($u['role'] === 'admin') {
        if (password_verify($passz, $u['password']) || $u['password'] === md5($passz) || $u['password'] === $passz) {
            $login_success = true;
        }
    } else {
        // Student login
        if ($passz === trim($u['nisn'])) {
            $login_success = true;
        }
    }
}

// 6. PENANGANAN SESI & LOGGING
if ($login_success) {
    // Reset failed attempts
    mysqli_query($sqlconn, "DELETE FROM login_attempts WHERE ip_address = '$ip_safe'");
    
    // --- 2FA Check (Only for Admin with keys or email) ---
    if ($u['role'] === 'admin' && (!empty($u['google_secret']) || !empty($u['email']))) {
        $_SESSION['temp_skradm'] = $skradm;
        $_SESSION['temp_user_id_db'] = $u[$user_col];
        $_SESSION['database_asli'] = $database_name;
        $_SESSION['user_role'] = $u['role'];
        
        write_log("LOGIN_STAGING", "2FA challenge initiated for admin $skradm", $sqlconn);
        header("Location: ../verify_2fa.php");
        exit();
    }

    // Direct Login (Success)
    write_log("LOGIN_SUCCESS", "User $skradm logged in as " . $u['role'], $sqlconn);

    session_regenerate_id(true);
    $_SESSION['idu'] = session_id();
    $_SESSION['skradm'] = $skradm;
    $_SESSION['user_role'] = $u['role'];
    $_SESSION['database_asli'] = $database_name;
    $_SESSION['nama'] = $u['nama'] ?? $u['pd'] ?? $skradm;
    
    if($u['role'] === 'siswa') $_SESSION['nis_login'] = $u['nis'];

    header('Location: ./index.php');
    exit();
} else {
    // Fail Logic (Username/Password salah)
    write_log("LOGIN_FAIL", "Invalid credentials for: $skradm", $sqlconn);

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
        $redirect_url = "login.php?salah=3&t=300";
    }
    header("Location: $redirect_url");
    exit();
}


