<?php
include "cfg/konek.php";
include "cfg/secure.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tapel = mysqli_real_escape_string($sqlconn, $_POST['tapel']);
    $smt = mysqli_real_escape_string($sqlconn, $_POST['smt']);
    $tahun = mysqli_real_escape_string($sqlconn, $_POST['tahun']);

    // 1. Deactivate current active tapels
    $deactivate = mysqli_query($sqlconn, "UPDATE tapel SET aktif = '0'");
    
    if ($deactivate) {
        // 2. Insert new tapel entry as active
        // Note: Using NULL for auto-increment ID if it exists, or handling manual ID if necessary.
        // Based on schema, ID is there but not explicitly auto_increment in the dump for tapel, 
        // though it usually is. Let's try to get max(id)+1 just in case, or use NULL.
        $res_id = mysqli_query($sqlconn, "SELECT MAX(id) as max_id FROM tapel");
        $row_id = mysqli_fetch_array($res_id);
        $next_id = ($row_id['max_id'] ?? 0) + 1;

        $insert = mysqli_query($sqlconn, "INSERT INTO tapel (id, tapel, smt, tahun, aktif) VALUES ('$next_id', '$tapel', '$smt', '$tahun', '1')");
        
        if ($insert) {
            // 3. Update dbset table to reflect the current year
            // Detect if this is a new academic year (SMT 1)
            // If it's a new academic year, we might need to sync dbset year
            $update_dbset = mysqli_query($sqlconn, "UPDATE dbset SET aktif = '1', tahun = '$tahun' WHERE dbname = '$database'");
            
            if ($update_dbset) {
                // Log the action
                if (function_exists('write_log')) {
                    write_log("SYSTEM", "Activated New Academic Period: $tapel Semester $smt");
                }
                echo "success";
            } else {
                echo "Gagal mengupdate dbset: " . mysqli_error($sqlconn);
            }
        } else {
            echo "Gagal membuat tapel baru: " . mysqli_error($sqlconn);
        }
    } else {
        echo "Gagal menonaktifkan tapel lama: " . mysqli_error($sqlconn);
    }
} else {
    echo "Invalid Request";
}
?>
