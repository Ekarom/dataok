<?php
session_start();

// Include konek.php
require_once "cfg/konek.php";

// CHECK Dependencies
if (!file_exists("lib/TOTP.php")) {
    die("Error: Library TOTP tidak ditemukan.");
}
require_once "lib/TOTP.php";
require_once "send_email_helper.php";

// Redirect if session not set
if (!isset($_SESSION['temp_skradm'])) {
    header("Location: login");
    exit();
}

$error = "";
$success = "";
$user_id = isset($_SESSION['temp_user_id_db']) ? $_SESSION['temp_user_id_db'] : '';
$skradm = $_SESSION['temp_skradm'];

// SECURITY: Escape inputs before query
$skradm_safe = mysqli_real_escape_string($sqlconn, $skradm);

// Check if 'userid' column exists (Consistency with proseslogin.php)
$check_col = mysqli_query($sqlconn, "SHOW COLUMNS FROM usera LIKE 'userid'");
$user_col = ($check_col && mysqli_num_rows($check_col) > 0) ? 'userid' : 'username';

$q = mysqli_query($sqlconn, "SELECT * FROM usera WHERE $user_col='$skradm_safe'");
$user = mysqli_fetch_assoc($q);

// Handle case where user not found
if (!$user) {
    session_destroy();
    header("Location: login");
    exit();
}

$google_secret = isset($user['google_secret']) ? $user['google_secret'] : '';
// Ensure user_id is taken securely from the database result
$user_id = $user[$user_col];

if (isset($_POST['verify_code'])) {
    $code = trim($_POST['code']); // Trim whitespace
    $is_valid = false;

    // 1. Try TOTP
    if (!empty($google_secret) && class_exists('TOTP') && TOTP::verifyCode($google_secret, $code, 2)) {
        $is_valid = true;
    }
    // 2. Try Email Code
    elseif (isset($user['email_code']) && $code == $user['email_code'] && strtotime($user['email_code_expired']) > time()) {
        $is_valid = true;
        // Clear email code
        mysqli_query($sqlconn, "UPDATE usera SET email_code=NULL, email_code_expired=NULL WHERE $user_col='$user_id'");
    }

    if ($is_valid) {
        // Set actual session
        $_SESSION['skradm'] = $skradm;

        // Restore session vars if needed
        $_SESSION['database_asli'] = isset($_SESSION['database_asli']) ? $_SESSION['database_asli'] : '';

        // Clear temp session
        unset($_SESSION['temp_skradm']);
        unset($_SESSION['temp_user_id_db']);

        // Remember device
        if (isset($_POST['remember_device'])) {
            try {
                $token = bin2hex(random_bytes(32));
            } catch (Exception $e) {
                $token = bin2hex(openssl_random_pseudo_bytes(32));
            }
            $expires = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60)); // 30 days

            // Checking and creating table if not exists (Auto-Fix)
            mysqli_query($sqlconn, "CREATE TABLE IF NOT EXISTS user_devices (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id VARCHAR(50) NOT NULL,
                device_token VARCHAR(255) NOT NULL,
                expires_at DATETIME NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (user_id),
                INDEX (device_token)
            )");

            $token_safe = mysqli_real_escape_string($sqlconn, $token);
            $user_id_safe = mysqli_real_escape_string($sqlconn, $user_id);
            mysqli_query($sqlconn, "INSERT INTO user_devices (user_id, device_token, expires_at) VALUES ('$user_id_safe', '$token_safe', '$expires')");

            setcookie('device_token', $token, time() + (30 * 24 * 60 * 60), "/", "", false, true); // HttpOnly
        }

        // --- LOG SUCCESSFUL LOGIN ---
        // Log login
        $userc = $userz;
        $nama = $row_user['nama'];
        write_log("LOGIN", "Login", "User Logged In");

        // --------------------------------

        header("Location: ./dashboard");
        exit();
    } else {
        $error = "Kode salah atau kadaluarsa.";
    }
}

if (isset($_POST['send_email'])) {
    if (!empty($user['email'])) {
        $code = rand(100000, 999999);
        $expired = date('Y-m-d H:i:s', time() + (15 * 60)); // 15 minutes

        mysqli_query($sqlconn, "UPDATE usera SET email_code='$code', email_code_expired='$expired' WHERE $user_col='$user_id'");

        if (function_exists('sendEmailCode')) {
            $send = sendEmailCode($user['email'], $code, $sqlconn);
            if ($send === true) {
                $success = "Kode verifikasi telah dikirim ke email Anda (" . substr($user['email'], 0, 3) . "***" . substr($user['email'], strpos($user['email'], '@')) . ").";
            } else {
                $error = "Gagal mengirim email: " . $send;
            }
        } else {
            $error = "Fungsi pengiriman email tidak tersedia.";
        }
    } else {
        $error = "Email tidak terdaftar pada akun ini.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Verifikasi 2FA</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="images/<?php echo $sklogo; ?>" />
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

        .alert-success {
            color: #3c763d;
            background-color: #dff0d8;
            border-color: #d6e9c6;
        }

        /* Custom checkbox styling to ensure visibility */
        .contact100-form-checkbox .input-checkbox100 {
            display: inline-block !important;
            width: 18px;
            height: 18px;
            margin-right: 8px;
            cursor: pointer;
            vertical-align: middle;
            position: relative;
            top: -1px;
        }

        .contact100-form-checkbox .label-checkbox100 {
            color: white !important;
            cursor: pointer;
            display: inline;
            padding-left: 0 !important;
        }

        .contact100-form-checkbox .label-checkbox100::before {
            display: none !important;
        }

        /* Make input box more visible with darker background */
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
        <div class="container-login100"
            style="background-image: url('images/<?php echo isset($skback) ? $skback : 'logo_default.png'; ?>');">
            <div class="wrap-login100">
                <form class="login100-form validate-form" method="post">
                    <span class="login100-form-logo">
                        <i class="zmdi landscape"><img
                                src="images/<?php echo isset($sklogo) ? $sklogo : 'logo_default.png'; ?>" width="120"
                                height="110" /></i>
                    </span>
                    <span class="login100-form-title p-b-20 p-t-27">
                        Verifikasi Keamanan
                    </span>
                    <div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>

                        <div class="wrap-input100 validate-input" data-validate="Masukan Kode">
                            <input class="input100" type="text" name="code" placeholder="Kode Authenticator / Email"
                                autocomplete="off" autofocus>
                            <span class="focus-input100" data-placeholder="G"></span>
                        </div>

                        <div class="contact100-form-checkbox" style="padding-top: 10px; padding-bottom: 20px;">
                            <input class="input-checkbox100" id="ckb1" type="checkbox" name="remember_device">
                            <label class="label-checkbox100" for="ckb1">
                                Ingat browser ini selama 30 hari
                            </label>
                        </div>

                        <div class="container-login100-form-btn">
                            <button class="login100-form-btn" name="verify_code">
                                Verifikasi
                            </button>
                        </div>

                        <div class="text-center p-t-50">
                            <span class="txt1">
                                Tidak punya akses ke Authenticator?
                            </span>
                            <br>
                            <button type="submit" name="send_email" class="txt2"
                                style="background: none; border: none; cursor: pointer; color: white; text-decoration: underline;">
                                Kirim kode via Email
                            </button>
                        </div>
                </form>
            </div>
        </div>
    </div>

    <script src="js/jquery-3.2.1.min.js"></script>
    <script src="js/main.js"></script>
</body>

</html>