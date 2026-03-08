<?php
// PASTIKAN session_start() sudah terpanggil dalam cfg/konek.php atau panggil di sini jika perlu
include "cfg/konek.php";
require_once "cfg/recaptcha_config.php";
ob_start();

// Check for required POST data
if (isset($_POST['skradm'], $_POST['skrpass'], $_POST['database_name'])) {

    $database_name = $_POST['database_name'];
    $semester = isset($_POST['semester']) ? (int)$_POST['semester'] : 1;
    
    if (empty($database_name)) {
        header("Location: login.php?salah=8");
        exit();
    }

    // Validate Database Name (Security)
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $database_name)) {
        header("Location: login.php?salah=2&err=invalid_db");
        exit();
    }

    $skradm = $_POST['skradm'];
    $userz = mysqli_real_escape_string($sqlconn, $skradm);
    $passz = $_POST['skrpass']; 

    // Extract year from database name (e.g., dnet_ad2025 -> 2025)
    $tahundb = substr($database_name, -4);
    if (is_numeric($tahundb)) {
        $tapel = $tahundb . "/" . ($tahundb + 1);
    } else {
        // For dnet_ad (non-numeric suffix), calculate Academic Year based on current month
        $curr_month = date("n");
        $curr_year = date("Y");
        $tahundb = ($curr_month < 7) ? ($curr_year - 1) : $curr_year;
        $tapel = $tahundb . "/" . ($tahundb + 1);
    }

    // Switch to the selected database
    if (mysqli_select_db($sqlconn, $database_name)) {
        // --- SYNC DBSET (Follow User Selection) ---
        // DISABLED: Jangan ubah status global di database agar tidak mempengaruhi user lain
        // @mysqli_query($sqlconn, "UPDATE dbset SET aktif='0'");
        // @mysqli_query($sqlconn, "UPDATE dbset SET aktif='1' WHERE dbname='$database_name'");

        // User's selection from login form
        $smt_pilih = $semester;
        $tahun_pilih = $tahundb;
        $tapel_pilih = $tapel;
        
        // Calculate REAL-TIME current period for comparison
        $bulan_sekarang = (int)date('n');
        $tahun_sekarang = (int)date('Y');
        if ($bulan_sekarang >= 7) {
            $smt_real = '1';
            $tahun_real = $tahun_sekarang;
        } else {
            $smt_real = '2';
            $tahun_real = $tahun_sekarang - 1;
        }
        $tapel_real = $tahun_real . "/" . ($tahun_real + 1);
        
        // Activate user's selection in database (FOLLOW USER CHOICE)
        // DISABLED: Jangan ubah status global tapel di database
        /*
        mysqli_query($sqlconn, "UPDATE tapel SET aktif='0'");
        
        $stmt_active = mysqli_prepare($sqlconn, "UPDATE tapel SET aktif='1' WHERE tapel=? AND smt=?");
        if ($stmt_active) {
            mysqli_stmt_bind_param($stmt_active, "ss", $tapel_pilih, $smt_pilih);
            mysqli_stmt_execute($stmt_active);
            mysqli_stmt_close($stmt_active);
        }
        */
        
        // Set session to user's selection (NOT real-time)
        $_SESSION['tapel'] = $tapel_pilih;
        $_SESSION['semester'] = $smt_pilih;
        
        // Check if user chose a different period than real-time
        $show_warning_alert = false;
        $warning_message = '';
        
        if ($tahun_pilih != $tahun_real || $smt_pilih != $smt_real) {
            $smt_text = ($smt_pilih == '1') ? 'Ganjil' : 'Genap';
            $smt_real_text = ($smt_real == '1') ? 'Ganjil' : 'Genap';
            $warning_message = "PERINGATAN: Anda sedang mengakses Tahun Pelajaran $tapel_pilih Semester $smt_text yang BUKAN merupakan periode aktif saat ini (Semester $smt_real_text $tapel_real).";
            $show_warning_alert = true;
        }
        
    } else {
        header("Location: login.php?salah=2");
        exit();
    }

    // Set Session variables
    $_SESSION['database_asli'] = $database_name;
    $_SESSION['skradm'] = $skradm;
    $_SESSION['tahundb'] = $tahundb;

    // IP Address detection
    $ip_address = $_SERVER['REMOTE_ADDR'];
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip_address = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    $ip_address = mysqli_real_escape_string($sqlconn, $ip_address);

    // Rate Limiting Check
    $check_table = mysqli_query($sqlconn, "SHOW TABLES LIKE 'login_attempts'");
    if ($check_table && mysqli_num_rows($check_table) > 0) {
        $check_limit = mysqli_query($sqlconn, "SELECT * FROM login_attempts WHERE ip_address = '$ip_address'");
        if ($check_limit && mysqli_num_rows($check_limit) > 0) {
            $limit_data = mysqli_fetch_assoc($check_limit);
            $attempts = $limit_data['attempts'];
            $last_attempt = strtotime($limit_data['last_attempt_time']);
            $lockout_time = 5 * 60;

            if ($attempts >= 3 && (time() - $last_attempt) < $lockout_time) {
                $remaining = $lockout_time - (time() - $last_attempt);
                header("Location: login.php?salah=3&wait=$remaining");
                exit();
            } elseif ((time() - $last_attempt) >= $lockout_time) {
                mysqli_query($sqlconn, "DELETE FROM login_attempts WHERE ip_address = '$ip_address'");
            }
        }
    }

    // reCAPTCHA verification
    $captcha = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : "";

    if (!$captcha && !$is_local) {
        echo "<script>alert('Verifikasi reCAPTCHA diperlukan'); window.history.back();</script>";
        exit;
    }
    
    $recaptcha_success = true;
    if (!$is_local) {
        $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
        $params = [
            'secret' => $recaptcha_secret_key,
            'response' => $captcha,
            'remoteip' => $ip_address
        ];
        
        $ch = curl_init($verifyUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if ($response === false || $http_code !== 200) {
            $recaptcha_success = false;
        } else {
             $responseData = json_decode($response);
             $recaptcha_success = $responseData->success;
        }
        curl_close($ch);
    }

    if ($recaptcha_success) {
        // Check if 'userid' column exists
        $check_col = mysqli_query($sqlconn, "SHOW COLUMNS FROM usera LIKE 'userid'");
        $user_col = ($check_col && mysqli_num_rows($check_col) > 0) ? 'userid' : 'username';

        $q_user = mysqli_query($sqlconn, "SELECT * FROM usera WHERE $user_col = '$userz' AND status='1'");
        $row_user = ($q_user) ? mysqli_fetch_assoc($q_user) : null;

        if ($row_user && (password_verify($passz, $row_user['password']) || $row_user['password'] === md5($passz))) {
            // Login Success
            $_SESSION['idu'] = session_id();
            mysqli_query($sqlconn, "DELETE FROM login_attempts WHERE ip_address = '$ip_address'");
            mysqli_query($sqlconn, "UPDATE usera SET lastlogin = NOW(), ip = '$ip_address', idu = '{$_SESSION['idu']}' WHERE $user_col = '$userz'");

            // 2FA / Setup check
            if ($passz !== 'smpn171**' && $passz !== $skradm) {
                $google_secret = $row_user['google_secret'];
                $user_id = $row_user[$user_col];
                $device_token = isset($_COOKIE['device_token']) ? $_COOKIE['device_token'] : '';

                if (!empty($google_secret)) {
                    $is_trusted = false;
                    if (!empty($device_token)) {
                        // Check if user_devices table exists first to avoid SQL errors
                        $check_table_dev = mysqli_query($sqlconn, "SHOW TABLES LIKE 'user_devices'");
                        if ($check_table_dev && mysqli_num_rows($check_table_dev) > 0) {
                            $token = mysqli_real_escape_string($sqlconn, $device_token);
                            $check_device = mysqli_query($sqlconn, "SELECT * FROM user_devices WHERE user_id='$user_id' AND device_token='$token' AND expires_at > NOW()");
                            if ($check_device && mysqli_num_rows($check_device) > 0) $is_trusted = true;
                        }
                    }

                    if (!$is_trusted) {
                        unset($_SESSION['skradm']);
                        $_SESSION['temp_skradm'] = $skradm;
                        $_SESSION['temp_user_id_db'] = $user_id;
                        header('Location: verify_2fa.php');
                        exit();
                    }
                } else {
                    unset($_SESSION['skradm']);
                    $_SESSION['temp_skradm'] = $skradm;
                    $_SESSION['temp_user_id_db'] = $user_id;
                    header('Location: setup_2fa.php');
                    exit();
                }
            }

            // Log login
            $userc = $userz;
            $nama = $row_user['nama'];
            write_log("LOGIN", "Login", "User Logged In");

            // Show warning alert if user chose non-current period
            if ($show_warning_alert) {
                echo "<script>alert('" . addslashes($warning_message) . "'); window.location.href='./?';</script>";
                exit();
            }

            // REDIRECT TO DASHBOARD
            header('Location: ./?');
            exit();

        } else {
            mysqli_query($sqlconn, "INSERT INTO login_attempts (userid, ip_address, attempts, last_attempt_time) 
                                    VALUES ('$userz', '$ip_address', 1, CURRENT_TIMESTAMP) 
                                    ON DUPLICATE KEY UPDATE attempts = attempts + 1, last_attempt_time = CURRENT_TIMESTAMP");
            
            $q_attempts = mysqli_query($sqlconn, "SELECT attempts FROM login_attempts WHERE ip_address = '$ip_address'");
            $data_attempts = mysqli_fetch_assoc($q_attempts);
            $remaining = 3 - $data_attempts['attempts'];
            if ($remaining < 0) $remaining = 0;

            unset($_SESSION['skradm']);
            header("Location: login.php?salah=1&attempts=$remaining");
            exit();
        }
    } else {
        header("Location: login.php?salah=5");
        exit();
    }

} else {
    header("Location: login.php");
    exit();
}
?>