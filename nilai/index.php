<?php
session_start();

include "../cfg/konek.php";
include "../cfg/secure.php";
include "../cfg/tapel.php";
include "../cfg/routes.php";

// --- ROUTING LOGIC ---
$route_key = key($_GET);
$route = empty($route_key) ? "home" : urldecode((string) $route_key);

// --- BREADCRUMB & PAGE TITLE LOGIC ---
$page_title = get_route_title($route, $route_map);
$breadcrumb_items = [
    [
        'name' => $page_title,
        'url' => $route,
        'active' => true
    ]
];

$page_title = end($breadcrumb_items)['name'] ?? 'Dashboard';

$user = $_SESSION['skradm'] ?? '';
$user_role = $_SESSION['user_role'] ?? '';

// Fetch Profile Data (Student Only for this Portal)
$user_safe = mysqli_real_escape_string($sqlconn, $user);
$p_siswa = [];

$sqlp_siswa = mysqli_query($sqlconn, "SELECT * FROM siswa WHERE nis = '$user_safe'");
$p_siswa = ($sqlp_siswa && mysqli_num_rows($sqlp_siswa) > 0) ? mysqli_fetch_array($sqlp_siswa) : [];

// Map variables for display
$nuser = $p_siswa['nis'] ?? '';
$nama = $p_siswa['pd'] ?? '';
$photo = $p_siswa['photo'] ?? '';
$student_id = $p_siswa['id'] ?? '';

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>S.A.D | <?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?></title>
    <base href="/dataok/">

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="">
    <!-- Base & Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/material-design-iconic-font/2.2.0/css/material-design-iconic-font.min.css">

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../plugins/css/select2.min.css">
    <link rel="stylesheet" href="../plugins/css/datatables.min.css">

    <!-- Core Bootstrap & Extensions -->
    <link rel="stylesheet" href="../plugins/css/components.min.css">
    <link rel="stylesheet" href="../plugins/css/bootstrap.min.css">
    <link rel="stylesheet" href="../plugins/css/bootstrap-extended.min.css">
    <link rel="stylesheet" href="../plugins/css/colors.min.css">
    <link rel="stylesheet" href="../plugins/css/palette-gradient.min.css">
    <link rel="stylesheet" href="../plugins/css/style.min.css">
    <link rel="stylesheet" href="https://maxcdn.icons8.com/fonts/line-awesome/1.1/css/line-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">


    <!-- Custom CSS -->
    <link rel="stylesheet" href="../plugins/css/main.css">
    <link rel="stylesheet" href="../custom.css">

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|Quicksand:300,400,500,700|Poppins:300,400,500,600,700"
        rel="stylesheet">

    <!-- Core Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand bg-menu-gradient">
            <!-- Left navbar links -->
            <ul class="navbar-nav align-items-center">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block text-white">
                    <span class="nav-link">Tahun Pelajaran: <?php echo $tapel ?> | Semester:
                        <?php echo $semester == '1' ? 'Ganjil' : ($semester == '2' ? 'Genap' : '-'); ?></span>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto align-items-center">
                <li class="nav-item d-none d-sm-inline-block text-white pr-3">
                    <span class="nav-link">
                        <script type='text/javascript'>
                            (function () {
                                var months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                var myDays = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jum\'at', 'Sabtu'];
                                var now = new Date();
                                var dayName = myDays[now.getDay()];
                                var day = now.getDate();
                                var monthName = months[now.getMonth()];
                                var year = now.getFullYear();
                                document.write(dayName + ', ' + day + ' ' + monthName + ' ' + year + ', ');
                            })();
                        </script>
                        <time id="clock"></time>

                        <script>
                            (function () {
                                var clock = document.getElementById('clock');
                                if (clock) {
                                    setInterval(function () {
                                        var now = new Date();
                                        clock.innerHTML = now.toLocaleTimeString('id-ID', { hour12: false });
                                    }, 1000);
                                    // Initial call
                                    clock.innerHTML = new Date().toLocaleTimeString('id-ID', { hour12: false });
                                }
                            })();
                        </script>
                    </span>
                </li>
            </ul>
        </nav>

        <!-- ==========================================
             MAIN SIDEBAR
             ========================================== -->
        <aside class="main-sidebar sidebar-dark-primary elevation">
            <!-- Brand Logo -->
            <a href="dashboard" class="brand-link d-flex flex-column align-items-center text-center py-2">
                <img src="logo_sekolah.png" alt="smpn171" class="brand-image img-circle elevation-3 mb-1"
                    style="opacity: .7; float: none; margin-left: 0;">
                <span class="brand-text font-weight-light text-wrap" style="line-height: 1.2;">Ruang Nilai Siswa</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- User Panel -->
                <div class="user-panel mt-1 pb-1 mb-1 d-flex">
                    <div class="image">
                        <?php
                        if (!empty($photo) && file_exists("../file/fotopd/$photo")) {
                            echo "<img src='../file/fotopd/$photo' class='img-circle elevation-2' alt='Student Photo'>";
                        } else {
                            echo "<img src='../images/default.png' class='img-circle elevation-2' alt='Default Photo'>";
                        }
                        ?>
                    </div>
                    <div class="info">
                        <?php
                        if ($nama !== "") {
                            echo "<a href='profil' class='d-block'>" . $nama . "</a>";
                        } else {
                            echo "<a class='d-block'>Error</a>";
                        }
                        ?>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        <!-- Main Menu Header -->
                        <li class="nav-header">MENU SISWA</li>

                        <li class="nav-item">
                            <a href="dashboard"
                                class="nav-link <?php echo ($route == 'home' || $route == 'dashboard') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="prestasi-saya?urut=<?php echo $student_id; ?>"
                                class="nav-link <?php echo ($route == 'prestasi-saya') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-medal"></i>
                                <p>Prestasi Saya</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="profil"
                                class="nav-link <?php echo ($route == 'profil') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-user-circle"></i>
                                <p>Profil Saya</p>
                            </a>
                        </li>

                        <!-- Logout -->
                        <li class="nav-item">
                            <a href="exit.php" class="nav-link">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                <p>Exit</p>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- ==========================================
             CONTENT WRAPPER - MODULE ROUTING
             ========================================== -->
        <div class="content-wrapper">
            <!-- Content Header -->
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0"><?php echo $page_title; ?></h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <?php foreach ($breadcrumb_items as $item): ?>
                                    <?php if ($item['active']): ?>
                                        <li class="breadcrumb-item active">
                                            <a href="<?php echo $item['url']; ?>">
                                                <?php echo $item['name']; ?>
                                            </a>
                                        </li>
                                    <?php else: ?>
                                        <li class="breadcrumb-item"><a href="<?php echo $item['url']; ?>">
                                                <?php echo $item['name']; ?>
                                            </a></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <li class="breadcrumb-item">Sistem Arsip Data (S.A.D)</li>

                            </ol>
                        </div>
                    </div>
                </div>
            </section>
            <?php
            $include_file = get_route_include($route, $route_map);
            if (file_exists($include_file)) {
                include $include_file;
            } else {
                include "load.php";
            }
            ?>
        </div>

        <!-- ==========================================
             FOOTER
             ========================================== -->
        <footer class="main-footer">
            <div class="text-center">
                <strong>S.A.D <?php echo isset($ver) ? htmlspecialchars($ver, ENT_QUOTES, 'UTF-8') : '1.0'; ?> -
                    Copyright &copy; <?php echo date('Y'); ?></strong>
            </div>
        </footer>
    </div>
    <!-- ./wrapper -->


    <!-- Scripts -->
    <script>
        $.widget.bridge('uibutton', $.ui.button)
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
    <script src="../plugins/chart.js/Chart.min.js"></script>
    <script src="../js/vendor.min.js"></script>
    <script src="../js/select2.full.min.js"></script>
    <script src="../plugins/toastr/toastr.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/fixedcolumns/3.2.6/js/dataTables.fixedColumns.min.js"></script>

    <script>
        $(document).ready(function () {
            // -- Sidebar Menu Auto-Active State
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

        // -- Modal Drag Support
        if ($.fn.draggable) {
            $('.modal-dialog').draggable({ handle: ".modal-header" });
        }
    </script>

    <!-- Toastr script handler -->
    <?php if (isset($_SESSION['toast_msg'])): ?>
        <script>
            $(document).ready(function () {
                toastr.options = { "closeButton": true, "progressBar": true, "positionClass": "toast-top-right", "timeOut": "5000" };
                toastr.<?php echo $_SESSION['toast_type']; ?>("<?php echo addslashes($_SESSION['toast_msg']); ?>");
            });
        </script>
        <?php
        unset($_SESSION['toast_msg'], $_SESSION['toast_type']);
        ?>
    <?php endif; ?>
</body>

</html>
<?php
// End of file
?>