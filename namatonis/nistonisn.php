<?php
include "../cfg/konek.php";

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

    // --- PEMBERSIHAN NAMA DARI FILE ---
    // 1. Ubah titik (.) menjadi spasi
    $nama = str_replace('.', ' ', $filename_raw);

    // 2. Hapus spasi berlebih
    $nama = preg_replace('/\s+/', ' ', $nama);
    $nama = trim($nama); 

    // Escape string untuk keamanan database
    $nama_esc = mysqli_real_escape_string($sqlconn, $nama);

    // Cari di database berdasarkan NIS (Exact Match atau Like)
    $sqld = mysqli_query($sqlconn,"SELECT pd, nis, nisn FROM siswa WHERE nis = '$nama_esc' OR nis LIKE '$nama_esc%' LIMIT 1");
    $d    = mysqli_fetch_array($sqld);

    if ($d) {
        $pd_db   = $d['pd'];
        $nis_db  = $d['nis'];
        $nisn_db = $d['nisn'];

        // --- REVISI: PENAMAAN BARU ([NAMA] _ NISN.pdf) ---
        
        // Membersihkan nama dari karakter ilegal untuk nama file
        $nama_file_aman = preg_replace('/[^A-Za-z0-9 _-]/', '', $pd_db);
        
        // Format: [NAMA] _ NISN.jpg
        $new_name = $nisn_db . '.jpg';

        // Validasi: Apakah nama file (NIS) cocok dengan NIS di database
        if ($nama == $nis_db) {
            // Proses Copy
            if (copy($in . $file, $out . $new_name)) {
                echo "<div style='color:green'>File: <b>$file</b> <br/>Nama Siswa: <b>$pd_db</b> <br/>Disimpan: <b>$new_name</b> (SUKSES)</div><hr/>";
            } else {
                echo "<div style='color:red'>Gagal copy file: $file</div><hr/>";
            }
        } else {
            echo "<div style='color:orange'>NIS file '<b>$nama</b>' ditemukan, tapi tidak cocok persis dengan database '<b>$nis_db</b>'.</div><hr/>";
        }
    } else {
        echo "<div style='color:grey'>File: <b>$file</b> (NIS: <b>$nama</b>) - Tidak Ada di database</div><hr/>";
    }
}
?>