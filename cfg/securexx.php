<?php

if (!isset($_SESSION['skradm'])) {
  header("Location: ./");
} else {
  $cekv = mysqli_query($sqlconn, "SELECT * FROM version ORDER BY id DESC LIMIT 1");
  if (!$cekv) {
    die('Could not query:' . mysqli_error($sqlconn));
  }
  $v = mysqli_fetch_array($cekv);
  $ver = $v['ver'] ?? '1.0';
  $logupdate = $v['logupdate'] ?? '';
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


    $usc = $_SESSION['skradm'];
    $tapel = $_SESSION['tapel'];
    $tahunsklh = $_SESSION['tahundb'];
    // Ambil semester dari session
    $semester = isset($_SESSION['semester']) ? $_SESSION['semester'] : '';





    date_default_timezone_set("Asia/Jakarta");
    $log = date("Y-m-d H:i:s");

    $tahun = date("Y");
    $tahunb = date("Y", strtotime("+1 year"));
    $tahunm = date("Y", strtotime("-3 year"));
    $tahunm8 = date("Y", strtotime("-2 year"));
    $tahunm7 = date("Y", strtotime("-1 year"));
    $tapels = "$tahunm7/$tahun";
    $tapelb = "$tahun/$tahunb";
    // Check column name dynamically (userid vs username)
    $column_to_check = 'username';
    $check_col = mysqli_query($sqlconn, "SHOW COLUMNS FROM usera LIKE 'userid'");
    if ($check_col && mysqli_num_rows($check_col) > 0) {
        $column_to_check = 'userid';
    }
    $userc = $_SESSION['skradm'];

    $getuser = mysqli_query($sqlconn, "select * from usera where $column_to_check = '$userc'");
    $test = mysqli_fetch_array($getuser);
    $level = $test['level'] ?? '';
    $nama = $test['nama'] ?? '';
    $idu = $test['idu'] ?? '';

    $log4 = mysqli_query($sqlconn, "select COUNT(id) as n1 from usera_log where user='$userc'");
    $log5 = mysqli_fetch_array($log4);

    $log1 = mysqli_query($sqlconn, "select * from usera_log where user='$userc' order by waktu desc limit 25");

    $gettpl = mysqli_query($sqlconn, "select * from tapel where tahun='$tahunsklh'");
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
