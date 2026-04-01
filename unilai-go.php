<?php
/**
 * Backend Handler for Grade Data Excel Import (AJAX)
 * URL: unilai-go.php
 */

error_reporting(0);
ini_set('display_errors', 0);

register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && ($err['type'] === E_ERROR || $err['type'] === E_USER_ERROR || $err['type'] === E_PARSE || $err['type'] === E_COMPILE_ERROR)) {
        if (ob_get_length()) ob_clean(); // Clean any partial output
        header('Content-Type: application/json');
        echo json_encode(["11", "Fatal Error: " . $err['message'] . " in " . basename($err['file']) . ":" . $err['line']]);
        exit;
    }
});

ob_start();
require_once "cfg/konek.php";
require_once "cfg/secure.php";
ob_clean();

header('Content-Type: application/json');
set_time_limit(0);
ini_set('memory_limit', '512M');

if (!isset($_FILES['userfile'])) { echo json_encode(["1", "Error: No file selected."]); exit; }

$file = $_FILES['userfile'];
if ($file['error'] !== UPLOAD_ERR_OK) { echo json_encode(["1", "Error: Upload failed with code " . $file['error']]); exit; }

$file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$log = ["3"]; // Status code 3 for success start
$sukses = 0; $gagal = 0; $total_rows = 0;

$col_mapping = [
    'pd' => 0, 'jk' => 1, 'nis' => 2, 'nisn' => 3, 'kelas' => 4,'tempat_lahir' => 5,'tgl_lahir' => 6,'nik' => 7,'agama' => 8,'alamat' => 9,'no_hp' => 10,'email' => 11,'nama_ayah' => 12,'nama_ibu' => 13,
    'pkn_1' => 14, 'ind_1' => 15, 'mtk_1' => 16, 'ipa_1' => 17, 'ips_1' => 18, 'eng_1' => 19,
    'pkn_2' => 20, 'ind_2' => 21, 'mtk_2' => 22, 'ipa_2' => 23, 'ips_2' => 24, 'eng_2' => 25,
    'pkn_3' => 26, 'ind_3' => 27, 'mtk_3' => 28, 'ipa_3' => 29, 'ips_3' => 30, 'eng_3' => 31,
    'pkn_4' => 32, 'ind_4' => 33, 'mtk_4' => 34, 'ipa_4' => 35, 'ips_4' => 36, 'eng_4' => 37,
    'pkn_5' => 38, 'ind_5' => 39, 'mtk_5' => 40, 'ipa_5' => 41, 'ips_5' => 42, 'eng_5' => 43
];

$cols = array_keys($col_mapping);

try {
    $baris = 0; $use_phpexcel = false; $data_reader = null;

    if ($file_extension == 'xlsx') {
        if (file_exists('PHPExcel/PHPExcel.php')) {
            require_once 'PHPExcel/PHPExcel.php';
            $use_phpexcel = true;
            $objPHPExcel = PHPExcel_IOFactory::load($file['tmp_name']);
            $worksheet = $objPHPExcel->getActiveSheet();
            $baris = $worksheet->getHighestRow();
        } else {
            echo json_encode(["10", "File .xlsx tidak didukung karena ekstensi PHPExcel tidak terinstall. Silakan 'Save As' file Excel Anda menjadi format lama yaitu Excel 97-2003 Workbook (.xls)."]); exit;
        }
    } elseif ($file_extension == 'xls') {
        require_once "excel_reader2.php";
        if (!class_exists('Spreadsheet_Excel_Reader')) {
            echo json_encode(["10", "Error: excel_reader2 library not found for .xls files."]); exit;
        }
        $data_reader = new Spreadsheet_Excel_Reader($file['tmp_name']);
        if (isset($data_reader->error) && $data_reader->error == 1) { echo json_encode(["10", "Error: Invalid Excel format."]); exit; }
        $baris = $data_reader->rowcount($sheet_index = 0);
    } else {
        echo json_encode(["4", "Error: Unsupported file format. Use .xls or .xlsx"]); exit;
    }

    if ($baris < 2) { echo json_encode(["5", "Error: Excel file is empty or missing data rows."]); exit; }

    $total_rows = $baris - 1;
    $log[] = "INFO: Starting import of " . $total_rows . " rows from " . $file['name'];

    for ($i = 2; $i <= $baris; $i++) {
        $row_data = [];
        foreach ($col_mapping as $colName => $excelIndex) {
            if ($use_phpexcel) {
                // getCellByColumnAndRow is 0-indexed for col, 1-indexed for row
                $val = trim((string)$worksheet->getCellByColumnAndRow($excelIndex, $i)->getValue());
            } else {
                // excel_reader2 is 1-indexed for both
                $val = trim((string)$data_reader->val($i, $excelIndex + 1));
            }
            $row_data[$colName] = $val;
        }

        $pd   = $row_data['pd'];
        $nis  = $row_data['nis'];

        if (empty($pd) && empty($nis)) {
            $log[] = "SKIPPED: Row $i - Student name and NIS are empty"; continue;
        }
        
        if (strtolower($pd) === 'nama' || strtolower($pd) === 'pd') {
            $log[] = "SKIPPED: Row $i - Header row detected"; continue;
        }

        // Default empty grades to NULL instead of saving them as empty strings if they are purely empty
        foreach ($cols as $idx => $colName) {
            if ($idx >= 5 && $row_data[$colName] === "") {
                $row_data[$colName] = null;
            }
        }

        // Check if student exists in `nilai` table to decide INSERT or UPDATE
        $stmt = mysqli_prepare($sqlconn, "SELECT id FROM nilai WHERE nis = ? OR pd = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "ss", $nis, $pd);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $exists = (mysqli_num_rows($res) > 0);
        mysqli_stmt_close($stmt);

        // Prepare statements using dynamic arrays
        $params = [];
        $types  = "";
        
        foreach($cols as $col) {
            $params[] = $row_data[$col];
            // Provide correctly typed strings or nulls for prepared statement binding
            $types .= "s";
        }

        // To bind dynamically we must pass arguments by reference in PHP <= 8.0, 
        // using the splat operator `...` is simpler but needs array of references if using call_user_func_array.
        // Or we can just build an array of references:
        $bind_params = [];
        $bind_params[0] = $types;
        for($j=0; $j<count($params); $j++) {
           $bind_params[] = &$params[$j];
        }

        if ($exists) {
            $set_clause = implode("=?, ", $cols) . "=?";
            $sql = "UPDATE nilai SET $set_clause WHERE nis = ?";
            
            $bind_params[0] .= "s";
            $bind_params[] = &$nis;
            
            $upd = mysqli_prepare($sqlconn, $sql);
            call_user_func_array(array($upd, 'bind_param'), $bind_params);
            
            if (mysqli_stmt_execute($upd)) {
                $log[] = "SUCCESS: Updated grades for [$pd] (NIS: $nis)";
                $sukses++;
            } else {
                $log[] = "ERROR: Row $i - Failed to update [$pd]: " . mysqli_error($sqlconn);
                $gagal++;
            }
            mysqli_stmt_close($upd);
        } else {
            $col_names = implode(", ", $cols);
            $placeholders = implode(", ", array_fill(0, count($cols), "?"));
            $sql = "INSERT INTO nilai ($col_names) VALUES ($placeholders)";
            
            $ins = mysqli_prepare($sqlconn, $sql);
            call_user_func_array(array($ins, 'bind_param'), $bind_params);
            
            if (mysqli_stmt_execute($ins)) {
                $log[] = "SUCCESS: Inserted grades for [$pd] (NIS: $nis)";
                $sukses++;
            } else {
                $log[] = "ERROR: Row $i - Failed to insert [$pd]: " . mysqli_error($sqlconn);
                $gagal++;
            }
            mysqli_stmt_close($ins);
        }
    }

    $log[] = "DONE: Processed $total_rows rows.";
    $log[] = "SUMMARY: Success=$sukses, Failed=$gagal";

} catch (Throwable $e) {
    if (ob_get_length()) ob_clean();
    echo json_encode(["11", "Critical Error: " . $e->getMessage()]); exit;
}

if (ob_get_length()) ob_clean();

function utf8ize($d) {
    if (is_array($d)) {
        foreach ($d as $k => $v) { $d[$k] = utf8ize($v); }
    } else if (is_string($d)) {
        return mb_convert_encoding($d, 'UTF-8', 'UTF-8');
    }
    return $d;
}

$log = utf8ize($log);
$json = json_encode($log, JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
if ($json === false) { echo json_encode(["11", "JSON Encode Error: " . json_last_error_msg()]); } else { echo $json; }
?>
