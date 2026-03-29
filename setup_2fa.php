<?php
session_start();
// Robust Database Connection Include
$konek_path = dirname(__FILE__) . "/cfg/konek.php";
if (!file_exists($konek_path)) {
    $konek_path = dirname(__FILE__) . "/cfg/konek.php";
}
if (file_exists($konek_path)) {
    include $konek_path;
} else {
    include "cfg/konek.php"; 
}
// Ensure dependencies exist
if (file_exists("lib/TOTP.php")) {
    require_once "lib/TOTP.php";
} elseif (file_exists("cfg/GoogleAuthenticator.php")) {
    require_once "cfg/GoogleAuthenticator.php";
} else {
    // Fatal error or handling
    die("Error: lib/TOTP.php not found.");
}
if (!isset($_SESSION['temp_skradm'])) {
    header("Location: ./");
    exit();
}
$user_id = $_SESSION['temp_user_id_db'];
$skradm = $_SESSION['temp_skradm'];
$error = "";

// Fetch User Info (to ensure name is available for logging)
$skradm_safe = mysqli_real_escape_string($sqlconn, $skradm);

// Check if 'userid' column exists (Consistency with proses./)
$check_col = mysqli_query($sqlconn, "SHOW COLUMNS FROM usera LIKE 'userid'");
$user_col = ($check_col && mysqli_num_rows($check_col) > 0) ? 'userid' : 'username';

$q_u = mysqli_query($sqlconn, "SELECT nama FROM usera WHERE $user_col='$skradm_safe'");
$u_data = mysqli_fetch_assoc($q_u);
$nama = $u_data['nama'] ?? $skradm;
// Generate new secret if not already in session
if (!isset($_SESSION['new_secret'])) {
    if (class_exists('TOTP')) {
        $_SESSION['new_secret'] = TOTP::generateSecret();
    } elseif (class_exists('GoogleAuthenticator')) {
         $ga = new GoogleAuthenticator();
         $_SESSION['new_secret'] = $ga->createSecret();
    } else {
        die("Error: TOP/Authenticator class not found.");
    }
}
$secret = $_SESSION['new_secret'];

// Logika Lewati Setup (Bisa via POST button atau GET link)
if (isset($_POST['skip_setup']) || isset($_GET['skip'])) {
    unset($_SESSION['new_secret']);
    unset($_SESSION['temp_skradm']);
    unset($_SESSION['temp_user_id_db']);
    $_SESSION['skradm'] = $skradm;
    // --- LOG SKIP 2FA ---
    $userc = $skradm;
    $nama = isset($nama) ? $nama : $skradm; 
    write_log("LOGIN", "Login");
    // --------------------
    header("Location: index.php");
    exit();
}

if (isset($_POST['verify_setup'])) {
    $code = $_POST['code'];
    $valid = false;
    
    if (class_exists('TOTP')) {
        if (TOTP::verifyCode($secret, $code, 2)) $valid = true;
    } elseif (class_exists('GoogleAuthenticator')) {
        $ga = new GoogleAuthenticator();
        if ($ga->verifyCode($secret, $code, 2)) $valid = true;
    }
    if ($valid) {
        // Save secret to database
        $secret_escaped = mysqli_real_escape_string($sqlconn, $secret);
        $setqr = mysqli_query($sqlconn, "UPDATE usera SET google_secret='$secret_escaped' WHERE $user_col='$skradm_safe'"); 
        if (!$setqr) {
             $error = "Database Error: " . mysqli_error($sqlconn);
        } else {
            unset($_SESSION['new_secret']);
            unset($_SESSION['temp_skradm']);
            unset($_SESSION['temp_user_id_db']);
            $_SESSION['skradm'] = $skradm;
            // --- LOG SUCCESS 2FA SETUP ---
            $userc = $skradm;
            $nama = isset($nama) ? $nama : $skradm;
            write_log("LOGIN", "User Logged In (2FA Enabled)");
            // -----------------------------
            header("Location: index.php");
            exit();
        }
    } else {
        $error = "Kode verifikasi salah. Silakan coba lagi.";
    }
}
// Generate QR Code URL (OTP Auth)
$issuer = "SistemArsip";
$label = $skradm;
$otpauth = "otpauth://totp/$issuer:$label?secret=$secret&issuer=$issuer";
// Determine QR Image Source
// Use public API as reliable fallback if local script is missing
$qr_image_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($otpauth);
// If local generator exists, use it (faster/private)
// UPDATE: Local generator broken (missing library), forced public API
/* 
if (file_exists("qrcode_gen.php")) {
    $qr_image_url = "qrcode_gen.php?data=" . urlencode($otpauth);
}
*/
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Setup Google Authenticator</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="images/logodik2.png" />
    <link rel="stylesheet" type="text/css" href="plugins/fontawesome-free/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="plugins/css/util.css">
    <link rel="stylesheet" type="text/css" href="plugins/css/main.css">
    <style>
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-danger {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }
        .input100 {
            background-color: rgba(0, 0, 0, 0.3) !important;
            border-radius: 5px;
            padding: 10px 15px 10px 38px !important;
        }
        .wrap-input100 {
            border-bottom: 2px solid rgba(255, 255, 255, 0.5) !important;
        }
    </style>
</head>
<body>
    <div class="limiter">
        <div class="container-login100" style="background-image: url('images/<?php echo isset($skback) ? $skback : 'bg_default.jpg'; ?>');">
            <div class="wrap-login100">
                <form class="login100-form validate-form" method="post">
                    <span class="login100-form-logo">
                        <i class="zmdi landscape"><img src="images/<?php echo isset($sklogo) ? $sklogo : 'logo_default.png'; ?>" width="120"
                                height="110" /></i>
                    </span>
                    <span class="login100-form-title p-b-20 p-t-27">
                        Setup Google Authenticator
                    </span>
                    <div class="text-center p-b-20" style="color: white !important;">
                        <p style="color: white !important;">Silakan scan QR Code di bawah ini dengan aplikasi Google Authenticator Anda.</p>
                        <br>
                        
                        <!-- QR CODE DISPLAY -->
                        <img src="<?php echo $qr_image_url; ?>" alt="QR Code" style="background: white; padding: 10px; border-radius: 5px; width:200px; height:200px;" />
                        
                        <br><br>
                        <p style="color: white !important;">Atau masukkan kode manual: <strong style="color: white !important;"><?php echo $secret; ?></strong></p>
                        <br>
                        <p style="color: white !important;">Belum punya aplikasinya?</p>
                        <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target="_blank">
                            <img src="https://play.google.com/intl/en_us/badges/static/images/badges/en_badge_web_generic.png" alt="Get it on Google Play" height="60" style="margin-top: 10px;">
                        </a>
                    </div>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <div class="wrap-input100 validate-input" data-validate="Masukan Kode">
                        <input class="input100" type="text" name="code" placeholder="Masukan Kode 6 Digit" autocomplete="off">
                        <span class="focus-input100" data-placeholder="G"></span>
                    </div>
                    <div class="container-login100-form-btn">
                        <button class="login100-form-btn" name="verify_setup">
                            Verifikasi & Simpan
                        </button>
                    </div>

                    <div class="text-center p-t-20">
                        <a href="setup_2fa.php?skip=1" class="txt2" style="text-decoration: none; color: rgba(255,255,255,0.7);">
                            <i class="fa fa-arrow-right m-r-5"></i> Lewati saat ini
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="js/jquery-3.2.1.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
