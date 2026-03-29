<?php
// Environment Detection
$whitelist = ['127.0.0.1', '::1', 'localhost', '192.168.90.218'];
$rem_addr = $_SERVER['REMOTE_ADDR'] ?? ''; // Tambahkan check agar tidak error jika kosong
$is_local = in_array($rem_addr, $whitelist);

// Sebagai catatan: Karena kunci sudah disamakan, variabel $is_local saat ini 
// hanya bersifat informatif saja.
if ($is_local) {
    // Keys for Localhost Testing
    $recaptcha_site_key   = '6LfNEUEsAAAAAH1eBMbspHL3FwZjWuyb6gCE-EyL';
    $recaptcha_secret_key = '6LfNEUEsAAAAAFjgYVZ91QBceAZg09p7_1cLRkqN';
} else {
    // Keys for Production (Online)
    $recaptcha_site_key   = '6LfNEUEsAAAAAH1eBMbspHL3FwZjWuyb6gCE-EyL';
    $recaptcha_secret_key = '6LfNEUEsAAAAAFjgYVZ91QBceAZg09p7_1cLRkqN';
}
?>
