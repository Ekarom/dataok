<?php
/**
 * Backend Handler for Image Upload (AJAX)
 * URL: uphoto-go.php
 */

include "cfg/konek.php";
include "cfg/secure.php";

header('Content-Type: application/json');
set_time_limit(0); 
ini_set('memory_limit', '256M');

if (!isset($_FILES['file1'])) {
    echo json_encode(["1", "Error: No file selected."]);
    exit;
}

$file = $_FILES['file1'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["1", "Error: Upload failed with code " . $file['error']]);
    exit;
}

// Validate ZIP
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($ext !== 'zip') {
    echo json_encode(["4", "Error: File must be a .zip file."]);
    exit;
}

$foto_dir = "file/fotopd/";
if (!file_exists($foto_dir)) {
    mkdir($foto_dir, 0755, true);
}

if (!is_writable($foto_dir)) {
    echo json_encode(["6", "Error: Destination directory not writable."]);
    exit;
}

$temp_dir = "temp/uphoto_" . time() . "/";
if (!file_exists($temp_dir)) {
    mkdir($temp_dir, 0755, true);
}

$zip = new ZipArchive;
if ($zip->open($file['tmp_name']) === TRUE) {
    if ($zip->numFiles == 0) {
        echo json_encode(["5", "Error: ZIP file is empty."]);
        $zip->close();
        deleteDir($temp_dir);
        exit;
    }
    $zip->extractTo($temp_dir);
    $zip->close();
} else {
    echo json_encode(["110", "Error: Failed to open ZIP file or invalid format."]);
    deleteDir($temp_dir);
    exit;
}

// Process photos
$sukses = 0;
$found_any = false;
$log = ["3"]; // Status code 3 for success start

try {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($temp_dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($files as $f) {
        if ($f->isFile()) {
            $fname = $f->getFilename();
            $fext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
            $found_any = true;
            
            if (in_array($fext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $nis = trim(pathinfo($fname, PATHINFO_FILENAME));
                
                if (empty($nis)) {
                    $log[] = "ERROR: Empty filename for " . $fname;
                    continue;
                }
                
                // Find student
                $stmt = mysqli_prepare($sqlconn, "SELECT pd FROM siswa WHERE nis = ? LIMIT 1");
                mysqli_stmt_bind_param($stmt, "s", $nis);
                mysqli_stmt_execute($stmt);
                $res = mysqli_stmt_get_result($stmt);
                
                if ($row = mysqli_fetch_assoc($res)) {
                    $new_name = preg_replace('/[^a-zA-Z0-9]/', '', $nis) . '.' . $fext;
                    if (copy($f->getPathname(), $foto_dir . $new_name)) {
                        $up = mysqli_prepare($sqlconn, "UPDATE siswa SET photo = ? WHERE nis = ?");
                        mysqli_stmt_bind_param($up, "ss", $new_name, $nis);
                        mysqli_stmt_execute($up);
                        
                        $log[] = "SUCCESS: " . $nis . " - " . $row['pd'];
                        $sukses++;
                    } else {
                        $log[] = "ERROR: Failed to copy " . $nis;
                    }
                } else {
                    $log[] = "ERROR: Student with NIS [" . $nis . "] not found in database";
                }
                mysqli_stmt_close($stmt);
            } else {
                $log[] = "SKIPPED: " . $fname . " (Not an image)";
            }
        }
    }
    
    if (!$found_any) {
        $log[] = "INFO: ZIP extracted but no files were found inside.";
    }
} catch (Exception $e) {
    echo json_encode(["11", "Critical Error: " . $e->getMessage()]);
    exit;
}

// Clean up
function deleteDir($dir) {
    if (!file_exists($dir)) return;
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $f) {
        (is_dir("$dir/$f")) ? deleteDir("$dir/$f") : unlink("$dir/$f");
    }
    return rmdir($dir);
}
deleteDir($temp_dir);

// Status 3 = Processing complete
if (count($log) > 1) {
    echo json_encode($log);
} else {
    // If no image files were even attempted, return code 5
    echo json_encode(["5", "Error: No image files (.jpg, .png, .jpeg) found in the ZIP archive."]);
}
?>
