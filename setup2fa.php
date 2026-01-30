<?php
ob_start();
session_start();
require "cfg/konek.php";
require_once 'cfg/GoogleAuthenticator.php';

// Set Timezone
date_default_timezone_set("Asia/Jakarta");

// Check if user has temp_login session
if (!isset($_SESSION['temp_login'])) {
    header("Location: login.php");
    exit();
}

$temp_login = $_SESSION['temp_login'];
$ga = new GoogleAuthenticator();

// Check if user is actually a new user (is_first_time flag)
if (!$temp_login['is_first_time']) {
    header("Location: verify_2fa.php");
    exit();
}

// Generate QR Code URL
$qr_code_url = $ga->getQRCodeGoogleUrl(
    'Arsip Data - ' . $temp_login['username'],
    $temp_login['google_secret'],
    'Arsip'
);

// Handle direct activation (without code verification)
if (isset($_POST['activate_and_continue'])) {
    $uid_save = $temp_login['id'];
    $secret_save = $temp_login['google_secret'];
    
    // Save to Database
    $stmt_save = mysqli_prepare($sqlconn, "UPDATE usera SET google_secret = ? WHERE id = ?");
    if ($stmt_save) {
        mysqli_stmt_bind_param($stmt_save, "si", $secret_save, $uid_save);
        mysqli_stmt_execute($stmt_save);
        mysqli_stmt_close($stmt_save);
    }
    
    // Set Cookies (30 Hari) - HttpOnly & Secure
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    setcookie('tapel', $temp_login['tapel'], time() + (86400 * 30), "/", "", $is_https, true);
    setcookie('id', $temp_login['id'], time() + (86400 * 30), "/", "", $is_https, true); 
    setcookie('nama', $temp_login['nama'], time() + (86400 * 30), "/", "", $is_https, true);
    setcookie('level', $temp_login['level'], time() + (86400 * 30), "/", "", $is_https, true);
    setcookie('poto', $temp_login['poto'], time() + (86400 * 30), "/", "", $is_https, true);
    setcookie('nik', $temp_login['nik'], time() + (86400 * 30), "/", "", $is_https, true);
    
    // Log successful login
    $stmt_log = mysqli_prepare($sqlconn, "INSERT INTO usera_log (user, nama, waktu, ip, info) VALUES (?, ?, ?, ?, 'login (2FA setup direct)')");
    if ($stmt_log) {
        mysqli_stmt_bind_param($stmt_log, "ssss", $temp_login['username'], $temp_login['nama'], $temp_login['log_time'], $temp_login['ip']);
        mysqli_stmt_execute($stmt_log);
        mysqli_stmt_close($stmt_log);
    }

    // Update User Table (Last Login & IP)
    $stmt_update_user = mysqli_prepare($sqlconn, "UPDATE usera SET lastlogin = ?, ip = ? WHERE id = ?");
    if ($stmt_update_user) {
        mysqli_stmt_bind_param($stmt_update_user, "ssi", $temp_login['log_time'], $temp_login['ip'], $temp_login['id']);
        mysqli_stmt_execute($stmt_update_user);
        mysqli_stmt_close($stmt_update_user);
    }
    
    // Clear temp_login session
    unset($_SESSION['temp_login']);
    
    // Redirect to dashboard
    header("Location: /?");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Setup 2FA - Arsip Data</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="dist/img/icon.png">
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="plugins/css/util.css">
    <link rel="stylesheet" type="text/css" href="plugins/css/main.css">
    <style>
        .container-login100 {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .setup-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            text-align: center;
        }
        .qr-wrapper {
            background: white;
            padding: 15px;
            border-radius: 15px;
            display: inline-block;
            margin: 20px 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .step-pill {
            background: rgba(255, 255, 255, 0.2);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            margin-bottom: 10px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container-login100">
        <div class="wrap-login100 p-l-55 p-r-55 p-t-65 p-b-54">
            <div class="setup-card">
                <span class="step-pill">Setup Keamanan</span>
                <h3 class="m-b-20">Aktifkan 2FA</h3>
                
                <p class="txt1 text-center p-b-20" style="color: rgba(255,255,255,0.8);">
                    Scan QR Code di bawah menggunakan aplikasi <strong>Google Authenticator</strong> untuk mengamankan akun Anda.
                </p>

                <div class="qr-wrapper">
                    <img src="<?php echo $qr_code_url; ?>" alt="QR Code" width="180" height="180">
                </div>

                <div class="m-t-10 m-b-30">
                    <small>Key Manual:</small><br>
                    <code style="color: #ffeb3b; background: rgba(0,0,0,0.3); padding: 5px 10px; border-radius: 5px;"><?php echo chunk_split($temp_login['google_secret'], 4, ' '); ?></code>
                </div>

                <form method="post">
                    <div class="container-login100-form-btn">
                        <button type="submit" name="activate_and_continue" class="login100-form-btn">
                            Lanjutkan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
<?php ob_end_flush(); ?>
