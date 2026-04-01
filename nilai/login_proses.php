<?php
/**
 * Pengolah Login - Portal Nilai Siswa
 * KHUSUS SISWA: Login menggunakan NIS sebagai username dan NISN sebagai password
 * Admin TIDAK dapat login melalui portal ini
 */

// Custom name to prevent clash with main portal
session_name('NILAISESSID');
if (session_status() == PHP_SESSION_NONE) session_start();

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

// 2. Pilih Database
if (!mysqli_select_db($sqlconn, $database_name)) {
    header("Location: login.php?salah=2");
    exit();
}

$user_safe = mysqli_real_escape_string($sqlconn, $skradm);

// 3. Lockout Check
$check_attempt = mysqli_query($sqlconn, "SELECT * FROM login_attempts WHERE ip_address = '$ip_safe'");
if ($check_attempt && mysqli_num_rows($check_attempt) > 0) {
    $attempt_data = mysqli_fetch_assoc($check_attempt);
    if ($attempt_data['attempts'] >= 3) {
        $last = strtotime($attempt_data['last_attempt_time']);
        $lockout = 5 * 60;
        if ((time() - $last) < $lockout) {
            $remaining = $lockout - (time() - $last);
            header("Location: login.php?salah=3&t=$remaining");
            exit();
        } else {
            // Reset jika lockout sudah expired
            mysqli_query($sqlconn, "DELETE FROM login_attempts WHERE ip_address = '$ip_safe'");
        }
    }
}

// 4. IDENTIFIKASI SISWA (HANYA dari tabel siswa - Admin TIDAK diizinkan)
$q_siswa = mysqli_query($sqlconn, "SELECT * FROM siswa WHERE nis = '$user_safe'");
$u = ($q_siswa && mysqli_num_rows($q_siswa) > 0) ? mysqli_fetch_assoc($q_siswa) : null;

// 4.5 VERIFIKASI reCAPTCHA
if ($u) {
    $verify_url = "https://www.google.com/recaptcha/api/siteverify";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $verify_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'secret' => $recaptcha_secret_key,
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

        // --- SOFT-FAIL LOGIC ---
        $is_suspended = (stripos($err_codes, 'suspended') !== false || stripos($err_codes, 'invalid-input-secret') !== false);

        if ($is_suspended) {
            write_log("RECAPTCHA_WARNING", "Service suspended ($err_codes). Allowing bypass for $skradm.", $sqlconn);
        } else {
            write_log("LOGIN_FAIL", "reCAPTCHA failed: $skradm ($err_codes)", $sqlconn);
            header("Location: login.php?salah=4");
            exit();
        }
    }
}

// 5. VERIFIKASI PASSWORD (NISN atau NIS)
$login_success = false;
if ($u) {
    if ($passz === trim($u['nisn']) || $passz === trim($u['nis'])) {
        $login_success = true;
    }
}
// 6. PENANGANAN SESI & LOGGING
if ($login_success) {

    // Reset failed attempts
    mysqli_query($sqlconn, "DELETE FROM login_attempts WHERE ip_address = '$ip_safe'");

    // INITIALIZE SESSION FIRST (so logger picks up $skradm)
    session_regenerate_id(true);
    $_SESSION['idu'] = session_id();
    $_SESSION['skradm'] = $skradm;
    $_SESSION['user_role'] = 'siswa';
    $_SESSION['user_level'] = '3';            // Level 3 = Siswa
    $_SESSION['database_asli'] = $database_name;
    $_SESSION['nama'] = $u['pd'] ?? $skradm;
    $_SESSION['nis_login'] = $u['nis'];
    $_SESSION['siswa_id'] = $u['id'] ?? '';
    $_SESSION['siswa_nama'] = $u['pd'] ?? '';
    $_SESSION['siswa_kelas'] = $u['kelas'] ?? '';
    $_SESSION['siswa_nis'] = $u['nis'] ?? '';
    $_SESSION['siswa_nisn'] = $u['nisn'] ?? '';

    // Success Logging (Now it knows the session owner)
    global $userc, $nama;
    $userc = $skradm;
    $nama = $u['pd'] ?? $skradm;
    write_log("LOGIN_SUCCESS", "Masuk ke Ruang Nilai (Siswa)", $sqlconn);

    header('Location: ./dashboard');
    exit();

} else {
    // Fail Logic
    write_log("LOGIN_FAIL", "Invalid credentials for siswa: $skradm from $ip_address", $sqlconn);

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
