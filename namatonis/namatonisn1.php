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

    // --- PEMBERSIHAN NAMA DARI FILE (LOGIKA LAMA TETAP DIPAKAI UNTUK PENCARIAN) ---
    // 1. Ubah titik (.) menjadi spasi
    $nama = str_replace('.', ' ', $filename_raw);

    // 2. Hapus angka (0-9) dari string (untuk pencarian nama murni)
    $nama = preg_replace('/[0-9]/', '', $nama);
    
    // 3. Hapus spasi berlebih
    $nama = preg_replace('/\s+/', ' ', $nama);
    $nama = trim($nama); 

    // Escape string untuk keamanan database
    $nama_esc = mysqli_real_escape_string($sqlconn, $nama);

    // Cari di database berdasarkan nama yang sudah dibersihkan
    $sqld = mysqli_query($sqlconn,"SELECT siswa.pd, siswa.nisn FROM siswa WHERE siswa.pd LIKE '%$nama_esc%' OR siswa.nisn LIKE '%$nama_esc%' LIMIT 1");
    $d    = mysqli_fetch_array($sqld);

    if ($d) {
        $nisn_db = $d['nisn'];
        $nama_db = $d['pd']; // Nama asli dari Database

        // --- REVISI: PENAMAAN BARU (NISN + NAMA + .pdf) ---
        
        // Membersihkan nama dari karakter ilegal untuk nama file (seperti / \ : * ? " < > |)
        $nama_file_aman = preg_replace('/[^A-Za-z0-9 _-]/', '', $nama_db);
        
        // Format: [NAMA] _ NISN.pdf
        $new_name = '['.strtoupper($nama_file_aman).']' . ' _ ' . $nisn_db . '.pdf';

        // Validasi kecocokan (Case Sensitive dari script asli Anda)
        // Jika Anda ingin tidak case sensitive, ubah jadi: if (strtoupper($nama) == strtoupper($nama_db))
        if ($nama == $nama_db) {
            // Proses Copy
            if (copy($in . $file, $out . $new_name)) {
                echo "<div style='color:green'>File: <b>$file</b> <br/>Match DB: <b>$nama_db</b> <br/>Disimpan: <b>$new_name</b> (SUKSES)</div><hr/>";
            } else {
                echo "<div style='color:red'>Gagal copy file: $file</div><hr/>";
            }
        } else {
            // Case sensitive mismatch atau nama mirip tapi tidak sama persis
            echo "<div style='color:orange'>Nama file '<b>$nama</b>' ditemukan, tapi tidak cocok persis dengan database '<b>$nama_db</b>'.</div><hr/>";
        }
    } else {
        echo "<div style='color:grey'>File: <b>$file</b> (Dibaca sbg: <b>$nama</b>) - Tidak Ada di database</div><hr/>";
    }
}
?>