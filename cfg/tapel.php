<?php
/**
 * detect_tapel
 * Detects and updates the active Tapel (Tahun Pelajaran) based on current date.
 * Automatically creates the period if it doesn't exist.
 */
function detect_tapel($sqlconn) {
    $bulan = (int)date('n');
    $tahun_ini = (int)date('Y');

    // Logic: Semester 1 (July - December), Semester 2 (January - June)
    if ($bulan >= 7) {
        $smt = '1';
        $tahun_pelajaran_start = $tahun_ini;
        $tahun_pelajaran_end = $tahun_ini + 1;
        $tapel_text = $tahun_pelajaran_start . "/" . $tahun_pelajaran_end;
        $tahun_db = (string)$tahun_pelajaran_start;
    } else {
        $smt = '2';
        $tahun_pelajaran_start = $tahun_ini - 1;
        $tahun_pelajaran_end = $tahun_ini;
        $tapel_text = $tahun_pelajaran_start . "/" . $tahun_pelajaran_end;
        $tahun_db = (string)$tahun_pelajaran_start;
    }

    // Check if the calculated Tapel & Semester exists in the database
    $tahun_db_escaped = mysqli_real_escape_string($sqlconn, $tahun_db);
    $smt_escaped = mysqli_real_escape_string($sqlconn, $smt);
    
    $cek_query = "SELECT * FROM tapel WHERE tahun='$tahun_db_escaped' AND smt='$smt_escaped'";
    $cek_result = mysqli_query($sqlconn, $cek_query);

    // If it doesn't exist, insert it
    if ($cek_result && mysqli_num_rows($cek_result) == 0) {
        $tapel_text_escaped = mysqli_real_escape_string($sqlconn, $tapel_text);
        $insert_query = "INSERT INTO tapel (tapel, smt, tahun, aktif) VALUES ('$tapel_text_escaped', '$smt_escaped', '$tahun_db_escaped', '1')";
        mysqli_query($sqlconn, $insert_query);
    }

    // Update 'aktif' status: Set all to 0, then activate the current period
    mysqli_query($sqlconn, "UPDATE tapel SET aktif='0'");
    mysqli_query($sqlconn, "UPDATE tapel SET aktif='1' WHERE tahun='$tahun_db_escaped' AND smt='$smt_escaped'");
    
    return [
        'tapel' => $tapel_text,
        'smt' => $smt,
        'tahun' => $tahun_db
    ];
}

/**
 * activate_tapel
 * Activates a specific tapel/semester.
 */
function activate_tapel($sqlconn, $tahun, $smt) {
    $tapel_text = $tahun . "/" . ($tahun + 1);
    
    $tahun_escaped = mysqli_real_escape_string($sqlconn, $tahun);
    $smt_escaped = mysqli_real_escape_string($sqlconn, $smt);
    
    // Check if exists
    $cek_query = "SELECT * FROM tapel WHERE tahun='$tahun_escaped' AND smt='$smt_escaped'";
    $cek_result = mysqli_query($sqlconn, $cek_query);

    if ($cek_result && mysqli_num_rows($cek_result) == 0) {
        $tapel_text_escaped = mysqli_real_escape_string($sqlconn, $tapel_text);
        $insert_query = "INSERT INTO tapel (tapel, smt, tahun, aktif) VALUES ('$tapel_text_escaped', '$smt_escaped', '$tahun_escaped', '1')";
        mysqli_query($sqlconn, $insert_query);
    }

    // Activate: Set all to 0, then activate the specific one
    mysqli_query($sqlconn, "UPDATE tapel SET aktif='0'");
    mysqli_query($sqlconn, "UPDATE tapel SET aktif='1' WHERE tahun='$tahun_escaped' AND smt='$smt_escaped'");
}

/**
 * get_expected_tapel
 * Calculates the expected academic year and semester based on current date.
 * July - December = Semester 1 (Ganjil)
 * January - June = Semester 2 (Genap)
 */
function get_expected_tapel() {
    $month = (int)date('n');
    $year = (int)date('Y');
    
    if ($month >= 7) {
        // July to December: Semester 1 of current year / next year
        $tapel = $year . "/" . ($year + 1);
        $smt = '1';
        $tahun = (string)$year;
    } else {
        // January to June: Semester 2 of previous year / current year
        $tapel = ($year - 1) . "/" . $year;
        $smt = '2';
        $tahun = (string)($year - 1);
    }
    
    return [
        'tapel' => $tapel,
        'smt' => $smt,
        'tahun' => $tahun
    ];
}

/**
 * check_tapel_exists
 * Checks if a specific tapel and semester exist in the database.
 */
function check_tapel_exists($conn, $tapel, $smt) {
    if (!$conn) return false;
    $tapel = mysqli_real_escape_string($conn, $tapel);
    $smt = mysqli_real_escape_string($conn, $smt);
    
    $query = "SELECT id FROM tapel WHERE tapel = '$tapel' AND smt = '$smt'";
    $result = mysqli_query($conn, $query);
    
    return ($result && mysqli_num_rows($result) > 0);
}
?>
