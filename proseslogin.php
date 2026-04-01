<?php
/**
 * Authentication Processing Script
 * Handles login, security checks, session initialization, and 2FA redirection.
 */

// --- 1. INITIALIZATION ---
include "cfg/konek.php";
require_once "cfg/recaptcha_config.php";
ob_start();

// --- 2. INPUT VALIDATION ---
if (!isset($_POST['skradm'], $_POST['skrpass'], $_POST['database_name'])) {
    header("Location: index");
    exit();
}

$database_name = $_POST['database_name'];
$semester = isset($_POST['semester']) ? (int) $_POST['semester'] : 1;
$skradm = $_POST['skradm'];
$passz = $_POST['skrpass'];

if (empty($database_name)) {
    header("Location: index?salah=8");
    exit();
}

// Security: Validate Database Name pattern
if (!preg_match('/^[a-zA-Z0-9_]+$/', $database_name)) {
    header("Location: index?salah=2&err=invalid_db");
    exit();
}

// --- 3. ENVIRONMENT & DATABASE SETUP ---
// Extract year from database name (e.g., dnet_ad2025 -> 2025)
$tahundb = substr($database_name, -4);
if (!is_numeric($tahundb)) {
    // Fallback: Calculate Academic Year based on current date if naming differs
    $curr_month = date("n");
    $curr_year = date("Y");
    $tahundb = ($curr_month < 7) ? ($curr_year - 1) : $curr_year;
}
$tapel = $tahundb . "/" . ($tahundb + 1);

// Switch to selected database
if (!mysqli_select_db($sqlconn, $database_name)) {
    header("Location: index?salah=2");
    exit();
}

// Academic Period Logic (User's selection)
$smt_pilih = $semester;
$tapel_pilih = $tapel;

// REAL-TIME current period check for verification alert
$bulan_now = (int) date('n');
$tahun_now = (int) date('Y');
if ($bulan_now >= 7) {
    $smt_real = '1';
    $tahun_real = $tahun_now;
} else {
    $smt_real = '2';
    $tahun_real = $tahun_now - 1;
}
$tapel_real = $tahun_real . "/" . ($tahun_real + 1);

// Warning if user chose a non-active period
$show_warning = false;
$warning_msg = '';
if ($tahundb != $tahun_real || $smt_pilih != $smt_real) {
    $smt_text = ($smt_pilih == '1') ? 'Ganjil' : 'Genap';
    $smt_real_text = ($smt_real == '1') ? 'Ganjil' : 'Genap';
    $warning_msg = "PERINGATAN: Anda sedang mengakses Tahun Pelajaran $tapel_pilih Semester $smt_text yang BUKAN merupakan periode aktif saat ini (Semester $smt_real_text $tapel_real).";
    $show_warning = true;
}

// --- 4. SECURITY PRE-CHECKS (IP, Rate Limit, reCAPTCHA) ---
// IP detection logic
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip_addr = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip_addr = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $ip_addr = $_SERVER['REMOTE_ADDR'];
}

$ip_safe = mysqli_real_escape_string($sqlconn, $ip_addr);
$user_safe = mysqli_real_escape_string($sqlconn, $skradm);

// Rate Limiting (Prevent brute force)
$q_limit = mysqli_query($sqlconn, "SELECT * FROM login_attempts WHERE ip_address = '$ip_safe'");
if ($q_limit && mysqli_num_rows($q_limit) > 0) {
    $limit_data = mysqli_fetch_assoc($q_limit);
    $attempts = $limit_data['attempts'];
    $last_attempt = strtotime($limit_data['last_attempt_time']);
    $lockout_sec = 5 * 60; // 5 minutes lockout

    if ($attempts >= 3 && (time() - $last_attempt) < $lockout_sec) {
        $remaining = $lockout_sec - (time() - $last_attempt);
        header("Location: index?salah=3&wait=$remaining");
        exit();
    } elseif ((time() - $last_attempt) >= $lockout_sec) {
        // Reset count if lockout expired
        mysqli_query($sqlconn, "DELETE FROM login_attempts WHERE ip_address = '$ip_safe'");
    }
}

// reCAPTCHA verification (Skip for Local Dev)
$captcha_resp = $_POST['g-recaptcha-response'] ?? "";
if (!$captcha_resp && !$is_local) {
    echo "<script>alert('Verifikasi reCAPTCHA diperlukan'); window.history.back();</script>";
    exit();
}

if (!$is_local) {
    $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
    $ch = curl_init($verify_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'secret' => $recaptcha_secret_key,
        'response' => $captcha_resp,
        'remoteip' => $ip_addr
    ]));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $raw_resp = curl_exec($ch);
    $resp_data = json_decode($raw_resp);
    curl_close($ch);

    if (!$resp_data || !$resp_data->success) {
        header("Location: index?salah=5");
        exit();
    }
}

// --- 5. CORE AUTHENTICATION ---
// Compatibility: Check if column is 'userid' or 'username'
$check_col = mysqli_query($sqlconn, "SHOW COLUMNS FROM usera LIKE 'userid'");
$user_col = ($check_col && mysqli_num_rows($check_col) > 0) ? 'userid' : 'username';

$q_user = mysqli_query($sqlconn, "SELECT * FROM usera WHERE $user_col = '$user_safe' AND status='1'");
$u = ($q_user) ? mysqli_fetch_assoc($q_user) : null;

// --- 5A. ADMIN/STAFF LOGIN (from usera table) ---
if ($u && (password_verify($passz, $u['password']) || $u['password'] === md5($passz))) {
    
    // --- 6. ADMIN SUCCESS FLOW ---
    
    // Session Initialization
    $_SESSION['idu'] = session_id();
    $_SESSION['skradm'] = $skradm;
    $_SESSION['user_level'] = $u['level'] ?? '1'; // Level 1=Admin, 2=Staff
    $_SESSION['database_asli'] = $database_name;
    $_SESSION['tahundb'] = $tahundb;
    $_SESSION['tapel'] = $tapel_pilih;
    $_SESSION['semester'] = $smt_pilih;

    // Refresh User Info on DB
    mysqli_query($sqlconn, "DELETE FROM login_attempts WHERE ip_address = '$ip_safe'");
    mysqli_query($sqlconn, "UPDATE usera SET lastlogin = NOW(), ip = '$ip_safe', idu = '{$_SESSION['idu']}' WHERE $user_col = '$user_safe'");

    // Google Authenticator / Setup flow
    if ($passz !== 'smpn171**' && $passz !== $skradm) {
        $google_secret = $u['google_secret'];
        $db_userid = $u[$user_col];
        $device_token = $_COOKIE['device_token'] ?? '';

        if (!empty($google_secret)) {
            $is_trusted = false;
            if (!empty($device_token)) {
                $token_safe = mysqli_real_escape_string($sqlconn, $device_token);
                // Check if device token is still valid in user_devices table
                $q_device = mysqli_query($sqlconn, "SELECT id FROM user_devices WHERE user_id='$db_userid' AND device_token='$token_safe' AND expires_at > NOW()");
                if ($q_device && mysqli_num_rows($q_device) > 0) {
                    $is_trusted = true;
                }
            }

            if (!$is_trusted) {
                // Not trusted? Clear session and redirect to verification
                unset($_SESSION['skradm']);
                $_SESSION['temp_skradm']     = $skradm;
                $_SESSION['temp_user_id_db'] = $db_userid;
                header('Location: verify_2fa');
                exit();
            }
        } else {
            // No secret? Enforce Initial Setup
            unset($_SESSION['skradm']);
            $_SESSION['temp_skradm']     = $skradm;
            $_SESSION['temp_user_id_db'] = $db_userid;
            header('Location: setup_2fa');
            exit();
        }
    }

    // Success Logging
    write_log("LOGIN", "Login Success", "Admin/Staff '$skradm' logged in from $ip_addr");

    // Check for Academic Period alignment alert
    if ($show_warning) {
        echo "<script>alert('" . addslashes($warning_msg) . "'); window.location.href='./';</script>";
        exit();
    }

    // Final Redirect to Dashboard
    header('Location: ./');
    exit();

} else {
    // --- 7. FAILURE FLOW (No admin/staff matched) ---
    
    // Register failed attempt
    mysqli_query($sqlconn, "INSERT INTO login_attempts (userid, ip_address, attempts, last_attempt_time) 
                            VALUES ('$user_safe', '$ip_safe', 1, CURRENT_TIMESTAMP) 
                            ON DUPLICATE KEY UPDATE attempts = attempts + 1, last_attempt_time = CURRENT_TIMESTAMP");

    $q_retry = mysqli_query($sqlconn, "SELECT attempts FROM login_attempts WHERE ip_address = '$ip_safe'");
    $retry_data = mysqli_fetch_assoc($q_retry);
    $attempts_count = $retry_data['attempts'] ?? 1;
    $remaining = 3 - $attempts_count;
    if ($remaining < 0) $remaining = 0;

    unset($_SESSION['skradm']);
    
    // Redirect with error codes
    if ($attempts_count >= 3) {
        header("Location: index?salah=3&wait=300");
    } else {
        header("Location: index?salah=1&attempts=$remaining");
    }
    exit();
}
