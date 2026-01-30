<?php
/**
 * Backend Handler for Student Data Excel Import (AJAX)
 * URL: usiswa-go.php
 */

// Turn off error reporting for production/AJAX to prevent JSON breakage
error_reporting(0);
ini_set('display_errors', 0);

// Register shutdown function to catch Fatal Errors (which usually cause empty responses)
register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && ($err['type'] === E_ERROR || $err['type'] === E_USER_ERROR || $err['type'] === E_PARSE || $err['type'] === E_COMPILE_ERROR)) {
        if (ob_get_length()) ob_clean(); // Clean any partial output
        header('Content-Type: application/json'); // Ensure header is sent
        echo json_encode(["11", "Fatal Error: " . $err['message'] . " in " . basename($err['file']) . ":" . $err['line']]);
        exit;
    }
});

// Start output buffering to catch any stray whitespace or includes output
ob_start();

require_once "cfg/konek.php";
require_once "cfg/secure.php";

// Clean the buffer to ensure no whitespace before headers
ob_clean();

header('Content-Type: application/json');
set_time_limit(0);
ini_set('memory_limit', '512M');

if (!isset($_FILES['userfile'])) {
    echo json_encode(["1", "Error: No file selected."]);
    exit;
}

$file = $_FILES['userfile'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["1", "Error: Upload failed with code " . $file['error']]);
    exit;
}

$file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$log = ["3"]; // Status code 3 for success start
$sukses = 0;
$gagal = 0;
$total_rows = 0;

try {
    $baris = 0;
    $use_phpexcel = false;
    $data_reader = null;

    if ($file_extension == 'xlsx') {
        if (file_exists('PHPExcel/PHPExcel.php')) {
            require_once 'PHPExcel/PHPExcel.php';
            $use_phpexcel = true;
            $objPHPExcel = PHPExcel_IOFactory::load($file['tmp_name']);
            $worksheet = $objPHPExcel->getActiveSheet();
            $baris = $worksheet->getHighestRow();
        } else {
            echo json_encode(["10", "Error: PHPExcel library not found for .xlsx files."]);
            exit;
        }
    } elseif ($file_extension == 'xls') {
        require_once "excel_reader2.php";
        if (!class_exists('Spreadsheet_Excel_Reader')) {
            echo json_encode(["10", "Error: excel_reader2 library not found for .xls files."]);
            exit;
        }
        $data_reader = new Spreadsheet_Excel_Reader($file['tmp_name']);
        if (isset($data_reader->error) && $data_reader->error == 1) {
            echo json_encode(["10", "Error: Invalid Excel format."]);
            exit;
        }
        $baris = $data_reader->rowcount($sheet_index = 0);
    } else {
        echo json_encode(["4", "Error: Unsupported file format. Use .xls or .xlsx"]);
        exit;
    }

    if ($baris < 2) {
        echo json_encode(["5", "Error: Excel file is empty or missing data rows."]);
        exit;
    }

    $total_rows = $baris - 1; // Excluding header
    $log[] = "INFO: Starting import of " . $total_rows . " rows from " . $file['name'];

    for ($i = 2; $i <= $baris; $i++) {
        if ($use_phpexcel) {
            $pd     = trim((string)$worksheet->getCell('A' . $i)->getValue());
            $jk     = trim((string)$worksheet->getCell('B' . $i)->getValue());
            $nis    = trim((string)$worksheet->getCell('C' . $i)->getValue());
            $nisn   = trim((string)$worksheet->getCell('D' . $i)->getValue());
            $kelas  = trim((string)$worksheet->getCell('E' . $i)->getValue());
            $photo  = trim((string)$worksheet->getCell('F' . $i)->getValue());
        } else {
            $pd     = trim((string)$data_reader->val($i, 1));
            $jk     = trim((string)$data_reader->val($i, 2));
            $nis    = trim((string)$data_reader->val($i, 3));
            $nisn   = trim((string)$data_reader->val($i, 4));
            $kelas  = trim((string)$data_reader->val($i, 5));
            $photo  = trim((string)$data_reader->val($i, 6));
        }

        if (empty($pd)) {
            $log[] = "SKIPPED: Row $i - Student name is empty";
            continue;
        }

        // Check for duplicates
        $stmt = mysqli_prepare($sqlconn, "SELECT id FROM siswa WHERE nis = ? OR pd = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "ss", $nis, $pd);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($res) > 0) {
            $log[] = "ERROR: Row $i - Student [$pd] with NIS [$nis] already exists";
            $gagal++;
        } else {
            $ins = mysqli_prepare($sqlconn, "INSERT INTO siswa (pd, jk, nis, nisn, kelas, photo) VALUES (?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($ins, "ssssss", $pd, $jk, $nis, $nisn, $kelas, $photo);
            if (mysqli_stmt_execute($ins)) {
                $log[] = "SUCCESS: Imported [$pd] (NIS: $nis)";
                $sukses++;
            } else {
                $log[] = "ERROR: Row $i - Failed to insert [$pd]: " . mysqli_error($sqlconn);
                $gagal++;
            }
            mysqli_stmt_close($ins);
        }
        mysqli_stmt_close($stmt);
    }

    $log[] = "DONE: Processed $total_rows rows.";
    $log[] = "SUMMARY: Success=$sukses, Failed=$gagal";

} catch (Throwable $e) {
    if (ob_get_length()) ob_clean();
    echo json_encode(["11", "Critical Error: " . $e->getMessage()]);
    exit;
}

// Ensure clean buffer before final output
if (ob_get_length()) ob_clean();

// Helper to sanitize UTF-8
function utf8ize($d) {
    if (is_array($d)) {
        foreach ($d as $k => $v) {
            $d[$k] = utf8ize($v);
        }
    } else if (is_string($d)) {
        return mb_convert_encoding($d, 'UTF-8', 'UTF-8');
    }
    return $d;
}

$log = utf8ize($log);

$json = json_encode($log, JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);

if ($json === false) {
    echo json_encode(["11", "JSON Encode Error: " . json_last_error_msg()]);
} else {
    echo $json;
}

