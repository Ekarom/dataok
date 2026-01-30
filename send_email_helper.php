<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\OAuth;
use League\OAuth2\Client\Provider\Google;

function sendEmailCode($toEmail, $code, $sqlconn)
{
    // Load dependencies (gmail folder is in the same directory as this file)
    require_once 'gmail/vendor/autoload.php';

    $mail = new PHPMailer;
    $mail->isSMTP();
    $mail->SMTPDebug = 0; // Turn off debug output
    $mail->Host = 'smtp.gmail.com'; // MUST be smtp.gmail.com for Google XOAUTH2
    $mail->Port = 587;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->SMTPAuth = true;
    $mail->AuthType = 'XOAUTH2';

    // Get Token
    $sql = mysqli_query($sqlconn, "select * from email_token where id='1'");
    if (!$sql) {
        return "Error getting email token: " . mysqli_error($sqlconn);
    }
    $g = mysqli_fetch_array($sql);
    $token = $g['token'];

    $email_sender = 'noreplay@smpn171.sch.id'; 
    $clientId = '932098413787-mi88vb1nk3a5cbe6rqnvctbmvl46pitr.apps.googleusercontent.com';
    $clientSecret = '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';
    $refreshToken = $token;

    $provider = new Google([
        'clientId' => $clientId,
        'clientSecret' => $clientSecret,
    ]);

    $mail->setOAuth(
        new OAuth([
            'provider' => $provider,
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'refreshToken' => $refreshToken,
            'userName' => $email_sender,
        ])
    );

    $mail->setFrom($email_sender, 'Sistem Keamanan Sekolah');
    $mail->addAddress($toEmail);
    $mail->Subject = 'Kode Verifikasi Login';
    $mail->Body = "Kode verifikasi Anda adalah: $code\n\nKode ini akan kadaluarsa dalam 15 menit.";

    if (!$mail->send()) {
        return "Mailer Error: " . $mail->ErrorInfo;
    } else {
        return true;
    }
}
?>