<?php
session_start();

include "cfg/konek.php";
include "cfg/secure.php";
include "cfg/tapel.php"; // Ensure functions are available

// Ambil daftar kelas untuk modal daftar hadir
$dh_kelas_list = [];
$rk_dh = @mysqli_query($sqlconn, "SELECT DISTINCT kelas FROM siswa WHERE kelas != '' ORDER BY kelas ASC");
if ($rk_dh) { while ($k = mysqli_fetch_assoc($rk_dh)) { $dh_kelas_list[] = $k['kelas']; } }



$user = $_SESSION['skradm'];

// mengambil data berdasarkan id
// dan menampilkan data ke dalam form modal bootstrap
$sqlp = mysqli_query($sqlconn, "SELECT * FROM usera WHERE userid = '$user'");
$p = mysqli_fetch_array($sqlp);
$poto = $p['poto'] ?? '';
$lv = $p['level'] ?? '';
$nuser = $p['userid'] ?? '';
$nama = $p['nama'] ?? '';
$passworddb = $p['password'] ?? '';

$sqlp = mysqli_query($sqlconn, "SELECT * FROM siswa WHERE pd = '$user'");
$p = mysqli_fetch_array($sqlp);
$photo = $p['photo'] ?? '';

// Check for default password (smpn171**) OR Username as Password
$triggerForceChange = false;
$default_pass = 'smpn171**';

// Check if password matches default OR matches username (common initial setup)
if (password_verify($default_pass, $passworddb) || 
    $passworddb === md5($default_pass) || 
    password_verify($user, $passworddb) || 
    $passworddb === md5($user)) {
    $triggerForceChange = true;
}

// Redirect or block module access if force change is needed (optional, effectively handled by modal)
// But we want to ensure they can't simply ignore the modal via inspector, so maybe strictness?
// For now, the modal is backdrop static, which is good enough for UI level enforcement.


?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Staff AD | Dashboard</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="">
    
    <!-- Vendor CSS -->
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Select2 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <!-- AdminLTE Theme -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- jQuery UI -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script> 
    <!-- Select2 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <style>
        /* ==========================================
           FORM VALIDATION STYLES
           ========================================== */
        .warna:valid {
            background-color: #18c5dbff;
        }
        
        .custom {
            width: 200px !important;
        }
        
        /* ==========================================
           SIDEBAR & NAVBAR STYLES
           ========================================== */
        .main-sidebar {
            background: linear-gradient(180deg, #2c3e50 0%, #01b2d1ff 100%) !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }

        .nav-sidebar .nav-item {
            margin-bottom: 5px;
        }

        .nav-sidebar .nav-link {
            border-radius: 10px !important;
            color: #ecf0f1 !important;
            transition: all 0.3s ease;
        }

        .nav-sidebar .nav-link:hover, .nav-sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.2) !important;
            transform: translateX(5px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .nav-sidebar .nav-icon {
            color: #fff !important;
            opacity: 0.8;
            }

              
        .brand-link {
            border-bottom: 1px solid rgba(255,255,255,0.1) !important;
            text-decoration: none !important;
        }

        .user-panel {
            border-bottom: 1px solid rgba(255,255,255,0.1) !important;
        }

        .user-panel a {
            text-decoration: none !important;
        }
        
        /* Global Menu Gradient Class */
        .bg-menu-gradient {
            background: linear-gradient(180deg, #2c3e50 0%, #01b2d1ff 100%) !important;
            color: #fff !important;
        }       

        /* Nav Tabs Styling */
        .nav-tabs .nav-link.active {
            background: linear-gradient(180deg, #2c3e50 0%, #01b2d1ff 100%) !important;
            color: #fff !important;
            border-color: #dee2e6 #dee2e6 #fff;
        }

        /* Table Header Styling */
        table thead th {
            background: linear-gradient(180deg, #2c3e50 0%, #01b2d1ff 100%) !important;
            color: #fff !important;
            border-color: #5682fc;
            text-align: center;
        }

        table td {
            text-align: center;
        }

        .modal-header {
            padding: 9px 15px;
            border-bottom: 1px solid #eee;
            background-color: white;
            border-top-left-radius: 5px;
            border-top-right-radius: 5px;
        }
        .bg-1 {
            background: linear-gradient(180deg,  #2c3e50 25%, #01d112ff 100%);
            color: #fff !important;
        }
        .bg-2 {
            background: linear-gradient(180deg,  #2c3e50 25%, #01b2d1ff 100%);
            color: #fff !important;
        }
        .bg-3 {
            background: linear-gradient(180deg,  #2c3e50 25%, #1900ffff 100%);
            color: #fff !important;
        }
        .bg-4 {
            background: linear-gradient(180deg,  #2c3e50 25%, #ffee00ff 100%);
            color: #fff !important;
        }
        .bg-5 {
            background: linear-gradient(180deg,  #2c3e50 25%, #ff0000ff 100%);
            color: #fff !important;
        }
        .icon {
             background-color: transparent !important;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
 <div class="wrapper">
    <!-- Navbar -->
<nav class="main-header navbar navbar-expand bg-menu-gradient">
      <!-- Left navbar links -->
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" <?php echo !$triggerForceChange ? 'data-widget="pushmenu" href="#" role="button"' : 'style="cursor: default;"'; ?>><i class="fas fa-bars"></i></a>
        </li>
       </ul>
      <li class="nav-item d-none d-sm-inline-block">
            Tahun Pelajaran: <?php echo isset($tapel) ? $tapel : '-'; ?> | Semester: <?php if (isset($semester) && $semester == 1) { echo 'Ganjil'; } else { echo 'Genap'; } ?>
        </li>
      </ul>
            
            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <script type='text/javascript'>
                        var months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                        var myDays = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                        var date = new Date();
                        var day = date.getDate();
                        var month = date.getMonth();
                        var thisDay = date.getDay();
                        thisDay = myDays[thisDay];
                        var yy = date.getYear();
                        var year = (yy < 1000) ? yy + 1900 : yy;
                        document.write(thisDay + ', ' + day + ' ' + months[month] + ' ' + year + ', ');
                    </script>
                    <time id="clock"></time>
                             <script>
            (function () {
              var clock = document.getElementById('clock');
              setInterval(function () {
                var time = new Date().toString().split(' ')[4];
                clock.innerHTML = time;
              }, 13);
            })();
          </script>
                </li>
            </ul>
            
      <!-- START: Interactive Tapel Check -->
      <?php

      
      $show_tapel_modal = false;
      $expected = get_expected_tapel();
      
      // Check if this expected tapel/smt exists
      if (!check_tapel_exists($sqlconn, $expected['tapel'], $expected['smt'])) {
          $show_tapel_modal = true;
          $new_tapel = $expected['tapel'];
          $new_smt = $expected['smt'];
          $new_tahun = $expected['tahun'];
      }
      ?>
      
      <?php if($show_tapel_modal): ?>
      <script>
        $(document).ready(function(){
            // Append modal to body to fix backdrop issue
            $('#modalNewTapel').appendTo('body').modal('show');
            
            $('#btnCreateTapel').click(function(){
                var tapel = '<?php echo $new_tapel; ?>';
                var smt = '<?php echo $new_smt; ?>';
                var tahun = '<?php echo $new_tahun; ?>';
                
                $.ajax({
                    type: 'POST',
                    url: 'create_tapel.php',
                    data: {tapel: tapel, smt: smt, tahun: tahun},
                    success: function(response){
                        if(response.trim() == "success"){
                            alert("Tahun Pelajaran Baru Berhasil Dibuat dan Diaktifkan! Silahkan Login Ulang untuk memperbaharui sesi.");
                            window.location.href = 'exit.php';
                        } else {
                            alert("Gagal: " + response);
                        }
                    },
                    error: function(){
                        alert("Terjadi kesalahan koneksi.");
                    }
                });
            });
        });
      </script>
      
      <!-- Modal New Tapel -->
      <div class="modal fade" id="modalNewTapel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document">
          <div class="modal-content ">
            <div class="modal-header bg-info">
              <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-calendar-alt"></i> Deteksi Tahun Pelajaran Baru</h5>
            </div>
            <div class="modal-body">
              <p>Sistem mendeteksi bahwa saat ini sudah memasuki periode:</p>
              <h3>Tahun Pelajaran: <b><?php echo $new_tapel; ?></b></h3>
              <h3>Semester: <b><?php echo $new_smt == '1' ? '1 (Ganjil)' : '2 (Genap)'; ?></b></h3>
              <p>Data ini belum ada di database. Apakah Anda ingin membuatnya dan mengaktifkannya sekarang?</p>
            </div>
            <div class="modal-footer justify-content-between"> 
               <!-- User cannot easily close without action, mostly forced or just close if they want to ignore but backdrop static prevents accidental close -->
              <button type="button" class="btn btn-outline-light" data-dismiss="modal">Nanti Saja</button>
              <button type="button" class="btn btn-outline-light" id="btnCreateTapel"><b>Ya, Buat & Aktifkan</b></button>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>
      <!-- END: Interactive Tapel Check -->
        </nav>
        
        <!-- ==========================================
             MAIN SIDEBAR
             ========================================== -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="?" class="brand-link d-flex flex-column align-items-center text-center py-3">
                <img src="images/logo.png" alt="smpn171" class="brand-image img-circle elevation-3 mb-2"
                    style="opacity: .7; float: none; margin-left: 0;">
                <span class="brand-text font-weight-light text-wrap" style="line-height: 1.2;">Sistem Arsip Sekolah</span>
            </a>
            
            <!-- Sidebar -->
            <div class="sidebar">
                <!-- User Panel -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <?php
                        if (!empty($poto) && file_exists("images/$poto")) {
                            echo "<img src='images/$poto' class='img-circle elevation-2' alt='User Photo'>";
                        } else {
                            echo "<img src='images/default.png' class='img-circle elevation-2' alt='Default Photo'>";
                        }
                        ?>
                    </div>
                    <div class="info">
                        <?php
                        if ($nama !== "") {
                            if (!$triggerForceChange) {
                                echo "<a href='?modul=Profile' class='d-block'>" . $nama . "</a>";
                            } else {
                                echo "<span class='d-block text-white'>" . $nama . "</span>";
                            }
                        } else {
                            echo "<a class='d-block'>Error</a>";
                        }
                        ?>
                    </div>
                </div>
                
                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <?php if (!$triggerForceChange) { ?>
                        <!-- Main Menu Header -->
                        <li class="nav-header">MENU UTAMA</li>
                        
                        <!-- Data Siswa (Admin Only) -->
                        <?php if ($lv == "1") { ?>
                        <li class="nav-item">
                            <a href="?siswa" class="nav-link" data-toggle="mn" id="1">
                                <i class="nav-icon fas fa-address-card"></i>
                                <p>Data Siswa</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../compress" class="nav-link" target="_blank">
                                <i class=" nav-icon fas fa-tools"></i>
                                <p>SAD PDF</p>
                            </a>
                        </li>
                        <?php } ?>
                        
                        <!-- Arsip Data Menu -->
                        <li class="nav-item has-treeview">
                            <a href="#" class="nav-link" data-toggle="mn" id="2">
                                <i class="nav-icon fas fa-file-archive"></i>
                                <p>
                                    Arsip Data
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="?press" class="nav-link" data-toggle="mn" id="3">
                                        <i class="nav-icon fas fa-trophy"></i>
                                        <p>Data Prestasi</p>
                                    </a>
                                </li>
                                <!--<li class="nav-item">
                                    <a href="?modul=usulan" class="nav-link" data-toggle="mn" id="4">
                                        <i class="nav-icon fas fa-file"></i>
                                        <p>Data Usulan</p>
                                    </a>
                                </li>--->
                                <li class="nav-item">
                                    <a href="?legalisir" class="nav-link" data-toggle="mn" id="5">
                                        <i class="nav-icon fas fa-stamp"></i>
                                        <p>Legalisir</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        <!-- Administrator Section -->
                        <li class="nav-header">ADMINISTRATOR</li>
                        <!-- Print Menu -->
                        <li class="nav-item has-treeview">
                            <a href="#" class="nav-link" data-toggle="mn" id="6">
                                <i class="nav-icon fas fa-print"></i>
                                <p>
                                    Print
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="#" class="nav-link" data-toggle="modal" data-target="#modalDaftarHadir" id="7">
                                        <i class="nav-icon fas fa-clipboard-list"></i>
                                        <p>Daftar Hadir</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href='?press&aksi=laporan' class="nav-link" data-toggle="mn" id="8">
                                        <i class="nav-icon fas fa-clipboard-list"></i>
                                        <p>Laporan Prestasi</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <!-- Management Menu -->
                        <?php if ($lv == "1" || $lv == "2") { ?>
                        <li class="nav-item has-treeview">
                            <a href="#" class="nav-link" data-toggle="mn" id="9">
                                <i class="nav-icon fas fa-layer-group"></i>
                                <p>
                                    Management
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <?php if ($lv == "1") { ?>
                                <li class="nav-item">
                                    <a href="?user" class="nav-link" data-toggle="mn" id="10">
                                        <i class="nav-icon fas fa-users-cog"></i>
                                        <p>User Staff & Admin</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="?datasek" class="nav-link" data-toggle="mn" id="11">
                                        <i class="nav-icon fas fa-school"></i>
                                        <p>Data Sekolah</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="?settings" class="nav-link" data-toggle="mn" id="12">
                                        <i class="nav-icon fas fa-cogs"></i>
                                        <p>Settings</p>
                                    </a>
                                </li>
                               <?php } ?>
                               
                                <li class="nav-item">
                                    <a href="?profile" class="nav-link" data-toggle="mn" id="13">
                                        <i class="nav-icon fas fa-user-edit"></i>
                                        <p>Profile</p>
                                    </a>
                                </li>
                                
                                <?php if ($lv == "1") { ?>
                                <li class="nav-item">
                                    <a href="?uploadsiswa" class="nav-link" data-toggle="mn" id="14">
                                        <i class="nav-icon fas fa-file-excel"></i>
                                        <p>Upload Excel Siswa</p>
                                    </a>    
                                </li>
                                <li class="nav-item">
                                    <a href="?modul=uploaduser" class="nav-link" data-toggle="mn" id="15">
                                        <i class="nav-icon fas fa-file-excel"></i>
                                        <p>Upload Data User</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="?modul=uploadfoto" class="nav-link" data-toggle="mn" id="16">
                                        <i class="nav-icon fas fa-images"></i>
                                        <p>Upload Foto (ZIP)</p>
                                    </a>
                                </li>
                                <?php } ?>
                                
                            </ul>
                        </li>
                        <?php } ?>
                        
                         <?php if ($lv == "1") { ?>
                        <!-- System Menu (Admin Only) -->
                        <li class="nav-item has-treeview">
                            <a href="#" class="nav-link" data-toggle="mn" id="17">
                                <i class="nav-icon fas fa-cogs"></i>
                                <p>
                                    System
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="?brd" class="nav-link" data-toggle="mn" id="18">
                                        <i class="nav-icon fas fa-database"></i>
                                        <p>Database</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="?checkupdate" class="nav-link" data-toggle="mn" id="19">
                                        <i class="nav-icon fas fa-sync-alt"></i>
                                        <p>Check Update</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="?activity" class="nav-link" data-toggle="mn" id="20">
                                        <i class="fas fa-chart-line nav-icon"></i>
                                        <p>Activity Log</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <?php } ?>
                        
                        <!-- Logout -->
                        <li class="nav-item">
                            <a href="exit.php" class="nav-link">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                <p>Exit</p>
                            </a>
                        </li>
                        <?php } ?>
                    </ul>
                    <br>
                    <center><i class="fa fa-database text-warning"> Database <?php echo $database; ?></i></center>
                    <br />
                </nav>
            </div>
        </aside>
        
        <!-- ==========================================
             CONTENT WRAPPER - MODULE ROUTING
             ========================================== -->
        <?php
        if (isset($triggerForceChange) && $triggerForceChange) {
            include "force_change_pass_card.php";
        } else if (isset($_GET['press'])) {
            include "prestasifix.php";
        } else if (isset($_GET['usulan'])) {
            include "usulan.php";
        } else if (isset($_GET['user'])) {
            include "user.php";
        } else if (isset($_GET['legalisir'])) {
            include "legalisir.php";
        } else if (isset($_GET['brd'])) {
            include "brd.php";
        } else if (isset($_GET['dh'])) {
            include "daftar_hadir.php";
        } else if (isset($_GET['siswa'])) {
            include "siswa.php";
        } else if (isset($_GET['uploadsiswa'])) {
            include "upload_siswa.php";
        } else if (isset($_GET['uploadfoto'])) {
            include "upload_foto.php";
        } else if (isset($_GET['uploaduser'])) {
            include "upload_user.php";
        } else if (isset($_GET['profile'])) {
            include "profil.php";
        } else if (isset($_GET['datasek'])) {
            include "datasek.php";
        } else if (isset($_GET['settings'])) {
            include "setting.php";
        } else if (isset($_GET['checkupdate'])) {
            include "chckupdate.php";
        } else if (isset($_GET['activity'])) {
            include "activity_log.php";
        } else if (isset($_GET['logout'])) {
            include "ceklogout.php";
        } else {
            include "load.php";
        }
        ?>
        
        <!-- ==========================================
             FOOTER
             ========================================== -->
        <footer class="main-footer">
            <center>
                <strong>S.A.D <?php echo $ver; ?> - Copyright &copy;2025</strong>
            </center>
            <div class="float-right d-none d-sm-inline-block">
                <right></right>
            </div>
        </footer>
    </div>
    <!-- ./wrapper -->
    
    <!-- ==========================================
         JAVASCRIPT LIBRARIES
         ========================================== -->
    <script>
        $.widget.bridge('uibutton', $.ui.button)
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
    <!-- Chart.js -->
    <script src="plugins/chart.js/Chart.min.js"></script>
    <!-- Toastr -->
    <script src="plugins/toastr/toastr.min.js"></script>
    
    <!-- ==========================================
         CUSTOM SCRIPTS
         ========================================== -->

   <!-- ==========================================
         MODAL PILIH KELAS - DAFTAR HADIR
         ========================================== -->
    <style>
        #modalDaftarHadir .modal-header { background: linear-gradient(135deg,#2c3e50 0%,#01b2d1 100%); color:#fff; border-radius: 4px 4px 0 0; }
        #modalDaftarHadir .modal-header .close { color:#fff; opacity:.8; }
        #modalDaftarHadir .modal-header .close:hover { opacity:1; }
        #modalDaftarHadir .form-label-sm { font-size:12px; font-weight:600; color:#475569; text-transform:uppercase; letter-spacing:.4px; margin-bottom:4px; display:block; }
        #modalDaftarHadir .form-control { border-radius:8px; border:1.5px solid #cbd5e1; font-size:13px; }
        #modalDaftarHadir .form-control:focus { border-color:#01b2d1; box-shadow:0 0 0 3px rgba(1,178,209,.15); }
        #modalDaftarHadir .btn-buka { background:linear-gradient(135deg,#2c3e50 0%,#01b2d1 100%); color:#fff; border:none; border-radius:8px; padding:10px 24px; font-weight:700; font-size:14px; transition:opacity .2s; }
        #modalDaftarHadir .btn-buka:hover { opacity:.88; color:#fff; }
        #modalDaftarHadir .preview-badge { display:inline-flex; align-items:center; gap:5px; background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; border-radius:6px; padding:4px 10px; font-size:11px; font-weight:600; }
    </style>

    <div class="modal fade" id="modalDaftarHadir" tabindex="-1" role="dialog" aria-labelledby="lblDaftarHadir">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="lblDaftarHadir">
                        <i class="fas fa-clipboard-list mr-2"></i>Cetak Daftar Hadir Siswa
                    </h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">

                    <!-- Pilih Kelas -->
                    <div class="form-group mb-3">
                        <label class="form-label-sm"><i class="fas fa-chalkboard-teacher mr-1"></i>Pilih Kelas</label>
                        <select id="dhKelasVal" class="form-control">
                            <option value="">Semua Kelas</option>
                            <?php foreach ($dh_kelas_list as $kl): ?>
                            <option value="<?= htmlspecialchars($kl) ?>"><?= htmlspecialchars($kl) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Tanggal otomatis hari ini -->
                    <input type="hidden" id="dhTanggal" value="<?= date('Y-m-d') ?>">

                    <!-- Pilih Jenis Cetakan -->
                    <div class="form-group">
                        <label class="form-label-sm"><i class="fas fa-file-alt mr-1"></i>Jenis Cetakan</label>
                        <select id="dhJenisCetak" class="form-control">
                            <option value="Daftar Hadir Siswa">Daftar Hadir Siswa</option>
                            <option value="Tanda Terima Kartu Pelajar RFID">Tanda Terima Kartu Pelajar</option>
                        </select>
                    </div>
                </div><!-- /modal-body -->
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnBukaDH">
                        <i class="fas fa-print mr-1"></i>Tampilkan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function () {
        // -- Tombol Tampilkan
        $('#btnBukaDH').on('click', function () {
            var kelas = $('#dhKelasVal').val();
            var tgl   = $('#dhTanggal').val() || '<?= date('Y-m-d') ?>';
            var judul = $('#dhJenisCetak').val();

            var url = 'daftar_hadir.php?kelas=' + encodeURIComponent(kelas)
                    + '&tanggal=' + encodeURIComponent(tgl)
                    + '&judul=' + encodeURIComponent(judul);

            window.open(url, '_blank');
            $('#modalDaftarHadir').modal('hide');
        });
    });
    </script>
    <script>
    $(document).ready(function () {
      $('a[data-toggle="mn"]').click(function () {
        var id = $(this).attr("id");

        $('#' + id).siblings().find(".active").removeClass("active");
        $('#' + id).addClass("active");
        localStorage.setItem("activeMenu", id);
      });
      var activeMenu = localStorage.getItem('activeMenu');

      if (activeMenu != null) {
        $('#' + activeMenu).siblings().find(".active").removeClass("active");
        $('#' + activeMenu).addClass("active");
      }
    });

    if ($.fn.draggable) {
      $('.modal-dialog').draggable({
        handle: ".modal-header"
      });
    }
    </script>
    <script>
            // ==========================================
            // CLOCK & DATE UPDATE
            // ==========================================
            // Validasi elemen clock sebelum akses
            var clock = document.getElementById('clock');
            var dateDisplay = document.getElementById('date-display');
            
            function updateTime() {
                var now = new Date();
                
                // Update Time
                if(clock) {
                     var timeString = now.toTimeString().split(' ')[0];
                     clock.textContent = timeString;
                }
                
                // Update Date (Replacement for document.write)
                if(dateDisplay) {
                     var months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                     var myDays = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                     
                     var dayName = myDays[now.getDay()];
                     var day = now.getDate();
                     var monthName = months[now.getMonth()];
                     var year = now.getFullYear();
                     
                     dateDisplay.textContent = dayName + ', ' + day + ' ' + monthName + ' ' + year;
                }
            }
            
            // Init and Interval
            updateTime();
            setInterval(updateTime, 1000);
    </script>
    <script>
      $(document).ready(function () {
    $('#pres').DataTable({
      responsive: true,
      autoWidth: true

    });
  });

   $(document).ready(function () {
    $('#us').DataTable({
      responsive: true,
      autoWidth: true

    });
  });
   $(document).ready(function () {
    $('#leg').DataTable({
      responsive: true,
      autoWidth: true

    });
  });
</script>
</body>
</html>
<?php
// End of file
?>