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

$error = '';

$attempts = isset($_SESSION['2fa_attempts']) ? $_SESSION['2fa_attempts'] : 0;



// DEBUG: Calculate expected code

$current_time = time();

$expected_code = '';

if (!empty($temp_login['google_secret'])) {

    $expected_code = $ga->getCode($temp_login['google_secret']);

}



// DEBUG: Log untuk troubleshooting

error_log("2FA Verification - User: " . $temp_login['username']);

error_log("2FA Secret exists: " . (!empty($temp_login['google_secret']) ? 'Yes' : 'No'));

error_log("2FA Secret value: " . (!empty($temp_login['google_secret']) ? $temp_login['google_secret'] : 'NULL'));

error_log("2FA Expected code: " . $expected_code);

error_log("2FA Server time: " . date('Y-m-d H:i:s', $current_time));



// Handle form submission

if (isset($_POST['verify_code'])) {

    $code = trim($_POST['code']);

    

    // DEBUG: Log kode yang diinput

    error_log("2FA Code submitted: " . $code);

    error_log("2FA Code length: " . strlen($code));

    error_log("2FA Expected code at submit: " . $ga->getCode($temp_login['google_secret']));

    

    // Verify the code

    $verification_result = $ga->verifyCode($temp_login['google_secret'], $code, 2);

    

    if ($verification_result) {

        // Code is valid - Complete login

        

        error_log("2FA Verification SUCCESS for user: " . $temp_login['username']);

        

        // Reset 2FA attempts

        unset($_SESSION['2fa_attempts']);

        

        // Regenerate session ID untuk mencegah session fixation

        session_regenerate_id(true);



        // Code is valid - Complete login
        error_log("2FA Verification SUCCESS for user: " . $temp_login['username']);

        

        // Set Cookies (30 Hari) - HttpOnly & Secure

        $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

        setcookie('tapel', $temp_login['tapel'], time() + (86400 * 30), "/", "", $is_https, true);

        setcookie('id', $temp_login['id'], time() + (86400 * 30), "/", "", $is_https, true); 

        setcookie('nama', $temp_login['nama'], time() + (86400 * 30), "/", "", $is_https, true);

        setcookie('level', $temp_login['level'], time() + (86400 * 30), "/", "", $is_https, true);

        setcookie('poto', $temp_login['poto'], time() + (86400 * 30), "/", "", $is_https, true);

        setcookie('nik', $temp_login['nik'], time() + (86400 * 30), "/", "", $is_https, true);

        

        // Log successful login

        $stmt_log = mysqli_prepare($sqlconn, "INSERT INTO usera_log (user, nama, waktu, ip, info) VALUES (?, ?, ?, ?, 'login (2FA verified)')");

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

        header("Location: ../?");

        exit();

        

    } else {

        // Invalid code

        error_log("2FA Verification FAILED for user: " . $temp_login['username']);

        error_log("2FA Code entered: " . $code);

        error_log("2FA Expected code: " . $ga->getCode($temp_login['google_secret']));

        

        $_SESSION['2fa_attempts'] = $attempts + 1;

        $attempts = $_SESSION['2fa_attempts'];

        

        if ($attempts >= 3) {

            // Too many failed attempts - log and redirect to login

            $stmt_log_fail = mysqli_prepare($sqlconn, "INSERT INTO usera_log (user, nama, waktu, ip, info) VALUES (?, ?, ?, ?, 'Gagal verifikasi 2FA (3 kali)')");

            if ($stmt_log_fail) {

                $log_time = date("Y-m-d H:i:s");

                mysqli_stmt_bind_param($stmt_log_fail, "ssss", $temp_login['username'], $temp_login['nama'], $log_time, $temp_login['ip']);

                mysqli_stmt_execute($stmt_log_fail);

                mysqli_stmt_close($stmt_log_fail);

            }

            

            unset($_SESSION['temp_login']);

            unset($_SESSION['2fa_attempts']);

            header("Location: login.php?salah=6"); // Error code 6: 2FA failed

            exit();

        }

        

        $error = "Kode salah. Silakan coba lagi. (" . (3 - $attempts) . " percobaan tersisa)<br>";

        $error .= "<small>Kode yang Anda masukkan: <strong>" . htmlspecialchars($code) . "</strong></small>";

    }

}



// BYPASS for testing - REMOVE THIS IN PRODUCTION

if (isset($_POST['bypass_2fa'])) {

    error_log("2FA BYPASS used by: " . $temp_login['username']);

    

    // Complete login without verification

    session_regenerate_id(true);

    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    setcookie('tapel', $temp_login['tapel'], time() + (86400 * 30), "/", "", $is_https, true);

    setcookie('id', $temp_login['id'], time() + (86400 * 30), "/", "", $is_https, true); 

    setcookie('nama', $temp_login['nama'], time() + (86400 * 30), "/", "", $is_https, true);

    setcookie('level', $temp_login['level'], time() + (86400 * 30), "/", "", $is_https, true);

    setcookie('poto', $temp_login['poto'], time() + (86400 * 30), "/", "", $is_https, true);

    setcookie('nik', $temp_login['nik'], time() + (86400 * 30), "/", "", $is_https, true);

    

    unset($_SESSION['temp_login']);

    unset($_SESSION['2fa_attempts']);

    

    header("Location:/?");

    exit();

}



// Handle cancel

if (isset($_POST['cancel'])) {

    unset($_SESSION['temp_login']);

    unset($_SESSION['2fa_attempts']);

    header("Location: login.php");

    exit();

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <title>Verifikasi 2FA - Arsip Data</title>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    

    <!-- Icons -->

    <link rel="icon" type="image/png" href="dist/img/<?php echo $apklogo; ?>">

    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">

    <link rel="stylesheet" type="text/css" href="plugins/iconic/css/material-design-iconic-font.min.css">

    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">



    <!-- Styles -->

    <link rel="stylesheet" type="text/css" href="plugins/css/util.css">

    <link rel="stylesheet" type="text/css" href="plugins/css/main.css">

    

    <style>

        .container-login100 {

            background-position: center;

            background-size: cover;

            background-repeat: no-repeat;

        }

        

        .code-input {

            text-align: center;

            font-size: 32px;

            letter-spacing: 15px;

            font-weight: 600;

            padding: 20px;

            background: rgba(255, 255, 255, 0.1);

            border: 2px solid rgba(255, 255, 255, 0.3);

            border-radius: 10px;

            color: #fff;

            transition: all 0.3s;

        }

        

        .code-input:focus {

            background: rgba(255, 255, 255, 0.15);

            border-color: rgba(255, 255, 255, 0.5);

            outline: none;

        }

        

        .code-input::placeholder {

            color: rgba(255, 255, 255, 0.5);

        }

        

        .alert-2fa {

            background: rgba(220, 53, 69, 0.9);

            color: #fff;

            padding: 15px;

            border-radius: 10px;

            margin-bottom: 20px;

            border: 1px solid rgba(255, 255, 255, 0.3);

        }

        

        .info-text {

            color: rgba(255, 255, 255, 0.8);

            font-size: 13px;

            text-align: center;

            margin-top: 10px;

        }

        

        .debug-info {

            background: rgba(255, 255, 255, 0.1);

            color: #fff;

            padding: 10px;

            border-radius: 5px;

            margin-bottom: 15px;

            font-size: 12px;

            text-align: center;

        }

    </style>

</head>

<body>



    <div class="container-login100" style="background-image: url('dist/img/<?php echo $skback; ?>');">

        <div class="wrap-login100">

            <section id="region-main" class="col-12 h-100" aria-label="Content">

                <form method="post" autocomplete="off">

                    <span class="login100-form-logo">

                        <i class="zmdi landscape"><img src="dist/img/<?php echo $apklogo; ?>" width="120" height="110"/></i>

                    </span>



                    <span class="login100-form-title p-b-34 p-t-27">

                        Verifikasi Keamanan<br>

                    </span>

                    

                    <div class="info-text" style="margin-bottom: 20px;">
                        <i class="fas fa-mobile-alt"></i> Buka Google Authenticator Anda
                    </div>


                    

                    <!-- DEBUG INFO 

                    <div class="debug-info">

                        <small>

                            <strong>DEBUG MODE</strong><br>

                            User: <?php echo htmlspecialchars($temp_login['username']); ?><br>

                            Secret: <?php echo !empty($temp_login['google_secret']) ? 'Set (' . strlen($temp_login['google_secret']) . ' chars)' : 'NOT SET'; ?><br>

                            <?php if (!empty($temp_login['google_secret'])): ?>

                                Secret Value: <?php echo htmlspecialchars($temp_login['google_secret']); ?><br>

                                <strong style="color: #ffeb3b;">Expected Code: <?php echo $expected_code; ?></strong><br>

                            <?php endif; ?>

                            Server Time: <?php echo date('H:i:s', $current_time); ?><br>

                            Attempts: <?php echo $attempts; ?>/3

                        </small>

                    </div>--->

                    

                    <?php if (!empty($error)): ?>

                        <div class="alert-2fa">

                            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>

                        </div>

                    <?php endif; ?>



                    <div class="wrap-input100 validate-input">

                        <input class="input100" type="text" id="code" name="code">

                        <span class="focus-input100" data-placeholder="G"></span>

                    </div>

               

                    

                    <div class="info-text">

                        <i class="fas fa-info-circle"></i> Masukkan kode 6 digit dari Google Authenticator<br>

                        <small>Kode akan berubah setiap 30 detik</small>

                    </div>

                    <br>

                    

                    <div class="container-login100-form-btn">

                        <button type="submit" name="verify_code" class="login100-form-btn">

                            Verifikasi

                        </button>

                    </div>

                    

                    <!-- BYPASS BUTTON FOR TESTING -

                    <div class="container-login100-form-btn" style="margin-top: 10px;">

                        <button type="submit" name="bypass_2fa" class="login100-form-btn" style="background: linear-gradient(to right, #f09819, #ff512f);" onclick="return confirm('BYPASS 2FA? Hanya untuk testing!')">

                            <i class="fas fa-unlock"></i> Bypass 2FA (Testing Only)

                        </button>

                    </div>-->

                </form>

            </section>

        </div>

    </div>



    <script>

        // Auto-focus and auto-submit when 6 digits entered

        document.querySelector('.code-input').addEventListener('input', function(e) {

            // Remove non-numeric characters

            this.value = this.value.replace(/[^0-9]/g, '');

            

            /* Auto-submit removed

            if (this.value.length === 6) {

                // Auto-submit after a short delay

                setTimeout(() => {

                    this.form.submit();

                }, 300);

            }

            */

        });

        

        // Only allow numbers

        document.querySelector('.code-input').addEventListener('keypress', function(e) {

            if (!/[0-9]/.test(e.key)) {

                e.preventDefault();

            }

        });

    </script>

</body>

</html>

<?php ob_end_flush(); ?>

