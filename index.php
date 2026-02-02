<?php
session_start();

include "cfg/konek.php";
include "cfg/secure.php";
include "cfg/tapel.php"; // Ensure functions are available

$show_tapel_modal = false;
$expected = get_expected_tapel();

// Check if this expected tapel/smt exists
// MODIFIED: Only show modal if the user's CURRENT session period doesn't exist
// OR if they haven't explicitly chosen a period and the expected one is missing.
$session_tapel = $_SESSION['tapel'] ?? '';
$session_smt = $_SESSION['semester'] ?? '';

$tapel_to_check = $expected['tapel'];
$smt_to_check = $expected['smt'];

if (!empty($session_tapel) && !empty($session_smt)) {
    // If user has a session choice, check if THAT one exists (it should)
    if (!check_tapel_exists($sqlconn, $session_tapel, $session_smt)) {
        $show_tapel_modal = true;
        // If the user's chosen session period is missing, we should probably default to the expected one for the modal
        $new_tapel = $expected['tapel'];
        $new_smt = $expected['smt'];
        $new_tahun = $expected['tahun'];
    }
} else {
    // Fallback to expected period if no session choice
    if (!check_tapel_exists($sqlconn, $expected['tapel'], $expected['smt'])) {
        $show_tapel_modal = true;
        $new_tapel = $expected['tapel'];
        $new_smt = $expected['smt'];
        $new_tahun = $expected['tahun'];
    }
}

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
            margin: 0 5px;
        }

        .nav-sidebar .nav-link:hover, 
        .nav-sidebar .nav-link.active,
        .nav-sidebar .nav-link:focus {
            background: linear-gradient(-45deg, #2c3e50, #3e6ff8, #01b2d1ff, #2c3e50) !important;
            background-size: 400% 400% !important;
            transform: translateX(5px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border: 2px solid #fbff00ff !important;
            color: #fff !important;
        }

        .nav-sidebar .nav-icon {
            color: #fff !important;
            opacity: 0.9;
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

<body class="hold-transition sidebar-mini layout-fixed" style="height: auto;">
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
        </nav>
        
        <!-- ==========================================
             MAIN SIDEBAR
             ========================================== -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="?" class="brand-link d-flex flex-column align-items-center text-center py-3">
                <img src="images/logo_apk.png" alt="smpn171" class="brand-image img-circle elevation-3 mb-2"
                    style="opacity: .7; float: none; margin-left: 0;">
                <span class="brand-text font-weight-light text-wrap" style="line-height: 1.2;">Sistem Arsip Data</span>
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
                            <a href="?modul=siswa" class="nav-link" data-toggle="mn" id="1">
                                <i class="nav-icon fas fa-address-card"></i>
                                <p>Data Siswa</p>
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
                                    <a href="?modul=press" class="nav-link" data-toggle="mn" id="3">
                                        <i class="nav-icon fas fa-trophy"></i>
                                        <p>Data Prestasi</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="?modul=usulan" class="nav-link" data-toggle="mn" id="4">
                                        <i class="nav-icon fas fa-file"></i>
                                        <p>Data Usulan</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="?modul=legalisir" class="nav-link" data-toggle="mn" id="5">
                                        <i class="nav-icon fas fa-stamp"></i>
                                        <p>Legalisir</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        <!-- Administrator Section -->
                        <li class="nav-header">ADMINISTRATOR</li>
                        
                        <!-- Management Menu (Admin Only) -->
                        <?php if ($lv == "1") { ?>
                        <li class="nav-item has-treeview">
                            <a href="#" class="nav-link" data-toggle="mn" id="6">
                                <i class="nav-icon fas fa-layer-group"></i>
                                <p>
                                    Management
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="?modul=user" class="nav-link" data-toggle="mn" id="7">
                                        <i class="nav-icon fas fa-users-cog"></i>
                                        <p>User Staff & Admin</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="?modul=settings" class="nav-link" data-toggle="mn" id="8">
                                        <i class="nav-icon fas fa-cogs"></i>
                                        <p>Settings</p>
                                    </a>
                                </li>
                               <?php } ?>
                               
                               <?php if ($lv == "1" || $lv == "2") { ?>
                                <li class="nav-item">
                                    <a href="?modul=profile" class="nav-link" data-toggle="mn" id="9">
                                        <i class="nav-icon fas fa-user-edit"></i>
                                        <p>Profile</p>
                                    </a>
                                </li>
                                <?php } ?>
                                
                                <?php if ($lv == "1") { ?>
                                <li class="nav-item">
                                    <a href="?modul=uploadsiswa" class="nav-link" data-toggle="mn" id="10">
                                        <i class="nav-icon fas fa-file-excel"></i>
                                        <p>Upload Excel Siswa</p>
                                    </a>    
                                </li>
                                <li class="nav-item">
                                    <a href="?modul=uploaduser" class="nav-link" data-toggle="mn" id="11">
                                        <i class="nav-icon fas fa-file-excel"></i>
                                        <p>Upload Data User</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="?modul=uploadfoto" class="nav-link" data-toggle="mn" id="12">
                                        <i class="nav-icon fas fa-images"></i>
                                        <p>Upload Foto (ZIP)</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="?modul=datasek" class="nav-link" data-toggle="mn" id="13">
                                        <i class="nav-icon fas fa-school"></i>
                                        <p>Data Sekolah</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <?php } ?>
                        
                         <?php if ($lv == "1") { ?>
                        <!-- System Menu (Admin Only) -->
                        <li class="nav-item has-treeview">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-cogs"></i>
                                <p>
                                    System
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="?modul=brd" class="nav-link" data-toggle="mn" id="14">
                                        <i class="nav-icon fas fa-database"></i>
                                        <p>Database</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="?modul=checkupdate" class="nav-link" data-toggle="mn" id="15">
                                        <i class="nav-icon fas fa-sync-alt"></i>
                                        <p>Check Update</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="?modul=activity" class="nav-link" data-toggle="mn" id="16">
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
        } elseif (isset($_REQUEST['modul']) && $_REQUEST['modul'] != "") {
            $modul = $_REQUEST['modul'];
            
            switch ($modul) {
                case 'press':
                    include "prestasifix.php";
                    break;
                case 'usulan':
                    include "usulan.php";
                    break;
                case 'user':
                    include "user.php";
                    break;
                case 'legalisir':
                    include "legalisir.php";
                    break;
                case 'brd':
                    include "brd.php";
                    break;
                case 'siswa':
                    include "test.php";
                    break;
                case 'uploadsiswa':
                    include "upload_siswa.php";
                    break;
                case 'uploadfoto':
                    include "upload_foto.php";
                    break;
                case 'uploaduser':
                    include "upload_user.php";
                    break;
                case 'profile':
                    include "profil.php";
                    break;
                case 'datasek':
                    include "datasek.php";
                    break;
                case 'settings':
                    include "setting.php";
                    break;
                case 'checkupdate':
                    include "chckupdate.php";
                    break;
                case 'activity':
                    include "activity_log.php";
                    break;
                case 'logout':
                    include "ceklogout.php";
                    break;
                default:
                    include "load.php";
                    break;
            }
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
    <!-- Toastr -->
    <script src="plugins/toastr/toastr.min.js"></script>
    
    <!-- ==========================================
         CUSTOM SCRIPTS
         ========================================== -->
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
                            // Gunakan Toastr jika tersedia, fallback ke alert
                            if(typeof toastr !== 'undefined'){
                                toastr.success('Tahun Pelajaran Baru Berhasil Dibuat dan Diaktifkan!');
                            } else {
                                alert("Tahun Pelajaran Baru Berhasil Dibuat dan Diaktifkan! Silahkan Login Ulang untuk memperbaharui sesi.");
                            }
                            
                            setTimeout(function(){
                                window.location.href = 'exit.php';
                            }, 1500);
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
              <button type="button" class="btn btn-outline-dark" data-dismiss="modal">Nanti Saja</button>
              <button type="button" class="btn btn-info" id="btnCreateTapel"><b>Ya, Buat & Aktifkan</b></button>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>

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