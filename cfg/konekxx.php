<?php
// 1. Connect ke database
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ambil database dari cookie, atau gunakan default
//$db_to_use = isset($_COOKIE['database_asli']) ? $_COOKIE['database_asli'] : 'pnet_pd';
// Ambil nama database dari SESSION
$database = isset($_SESSION['database_asli']) ? $_SESSION['database_asli'] : 'dnet_ad';

// Lakukan koneksi ke database yang dipilih
//$sqlconn = new mysqli($server, $username, $password, $db_to_use);

$sqlconn = new mysqli("localhost", "root", "", $database);

// Check connection
if ($sqlconn->connect_errno) {
  echo "Failed to connect to MySQL: " . $sqlconn->connect_error;
  exit();
}

date_default_timezone_set("Asia/Jakarta");

///////// Profil Sekolah /////////  
// Check if profils table exists before querying
$table_check = @mysqli_query($sqlconn, "SHOW TABLES LIKE 'profils'");
$table_exists = ($table_check && mysqli_num_rows($table_check) > 0);

if ($table_exists) {
    $mysql = @mysqli_query($sqlconn, "select * from profils where id='1'");
    $g = ($mysql && mysqli_num_rows($mysql) > 0) ? mysqli_fetch_array($mysql) : null;
} else {
    $g = null;
    // Log warning for debugging
    error_log("Warning: Table 'profils' does not exist in database '{$database}'");
}

$namasek = $g["nsekolah"] ?? "";
$possek = $g["kodepos"] ?? "";
$alamatsek = $g["alamat"] ?? "";
$kelsek = $g["kelurahan"] ?? "";
$kecsek = $g["kecamatan"] ?? "";
$provsek = $g["provinsi"] ?? "";
$kabsek = $g["kabupaten"] ?? "";
$tlpsek = $g["no_telp"] ?? "";
$emailsek = $g["email"] ?? "";
$website = $g["website"] ?? "";
$kepsek = $g["kepsek"] ?? "";
$nipkepsek = $g["nipkepsek"] ?? "";
$pengawas = $g["pengawas"] ?? "";
$nippengawas = $g["nippengawas"] ?? "";
$kasi = $g["kasi"] ?? "";
$nipkasi = $g["nipkasi"] ?? "";
$sklogo = $g["logo_sekolah"] ?? "logo_default.png";
$skback = $g["background_login"] ?? "bg_default.jpg";
$npsn = $g["npsn"] ?? "";

// Check if version table exists before querying
$version_table_check = @mysqli_query($sqlconn, "SHOW TABLES LIKE 'version'");
$version_table_exists = ($version_table_check && mysqli_num_rows($version_table_check) > 0);

if ($version_table_exists) {
    $mysqlv = @mysqli_query($sqlconn, "select * from version order by id desc limit 1");
    $v = ($mysqlv && mysqli_num_rows($mysqlv) > 0) ? mysqli_fetch_array($mysqlv) : null;
} else {
    $v = null;
}
$ver = $v["ver"] ?? "1.0";

// --- FUNGSI GENERATOR SEKOLAH ---
if (!function_exists('generateVariasiSekolah')) {
  function generateVariasiSekolah($input)
  {
    // 1. Ubah ke huruf besar semua & hilangkan spasi ganda
    $temp = strtoupper($input);
    $temp = preg_replace('/\s+/', ' ', $temp);

    // 2. Standarisasi kata "SEKOLAH PERTAMA" menjadi "SMP"
    $temp = str_replace("SEKOLAH PERTAMA", "SMP", $temp);

    // 3. Standarisasi singkatan "SMPN" menjadi "SMP NEGERI"
    $temp = preg_replace('/\bSMPN\b/', 'SMP NEGERI', $temp);

    $basis = trim($temp);

    // --- VARIABEL HASIL ---
    $namapendekbesar = $basis;

    $namapendekkecil = ucwords(strtolower($basis));
    $namapendekkecil = str_replace("Smp ", "SMP ", $namapendekkecil);

    $namapanjangbesar = str_replace("SMP", "SEKOLAH MENENGAH PERTAMA", $basis);
    $namapajangkecil = ucwords(strtolower($namapanjangbesar));

    if (strpos($basis, 'NEGERI') !== false) {
      $singkatan = str_replace("SMP NEGERI", "SMPN", $basis);
    } else {
      $singkatan = $basis;
    }
    if (strpos($singkatan, 'SMPN') !== false) {
      //  $namapendeknegerikecil = ucwords(strtolower($singkatan));
      //$namapendeknegerikecil = str_replace("SMPN", "SMP Negeri", $singkatan);

      $namapendeknegeri = str_replace("SMPN", "SMP Negeri", $singkatan);
      $namapendeknegeribesar = strtoupper($namapendeknegeri);
    } else {
      $namapendeknegeri = $basis;
      $namapendeknegeribesar = strtoupper($namapendeknegeri);

    }

    return [
      'namapanjangbesar' => $namapanjangbesar,
      'namapajangkecil' => $namapajangkecil,
      'namapendekbesar' => $namapendekbesar,
      'namapendekkecil' => $namapendekkecil,
      'namapendeknegeri' => $namapendeknegeri,
      'namapendeknegeribesar' => $namapendeknegeribesar,
      'singkatan' => $singkatan
    ];
  }
}

// --- JALANKAN FUNGSI SEKOLAH ---
$smpn = generateVariasiSekolah($namasek);

$namapanjangbesar = $smpn['namapanjangbesar'];
$namapajangkecil = $smpn['namapajangkecil'];
$namapendekbesar = $smpn['namapendekbesar'];
$namapendekkecil = $smpn['namapendekkecil'];
$namapendeknegeri = $smpn['namapendeknegeri'];
$namapendeknegeribesar = $smpn['namapendeknegeribesar'];
$singkatan = $smpn['singkatan'];


// --- FUNGSI BANTUAN (Tetap di luar) ---
if (!function_exists('fixCapitalization')) {
  function fixCapitalization($text)
  {
    $text = ucwords(strtolower($text));
    $text = str_replace("Dki ", "DKI ", $text);
    $text = preg_replace('/\bDi\s/', 'DI ', $text);
    return $text;
  }
}

// --- FUNGSI GENERATOR PROVINSI (UPDATED) ---
if (!function_exists('generateVariasiProvinsi')) {
  function generateVariasiProvinsi($input)
  {
    // 1. Normalisasi awal
    $temp = strtoupper($input);
    $temp = preg_replace('/\s+/', ' ', $temp);
    $temp = trim($temp);

    // Hapus kata PROVINSI
    $temp = preg_replace('/^PROVINSI\s+/', '', $temp);

    // Standarisasi D.I. jadi DI
    $temp = str_replace(['D.I.', 'D.I'], 'DI', $temp);

    // 2. TENTUKAN BASIS PENDEK DULU (Nama Inti)
    // Kita hapus dulu embel-embel "DKI" atau "DI" jika user menuliskannya
    // Supaya kita dapat murni "JAKARTA" atau "YOGYAKARTA"
    $basis_pendek = str_replace(['DKI ', 'DI '], '', $temp);

    // 3. TENTUKAN BASIS LENGKAP (Rekonstruksi)
    // Cek nama intinya, jika Jakarta/Yogya, paksa tambah awalan
    if ($basis_pendek == 'JAKARTA') {
      $basis_lengkap = 'DKI JAKARTA';
    } elseif ($basis_pendek == 'YOGYAKARTA') {
      $basis_lengkap = 'DI YOGYAKARTA';
    } else {
      // Untuk provinsi lain (Jawa Barat, Bali, dll), nama lengkap = nama pendek
      $basis_lengkap = $basis_pendek;
    }

    // 4. OUTPUT
    return [
      // Versi LENGKAP (Otomatis ada DKI jika Jakarta)
      'provinsilengkapbesar' => $basis_lengkap,
      'provinsilengkapkecil' => fixCapitalization($basis_lengkap),

      // Versi PENDEK (Murni nama daerah)
      'provinsibesar' => $basis_pendek,
      'provinsikecil' => fixCapitalization($basis_pendek)
    ];
  }
}

// --- EKSEKUSI ---
// Asumsi $provsek dari database adalah "PROVINSI DKI JAKARTA"
$provinsi = generateVariasiProvinsi($provsek);

// Cara Memanggilnya:
$provinsilengkapbesar = $provinsi['provinsilengkapbesar']; // "DKI JAKARTA"
$provinsilengkapkecil = $provinsi['provinsilengkapkecil']; // "DKI Jakarta"
$provinsibesar = $provinsi['provinsibesar'];        // "JAKARTA"
$provinsikecil = $provinsi['provinsikecil'];        // "Jakarta"

$provinsilengkap = "Provinsi " . $provinsilengkapkecil;


if (!function_exists('generateVariasiWebsite')) {
  function generateVariasiWebsite($input)
  {
    // 1. Normalisasi & Regex (Sama seperti sebelumnya)
    $text = strtolower($input);
    $pattern = '/(?:https?:\/\/)?(?:www\.)?([a-z0-9.-]+\.[a-z]{2,})/i';
    preg_match_all($pattern, $text, $matches);

    $domains_found = $matches[1]; // Array hasil domain bersih

    // 2. Tentukan Website 1
    $web1 = isset($domains_found[0]) ? $domains_found[0] : "";

    // 3. Tentukan Website 2 (LOGIKA BARU)
    if (isset($domains_found[1])) {
      // Jika user memasukkan 2 website, ambil yang kedua
      $web2 = $domains_found[1];
    } else {
      // Jika user HANYA isi 1, maka website2 = website1
      $web2 = $web1;
    }

    // 4. Susun Websitelengkap (Untuk Tampilan)
    // Kita gunakan $domains_found asli untuk tampilan agar rapi.
    // Jika input cuma 1, tampilan tetap 1 link (tidak duplikat "link - link").
    // Tapi jika Anda ingin tampilannya juga duplikat, ubah logika di bawah ini.

    $list_lengkap = [];

    // Cek domain yang ditemukan secara unik untuk tampilan string
    if (!empty($domains_found)) {
      foreach ($domains_found as $d) {
        $list_lengkap[] = "https://" . $d;
      }
    } else {
      // Jika kosong sama sekali (input user ngaco)
      $list_lengkap[] = "-";
    }

    $websitelengkap = implode(" - ", $list_lengkap);

    // Perbaikan kecil: Jika web1 kosong, web2 juga harus string kosong (bukan null)
    if (empty($web1)) {
      $websitelengkap = "-";
      $web2 = "";
    }

    return [
      'websitelengkap' => $websitelengkap,
      'website1' => $web1,
      'website2' => $web2
    ];
  }
}
// --- CARA PENGGUNAAN (Implementasi) ---

// Asumsi $website diambil dari database (seperti di kode Anda sebelumnya)
// $website = $g["web"]; 

// Contoh Testing Data User yang aneh-aneh:
/*
$test_inputs = [
    "https://smpn171.sch.id - https://p171.net",  // Format Lengkap
    "smpn171.sch.id p171.net",                     // Pakai spasi, tanpa http
    "smpn171.sch.id",                              // Cuma satu
    "http://smpn171.sch.id/ - p171.net",           // Campur, ada slash di akhir
    "www.smpn171.sch.id, p171.net"                 // Pakai koma
];

foreach ($test_inputs as $val) {
    $hasilWeb = generateVariasiWebsite($val);
    
    // Tampilkan (Uncomment baris di bawah untuk melihat hasil)
    /*
    echo "Input Asli: $val <br>";
    echo "Lengkap: " . $hasilWeb['websitelengkap'] . "<br>";
    echo "Web 1  : " . $hasilWeb['website1'] . "<br>";
    echo "Web 2  : " . $hasilWeb['website2'] . "<br>";
    echo "<hr>";
    
}
*/
// --- IMPLEMENTASI KE VARIABLE FINAL ---
// Panggil fungsi menggunakan data dari database
$data_web = generateVariasiWebsite($website);

$websitelengkap = $data_web['websitelengkap'];
$website1 = $data_web['website1'];
$website2 = $data_web['website2'];

?>

