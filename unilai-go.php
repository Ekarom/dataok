<?php
/**
 * Backend Handler for Grade Data Excel Import (Final Optimized)
 * URL: unilai-go.php
 */

// Sembunyikan error HTML, pastikan hanya JSON yang keluar
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Handler untuk menangkap Fatal Error agar tetap keluar sebagai JSON
register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        if (ob_get_length())
            ob_clean();
        header('Content-Type: application/json');
        echo json_encode(["11", "Fatal Error: " . $err['message'] . " di baris " . $err['line']]);
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

if (!isset($_FILES['userfile'])) {
    echo json_encode(["1", "Error: Tidak ada file yang dipilih."]);
    exit;
}

$file = $_FILES['userfile'];
$file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$log = ["3"]; // Kode status 3: Proses dimulai
$sukses = 0;
$gagal = 0;

/**
 * FIX: Mapping kolom disesuaikan berdasarkan image_d41790.png
 * Kolom A=0 (nama), B=1 (jk), C=2 (nis), D=3 (nisn), E=4 (kelas), dst.
 */
$col_mapping = [
    'pd' => 0,  // Kolom A
    'jk' => 1,  // Kolom B
    'nis' => 2,  // Kolom C
    'nisn' => 3,  // Kolom D
    'kelas' => 4,  // Kolom E
    'tempat_lahir' => 5,  // Kolom F
    'tgl_lahir' => 6,  // Kolom G
    'nik' => 7,  // Kolom H
    'agama' => 8,  // Kolom I
    'alamat' => 9,  // Kolom J
    'no_hp' => 10, // Kolom K
    'email' => 11, // Kolom L
    'nama_ayah' => 12, // Kolom M
    'nama_ibu' => 13, // Kolom N
    // Semester 1 (Mulai Kolom O = 14)
    'pkn_1' => 14,
    'ind_1' => 15,
    'mtk_1' => 16,
    'ipa_1' => 17,
    'ips_1' => 18,
    'eng_1' => 19,
    // Semester 2
    'pkn_2' => 20,
    'ind_2' => 21,
    'mtk_2' => 22,
    'ipa_2' => 23,
    'ips_2' => 24,
    'eng_2' => 25,
    // Semester 3
    'pkn_3' => 26,
    'ind_3' => 27,
    'mtk_3' => 28,
    'ipa_3' => 29,
    'ips_3' => 30,
    'eng_3' => 31,
    // Semester 4
    'pkn_4' => 32,
    'ind_4' => 33,
    'mtk_4' => 34,
    'ipa_4' => 35,
    'ips_4' => 36,
    'eng_4' => 37,
    // Semester 5
    'pkn_5' => 38,
    'ind_5' => 39,
    'mtk_5' => 40,
    'ipa_5' => 41,
    'ips_5' => 42,
    'eng_5' => 43
];

$cols = array_keys($col_mapping);

try {
    $excel_data = [];

    // --- PROSES MEMBACA FILE ---
    if ($file_extension == 'xlsx') {
        if (!file_exists('PHPExcel/PHPExcel.php')) {
            echo json_encode(["10", "Library PHPExcel tidak ditemukan."]);
            exit;
        }
        require_once 'PHPExcel/PHPExcel.php';
        $objPHPExcel = PHPExcel_IOFactory::load($file['tmp_name']);
        $worksheet = $objPHPExcel->getActiveSheet();
        $highestRow = $worksheet->getHighestRow();

        for ($i = 2; $i <= $highestRow; $i++) {
            $row = [];
            $has_content = false;
            foreach ($col_mapping as $colName => $idx) {
                $val = trim((string) $worksheet->getCellByColumnAndRow($idx, $i)->getValue());
                $row[$colName] = $val;
                if ($val !== "")
                    $has_content = true;
            }
            if ($has_content)
                $excel_data[] = $row;
        }
    } elseif ($file_extension == 'xls') {
        require_once "excel_reader2.php";
        $data_reader = new Spreadsheet_Excel_Reader($file['tmp_name']);
        $rowCount = $data_reader->rowcount();

        for ($i = 2; $i <= $rowCount; $i++) {
            $row = [];
            $has_content = false;
            foreach ($col_mapping as $colName => $idx) {
                $val = trim((string) $data_reader->val($i, $idx + 1));
                $row[$colName] = $val;
                if ($val !== "")
                    $has_content = true;
            }
            if ($has_content)
                $excel_data[] = $row;
        }
    }

    // --- DATABASE OPS ---
    $stmt_check = mysqli_prepare($sqlconn, "SELECT id FROM nilai WHERE nis = ? LIMIT 1");
    $fields = implode(", ", $cols);
    $placeholders = implode(", ", array_fill(0, count($cols), "?"));
    $stmt_ins = mysqli_prepare($sqlconn, "INSERT INTO nilai ($fields) VALUES ($placeholders)");
    $set_clause = implode("=?, ", $cols) . "=?";
    $stmt_upd = mysqli_prepare($sqlconn, "UPDATE nilai SET $set_clause WHERE nis = ?");
    $types = str_repeat("s", count($cols));

    foreach ($excel_data as $index => $row) {
        $nis = $row['nis'];
        $row_num = $index + 2;
        if (empty($nis))
            continue;

        mysqli_stmt_bind_param($stmt_check, "s", $nis);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);
        $exists = mysqli_stmt_num_rows($stmt_check) > 0;

        $values = array_values($row);

        if ($exists) {
            $upd_values = array_merge($values, [$nis]);
            mysqli_stmt_bind_param($stmt_upd, $types . "s", ...$upd_values);
            $exec = mysqli_stmt_execute($stmt_upd);
        } else {
            mysqli_stmt_bind_param($stmt_ins, $types, ...$values);
            $exec = mysqli_stmt_execute($stmt_ins);
        }

        if ($exec)
            $sukses++;
        else
            $gagal++;
    }

    $log[] = "IMPORT SELESAI: Berhasil=$sukses, Gagal=$gagal.";

} catch (Throwable $e) {
    if (ob_get_length())
        ob_clean();
    echo json_encode(["11", "Kesalahan Sistem: " . $e->getMessage()]);
    exit;
}

if (ob_get_length())
    ob_clean();
echo json_encode($log);
?>