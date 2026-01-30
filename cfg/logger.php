<?php
// cfg/logger.php

if (!function_exists('write_log')) {
    /**
     * Centralized function to write activity logs to usera_log table.
     * 
     * @param string $action The shorthand action (e.g. LOGIN, ADD, EDIT, DELETE)
     * @param string $info Detailed description of the activity
     * @param mysqli $conn Optional mysqli connection object.
     * @return boolean True on success, false on failure.
     */
    function write_log($action, $info, $details = null, $conn = null, $url = null) {
        global $sqlconn, $userc, $nama, $ip;
        
        // If 3rd argument is a mysqli object, assume it's the connection (legacy/convenience)
        if ($details instanceof mysqli) {
            $conn = $details;
            $details = null;
        }

        // Use provided connection or global sqlconn (Main database)
        $db = ($conn !== null) ? $conn : $sqlconn;
        if (!$db || !($db instanceof mysqli)) {
            error_log("Write Log Error: Invalid or missing database connection.");
            return false;
        }

        // Fetch user identity
        $current_user = !empty($userc) ? $userc : (isset($_SESSION['skradm']) ? $_SESSION['skradm'] : 'System');
        $current_nama = !empty($nama) ? $nama : $current_user;
        
        // Fetch IP
        $current_ip = !empty($ip) ? $ip : ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        if ($current_ip == '::1') $current_ip = '127.0.0.1';

        // Metadata - Use provided URL or detect from caller (the file calling write_log)
        if ($url !== null) {
            $current_url = $url;
        } else {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
            $current_url = isset($backtrace[0]['file']) ? basename($backtrace[0]['file']) : basename($_SERVER['PHP_SELF']);
        }
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '-';
        $waktu = date("Y-m-d H:i:s");
        $duration = "-";
        
        // Handle differentiation: $info for usera_log, $details for activity_logs
        $final_details = ($details !== null) ? $details : $info;

        // Sanitize for standardized schema (shared by both tables)
        $s_uid       = mysqli_real_escape_string($db, $current_user);
        $s_unm       = mysqli_real_escape_string($db, $current_nama);
        $s_act       = mysqli_real_escape_string($db, strtoupper($action));
        $s_info      = mysqli_real_escape_string($db, $info);
        $s_det       = mysqli_real_escape_string($db, $final_details);
        $s_waktu     = mysqli_real_escape_string($db, $waktu);
        $s_ip        = mysqli_real_escape_string($db, $current_ip);
        $s_url       = mysqli_real_escape_string($db, $current_url);
        $s_ua        = mysqli_real_escape_string($db, $user_agent);
        $s_dur       = mysqli_real_escape_string($db, $duration);

        // 1. WRITE TO usera_log (Synchronized Schema)
        $sql1 = "INSERT INTO usera_log (user, nama, waktu, ip, info) 
                 VALUES ('$s_uid', '$s_unm', '$s_waktu', '$s_ip', '$s_info')";
        $res1 = mysqli_query($db, $sql1);

        // 2. WRITE TO activity_logs (Synchronized Schema)
        $sql2 = "INSERT INTO activity (user_id, user_name, action, details, url, ip, user_agent, duration) 
                 VALUES ('$s_uid', '$s_unm', '$s_act', '$s_det', '$s_url', '$s_ip', '$s_ua', '$s_dur')";
        $res2 = mysqli_query($db, $sql2);

        // --- NOTE: Central log (log_sad) can be added here if needed ---
        
        return ($res1 || $res2);
    }
}
?>
