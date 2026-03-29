<?php
include "../config/konek.php";

$in  = 'in/';
$out = 'out/';

// Pastikan folder ada
if (!is_dir($out)) {
    mkdir($out, 0777, true);
}

$files = scandir($in);

foreach ($files as $file) {
    // Lewati navigasi folder (. dan ..)
    if ($file == '.' || $file == '..') {
        continue;
    }

    $tmp = explode(".", $file);
    $file_ext = end($tmp);
    
    // Ambil nama file tanpa ekstensi dulu
    $filename_raw = pathinfo($file, PATHINFO_FILENAME);

    // --- REVISI: PENCARIAN & PEMBERSIHAN ---
    // 1. Ubah titik (.) menjadi spasi agar kata tidak tergabung
    $nama = str_replace('.', ' ', $filename_raw);

    // 2. Hapus angka (0-9) dari string
    $nama = preg_replace('/[0-9]/', '', $nama);
    
    // 3. Hapus spasi berlebih (jika ada double spasi) dan spasi di awal/akhir
    $nama = preg_replace('/\s+/', ' ', $nama);
    $nama = trim($nama); 

    // Escape string untuk keamanan database
    $nama_esc = mysqli_real_escape_string($sqlconn, $nama);

    // Cari di database berdasarkan nama yang sudah dibersihkan
    $sqld = mysqli_query($sqlconn, "SELECT * FROM user WHERE nama = '$nama_esc'");
    $d    = mysqli_fetch_array($sqld);

    if ($d) {
        $nisp = $d['userid'];
        $cek  = $d['nama'];

        // --- REVISI: UBAH EXTENSION MENJADI .JPG ---
        // Apapun inputnya, outputnya dipaksa jadi .jpg
        $new_name = $nisp . '.jpg';

        if ($nama == $cek) {
            // Proses Copy
            if (copy($in . $file, $out . $new_name)) {
                echo "File: <b>$file</b> -> Dibaca sbg: <b>$nama</b> -> Disimpan: <b>$new_name</b> (OK)<br/>";
            } else {
                echo "Gagal copy file: $file<br/>";
            }
        } else {
            // Case sensitive mismatch
            echo "Nama '$nama' ditemukan tapi case tidak cocok dengan database.<br/>";
        }
    } else {
        echo "File: <b>$file</b> (Dibaca sbg: <b>$nama</b>) - Tidak Ada di database<br/>";
    }
}
?>
