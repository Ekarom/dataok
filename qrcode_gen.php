<?php
// qrcode_gen.php
// Helper untuk menghasilkan gambar QR Code secara lokal
// Membutuhkan library phpqrcode/qrlib.php
// Pastikan library ada
if (file_exists("phpqrcode/qrlib.php")) {
    include "phpqrcode/qrlib.php";
} else {
    // Jika library tidak ditemukan, coba cari di folder lain atau mati
    if (file_exists("lib/phpqrcode/qrlib.php")) {
        include "lib/phpqrcode/qrlib.php";
    } else {
        die("Error: Library phpqrcode tidak ditemukan.");
    }
}
// Ambil data dari parameter URL
if (isset($_GET['data'])) {
    $data = $_GET['data'];
    
    // Validasi data (opsional)
    if (empty($data)) {
        die("Error: Data tidak boleh kosong.");
    }
    // Tampilkan gambar PNG langsung
    // Parameters: text, outfile (false=direct output), level, size, margin
    QRcode::png($data, false, QR_ECLEVEL_L, 6, 2);
} else {
    echo "No data specified.";
}
?>
