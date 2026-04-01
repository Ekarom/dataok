<?php

if (!isset($_SESSION['skradm'])) {
  $query_string = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
  header("Location: login" . $query_string);
  exit();
} else {
  // Ensure connection exists
  if (!isset($sqlconn) || !$sqlconn) {
     // If included from a context where connection failed, stop here
     die("Database connection missing in secure.php"); 
  }

  $cekv = mysqli_query($sqlconn, "SELECT * FROM version ORDER BY id DESC LIMIT 1");
  if (!$cekv) {
    // die('Could not query:' . mysqli_error($sqlconn)); // Don't die, just warn
    error_log('Secure.php version query failed: ' . mysqli_error($sqlconn));
    $v = null;
  } else {
    $v = mysqli_fetch_array($cekv);
  }

  $ver = $v['ver'] ?? '1.0';
  $log = $v['logupdate'] ?? '';
  $tgl = $v['tgl'] ?? '';
  $kt = $v['ket'] ?? '';



  if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
  }
  //whether ip is from proxy
  elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
  }
  //whether ip is from remote address
  else {
    $ip = $_SERVER['REMOTE_ADDR'];
  }

  $browser = $_SERVER['HTTP_USER_AGENT'];


  $log = date("Y-m-d H:i:s");




  if (isset($_SESSION['skradm'])) {
    //header("Location: ./");


    $usc = $_SESSION['skradm'] ?? '';
    $tapel = $_SESSION['tapel'] ?? '-';
    $tahunsklh = $_SESSION['tahundb'] ?? '';
    // Ambil semester dari session
    $semester = $_SESSION['semester'] ?? '';





    date_default_timezone_set("Asia/Jakarta");
    $log = date("Y-m-d H:i:s");

    $tahun = date("Y");
    $tahunb = date("Y", strtotime("+1 year"));
    $tahunm = date("Y", strtotime("-3 year"));
    $tahunm8 = date("Y", strtotime("-2 year"));
    $tahunm7 = date("Y", strtotime("-1 year"));
    $tapels = "$tahunm7/$tahun";
    $tapelb = "$tahun/$tahunb";
    $userc = $_SESSION['skradm'];

    $getuser = mysqli_query($sqlconn, "select * from usera where userid='$userc'");
    $test = ($getuser && mysqli_num_rows($getuser) > 0) ? mysqli_fetch_array($getuser) : [];
    $level = $test['level'] ?? '';
    $lv = $level;
    $nama = $test['nama'] ?? '';
    $idu = $test['idu'] ?? '';

    $log4 = mysqli_query($sqlconn, "select COUNT(user) as n1 from usera_log where user='$userc' order by waktu desc");
    $log5 = ($log4 && mysqli_num_rows($log4) > 0) ? mysqli_fetch_array($log4) : ['n1' => 0];

    $log1 = mysqli_query($sqlconn, "select * from usera_log where user='$userc' order by waktu desc limit 25");

    // Prefer selecting the specific semester from session if available
    $semester_target = !empty($semester) ? $semester : '1'; 
    $gettpl = mysqli_query($sqlconn, "select * from tapel where tahun='$tahunsklh' AND smt='$semester_target'");
    
    // Fallback if specific semester not found (e.g. data missing)
    if (!$gettpl || mysqli_num_rows($gettpl) == 0) {
        $gettpl = mysqli_query($sqlconn, "select * from tapel where tahun='$tahunsklh' ORDER BY aktif DESC LIMIT 1");
    }

    if ($gettpl && mysqli_num_rows($gettpl) > 0) {
        $testtpl = mysqli_fetch_array($gettpl);
        $tpl = isset($testtpl['tapel']) ? $testtpl['tapel'] : '';
        $st = isset($testtpl['smt']) ? $testtpl['smt'] : '';
    } else {
        $tpl = '';
        $st = '';
    }


    if (!function_exists('tgl_indo')) {
      function tgl_indo($tanggal)
      {
        $bulan = array(
          1 => 'Januari',
          'Februari',
          'Maret',
          'April',
          'Mei',
          'Juni',
          'Juli',
          'Agustus',
          'September',
          'Oktober',
          'November',
          'Desember'
        );
        $pecahkan = explode('-', $tanggal);

        // variabel pecahkan 0 = tanggal
        // variabel pecahkan 1 = bulan
        // variabel pecahkan 2 = tahun

        return $pecahkan[2] . ' ' . $bulan[(int) $pecahkan[1]] . ' ' . $pecahkan[0];
      }
    }
  }
}
