<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "cfg/konek.php";


// --- Ambil username & password dari session
$userc = $_SESSION['userid'] ?? '';
$passc = $_SESSION['password'] ?? '';

// --- Validasi user dari database
if (!empty($userc)) {
    $stmt = $sqlconn->prepare("SELECT * FROM usera WHERE userid=? LIMIT 1");
    $stmt->bind_param("s", $userc);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        $userc = $row['userid'];
        $passc = $row['password'];
    }
}

// --- Ambil data user lengkap
$getuser = $sqlconn->prepare("SELECT * FROM usera WHERE userid=? LIMIT 1");
$getuser->bind_param("s", $userc);
$getuser->execute();
$p = $getuser->get_result()->fetch_assoc(); // $p akan 'null' jika user tidak ditemukan/tidak login

// --- PERBAIKAN DARI WARNING PHP ---
// Gunakan '??' (Null Coalescing Operator) untuk mencegah error jika session tidak ada
// Ini akan memberikan nilai 'null' jika session-nya kosong.
$level = $_SESSION['level'] ?? null;
$id_user = $_SESSION['id_user_login'] ?? null;

// Sekarang aman untuk memeriksa level
if ($level == 'admin') {
    // ... lakukan sesuatu (contoh)
    // Sekarang ini aman dan tidak akan menghasilkan Warning
}
// ---------------------------------


?>
