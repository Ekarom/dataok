<?php
/**
 * Backend Handler for Individual Profile Photo Upload
 * URL: post/photo.php
 */

include "../cfg/konek.php";
include "../cfg/secure.php";

$user_role = $_SESSION['user_role'] ?? 'admin';
$userc = $_SESSION['skradm'] ?? '';

if (empty($userc)) {
    die("ERROR: Unauthorized access.");
}

if (!isset($_FILES["photo1"])) {
    die("ERROR: No file uploaded.");
}

$file = $_FILES["photo1"];

if ($file["error"] > 0) {
    die("ERROR: Upload error code " . $file["error"]);
}

// Validate file type
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
if (!in_array($file["type"], $allowed_types)) {
    die("ERROR: Only JPG, JPEG, and PNG files are allowed.");
}

// Validate file size (2MB max)
if ($file["size"] > 2 * 1024 * 1024) {
    die("ERROR: File size exceeds 2MB limit.");
}

$temp = explode(".", $file["name"]);
$extension = strtolower(end($temp));
$newfilename = $userc . "_" . time() . "." . $extension;

// Determine target directory and table based on role
if ($user_role === 'siswa') {
    $target_dir = "../file/fotopd/";
    $table = "siswa";
    $col_photo = "photo";
    $col_id = "nis";
    $return_prefix = "file/fotopd/";
} else {
    $target_dir = "../images/";
    $table = "usera";
    $col_photo = "poto";
    $col_id = "userid";
    $return_prefix = "images/";
}

// Ensure directory exists
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

if (move_uploaded_file($file["tmp_name"], $target_dir . $newfilename)) {
    // Update database
    $stmt = mysqli_prepare($sqlconn, "UPDATE $table SET $col_photo = ? WHERE $col_id = ?");
    mysqli_stmt_bind_param($stmt, "ss", $newfilename, $userc);
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        
        // Log activity
        $activity = "Mengupdate foto profil";
        
        // Get IP safely
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) $ip = $_SERVER['HTTP_CLIENT_IP'];
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else $ip = $_SERVER['REMOTE_ADDR'];
        
        // Get Nama for logging
        if ($user_role === 'siswa') {
            $qn = mysqli_query($sqlconn, "SELECT pd as nama FROM siswa WHERE nis='$userc'");
        } else {
            $qn = mysqli_query($sqlconn, "SELECT nama FROM usera WHERE userid='$userc'");
        }
        
        if ($qn && mysqli_num_rows($qn) > 0) {
            $rn = mysqli_fetch_assoc($qn);
            $nama_log = $rn['nama'];
        } else {
            $nama_log = $userc;
        }

        $waktu = date("Y-m-d H:i:s");
        $log_stmt = mysqli_prepare($sqlconn, "INSERT INTO usera_log (user, nama, info, waktu, ip) VALUES (?, ?, ?, ?, ?)");
        if ($log_stmt) {
            mysqli_stmt_bind_param($log_stmt, "sssss", $userc, $nama_log, $activity, $waktu, $ip);
            mysqli_stmt_execute($log_stmt);
            mysqli_stmt_close($log_stmt);
        }
        
        // Return the new image URL and a success flag
        echo "SUCCESS|" . $return_prefix . $newfilename;
    } else {
        mysqli_stmt_close($stmt);
        die("ERROR: Failed to update database.");
    }
} else {
    die("ERROR: Failed to save uploaded file.");
}
?>
