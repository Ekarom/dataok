<?php
include "../cfg/konek.php";
include "../cfg/secure.php";

// Validate user authentication
if (empty($userc)) {
    die("ERROR: Session tidak valid");
}

// Get input and sanitize
$passLama = isset($_POST['passL']) ? $_POST['passL'] : '';
$passBaru = isset($_POST['passB']) ? $_POST['passB'] : '';
$passKonfirmasi = isset($_POST['passK']) ? $_POST['passK'] : '';

// Validation
if (empty($passLama)) {
    die("1"); // Password lama harus diisi
}

if (empty($passBaru)) {
    die("2"); // Password baru harus diisi
}

if (empty($passKonfirmasi)) {
    die("3"); // Password konfirmasi harus diisi
}

if ($passBaru !== $passKonfirmasi) {
    die("4"); // Password konfirmasi harus sama dengan password baru
}

if (strlen($passBaru) < 6) {
    die("5"); // Password minimal 6 karakter
}

// Verify old password
$stmt = mysqli_prepare($sqlconn, "SELECT password, nama FROM usera WHERE userid= ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "s", $userc);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) == 0) {
    mysqli_stmt_close($stmt);
    die("ERROR: User tidak ditemukan");
}

$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Check old password (support both MD5 and Bcrypt)
$passwordValid = false;
if (password_verify($passLama, $user['password'])) {
    // Bcrypt password
    $passwordValid = true;
} elseif (md5($passLama) === $user['password']) {
    // Legacy MD5 password
    $passwordValid = true;
}

if (!$passwordValid) {
    die("6"); // Password lama salah
}

// Prepare variables for logging
$nama = isset($user['nama']) ? $user['nama'] : $userc;
// Determine IP Address
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip_address = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $ip_address = $_SERVER['REMOTE_ADDR'];
}

// Hash new password with Bcrypt
$newPasswordHash = password_hash($passBaru, PASSWORD_BCRYPT);

// Update password
$update_stmt = mysqli_prepare($sqlconn, "UPDATE usera SET password = ? WHERE userid = ?");
mysqli_stmt_bind_param($update_stmt, "ss", $newPasswordHash, $userc);

if (mysqli_stmt_execute($update_stmt)) {
    mysqli_stmt_close($update_stmt);
    
    // Log activity
    $activity = "Mengganti password";
    $waktu = date("Y-m-d H:i:s");
    $log_stmt = mysqli_prepare($sqlconn, "INSERT INTO usera_log (user, nama, waktu, ip, info) VALUES (?, ?, ?, ?, ?)");
    if ($log_stmt) {
        mysqli_stmt_bind_param($log_stmt, "sssss", $userc, $nama, $waktu, $ip_address, $activity);
        mysqli_stmt_execute($log_stmt);
        mysqli_stmt_close($log_stmt);
    }
    
    echo "SUCCESS";
} else {
    mysqli_stmt_close($update_stmt);
    die("ERROR: Gagal mengupdate password - " . mysqli_error($sqlconn));
}
