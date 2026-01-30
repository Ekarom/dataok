<?php
/**
 * Backend Handler for Individual Profile Photo Upload
 * URL: post/photo.php
 */

include "../cfg/konek.php";
include "../cfg/secure.php";

// Initialize id_user from session if secure.php didn't provide it
if (empty($id_user)) {
    if (isset($_SESSION['id'])) {
        $id_user = $_SESSION['id'];
    } elseif (isset($userc)) { // $userc usually comes from secure.php
         $q_temp = mysqli_query($sqlconn, "SELECT id FROM usera WHERE userid='$userc'");
         if ($q_temp && mysqli_num_rows($q_temp) > 0) {
            $r_temp = mysqli_fetch_assoc($q_temp);
            $id_user = $r_temp['id'];
         }
    }
}

if (empty($id_user)) {
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

// Validate file size (e.g., 2MB max)
if ($file["size"] > 2 * 1024 * 1024) {
    die("ERROR: File size exceeds 2MB limit.");
}

$temp = explode(".", $file["name"]);
$extension = strtolower(end($temp));
// Use $userc for filename if available, or just ID
$file_prefix = isset($userc) ? $userc : $id_user;
$newfilename = $file_prefix . "_" . time() . "." . $extension;

// Ensure directory exists
$target_dir = "../images/";
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

if (move_uploaded_file($file["tmp_name"], $target_dir . $newfilename)) {
    // Update database
    $stmt = mysqli_prepare($sqlconn, "UPDATE usera SET poto = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "si", $newfilename, $id_user);
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        
        // Log activity
        $activity = "Mengupdate foto profil";
        
        // Get IP safely
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) $ip = $_SERVER['HTTP_CLIENT_IP'];
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else $ip = $_SERVER['REMOTE_ADDR'];
        
        // Ensure nama is set
        if (empty($nama) && isset($userc)) {
             // fetch nama if missing
             $qn = mysqli_query($sqlconn, "SELECT nama FROM usera WHERE id='$id_user'");
             if ($qn) { $rn = mysqli_fetch_assoc($qn); $nama = $rn['nama']; }
             else { $nama = $userc; }
        }

        $log_stmt = mysqli_prepare($sqlconn, "INSERT INTO usera_log (user, nama, info, waktu, ip) VALUES (?, ?, ?, NOW(), ?)");
        // binding ssss (user, nama, info, ip)
        // userc might be missing if only id_user is set
        $log_user = isset($userc) ? $userc : $id_user;
        
        if ($log_stmt) {
            mysqli_stmt_bind_param($log_stmt, "ssss", $log_user, $nama, $activity, $ip);
            mysqli_stmt_execute($log_stmt);
            mysqli_stmt_close($log_stmt);
        }
        
        // Return the new image URL and a success flag
        echo "SUCCESS|" . "file/profil/" . $newfilename;
    } else {
        mysqli_stmt_close($stmt);
        die("ERROR: Failed to update database.");
    }
} else {
    die("ERROR: Failed to save uploaded file.");
}
?>
