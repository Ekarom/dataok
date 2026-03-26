<?php
session_start();

include "cfg/konek.php";
include "cfg/secure.php";
include "cfg/tapel.php"; // Ensure functions are available

// Ambil daftar kelas untuk modal daftar hadir
$dh_kelas_list = [];
$rk_dh = @mysqli_query($sqlconn, "SELECT DISTINCT kelas FROM siswa WHERE kelas != '' ORDER BY kelas ASC");
if ($rk_dh) {
    while ($k = mysqli_fetch_assoc($rk_dh)) {
        $dh_kelas_list[] = $k['kelas'];
    }
}

// --- ROUTING LOGIC ---
$route = key($_GET);
if (empty($route)) {
    $route = "dashboard";
} else {
    $route = urldecode($route);
}

// --- BREADCRUMB & PAGE TITLE LOGIC ---
$breadcrumb_map = [
    'dashboard' => 'Dashboard',
    'datasiswa' => 'Data Siswa',
    'arsipdata' => 'Arsip Data',
    'arsipdata/inputprestasi' => 'Input Prestasi',
    'arsipdata/inputlegalisir' => 'Input Legalisir',
    'print' => 'Print',
    'print/laporanprestasi' => 'Laporan Prestasi',
    'print/laporanlegalisir' => 'Laporan Legalisir',
    'management' => 'Management',
    'management/usermanagement' => 'User Staff & Admin',
    'management/datasekolah' => 'Data Sekolah',
    'management/settings' => 'Settings',
    'management/profil' => 'Profile',
    'management/uploadsiswa' => 'Upload Excel Siswa',
    'management/uploaduser' => 'Upload Data User',
    'management/uploadfoto' => 'Upload Foto (ZIP)',
    'system' => 'System',
    'system/database' => 'Database',
    'system/checkupdate' => 'Check Update',
    'system/activitylog' => 'Activity Log',
    'viewpress' => 'Detail Prestasi',
    'editpress' => 'Edit Prestasi',
    'editlegalisir' => 'Edit Legalisir',
    'viewlegalisir' => 'Detail Legalisir'
];

$breadcrumb_items = [];
$route_parts = explode('/', $route);
$route_last = end($route_parts);
$name = $breadcrumb_map[$route] ?? ucfirst(str_replace(['-', '/'], ' ', $route_last));

if ($name !== 'Management') {
    $name = str_replace('Management', '', $name);
}

$breadcrumb_items[] = [
    'name' => $name,
    'url' => $route,
    'active' => true
];

$page_title = end($breadcrumb_items)['name'] ?? 'Dashboard';

$user = $_SESSION['skradm'];

// mengambil data berdasarkan id
// dan menampilkan data ke dalam form modal bootstrap
$sqlp = mysqli_query($sqlconn, "SELECT * FROM usera WHERE userid = '$user'");
$p = ($sqlp && mysqli_num_rows($sqlp) > 0) ? mysqli_fetch_array($sqlp) : [];
$poto = $p['poto'] ?? '';
$lv = $p['level'] ?? '';
$nuser = $p['userid'] ?? '';
$nama = $p['nama'] ?? '';
$passworddb = $p['password'] ?? '';

$sqlp_siswa = mysqli_query($sqlconn, "SELECT * FROM siswa WHERE pd = '$user'");
$p_siswa = ($sqlp_siswa && mysqli_num_rows($sqlp_siswa) > 0) ? mysqli_fetch_array($sqlp_siswa) : [];
$photo = $p_siswa['photo'] ?? '';

// Check for default password (smpn171**) OR Username as Password
$triggerForceChange = false;
$default_pass = 'smpn171**';

// Check if password matches default OR matches username (common initial setup)
if (
    password_verify($default_pass, $passworddb) ||
    $passworddb === md5($default_pass) ||
    password_verify($user, $passworddb) ||
    $passworddb === md5($user)
) {
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
    <title>S.A.D | <?php echo $page_title; ?></title>
    <base href="/coba/">

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="">

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">

    <!-- DataTables BS4 
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
    -->

    <!-- Core Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    <!-- Custom & Plugin CSS --->
    <link rel="stylesheet" href="plugins/css/select2.min.css">
    <link rel="stylesheet" href="plugins/css/datatables.min.css">





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
            background: linear-gradient(180deg, #f1f1f1ff 0%, #ee8f01ff 100%) !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .nav-sidebar .nav-item {
            margin-bottom: 5px;
        }

        .nav-sidebar .nav-link {
            border-radius: 10px !important;
            color: #1f2d3d !important; /* Diubah menjadi gelap agar terbaca di background putih */
            transition: all 0.3s ease;
        }

        .nav-sidebar .nav-link:hover,
        .nav-sidebar .nav-link.active {
            background-color: rgba(0, 0, 0, 0.1) !important; /* Efek hover digelapkan */
            transform: translateX(5px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .nav-sidebar .nav-icon {
            color: #1f2d3d !important; /* Ikon diubah gelap */
            opacity: 0.8;
        }


        .brand-link {
            border-bottom: 1px solid rgba(0, 0, 0, 0.1) !important;
            text-decoration: none !important;
            color: #1f2d3d !important; /* Teks logo atas diubah gelap */
        }

        .user-panel {
            border-bottom: 1px solid rgba(0, 0, 0, 0.1) !important;
        }

        .user-panel a {
            text-decoration: none !important;
            color: #1f2d3d !important;
        }

        /* Global Menu Gradient Class */
        .bg-menu-gradient {
            background: linear-gradient(180deg, #f1f1f1ff 0%, #ee8f01ff 100%) !important;
            color: #1f2d3d !important;
        }


        /* Nav Tabs Styling */
        .nav-tabs .nav-link.active {
            background: linear-gradient(180deg, #f1f1f1ff 0%, #ee8f01ff 100%) !important;
            color: #1f2d3d !important;
            border-color: #dee2e6 #dee2e6 #fff;
        }

        /* Table Header Styling */
        table thead th {
            background: linear-gradient(180deg, #f1f1f1ff 0%, #ee8f01ff 100%) !important;
            color: #1f2d3d !important;
            border-color: #1a1611ff;
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
            background: linear-gradient(180deg, #2c3e50 25%, #01d112ff 100%);
            color: #fff !important;
        }

        .bg-2 {
            background: linear-gradient(180deg, #2c3e50 25%, #01b2d1ff 100%);
            color: #fff !important;
        }

        .bg-3 {
            background: linear-gradient(180deg, #2c3e50 25%, #1900ffff 100%);
            color: #fff !important;
        }

        .bg-4 {
            background: linear-gradient(180deg, #2c3e50 25%, #ffee00ff 100%);
            color: #fff !important;
        }

        .bg-5 {
            background: linear-gradient(180deg, #2c3e50 25%, #ff0000ff 100%);
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
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block text-white ml-2">
                    Tahun Pelajaran: <?php echo $tapel ?> | Semester:
                    <?php echo $semester == '1' ? 'Ganjil' : ($semester == '2' ? 'Genap' : '-'); ?>
                </li>
            </ul>
            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item d-none d-sm-inline-block text-white pr-3">
<script type='text/javascript'>
    (function() {
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
(function() {
    var clock = document.getElementById('clock');
    if (clock) {
        setInterval(function() {
            var now = new Date();
            clock.innerHTML = now.toLocaleTimeString('id-ID', { hour12: false });
        }, 1000);
        // Initial call
        clock.innerHTML = new Date().toLocaleTimeString('id-ID', { hour12: false });
    }
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

                <?php if ($show_tapel_modal): ?>
                    <script>
                        $(document).ready(function () {
                            // Append modal to body to fix backdrop issue
                            $('#modalNewTapel').appendTo('body').modal('show');

                            $('#btnCreateTapel').click(function () {
                                var tapel = '<?php echo $new_tapel; ?>';
                                var smt = '<?php echo $new_smt; ?>';
                                var tahun = '<?php echo $new_tahun; ?>';

                                $.ajax({
                                    type: 'POST',
                                    url: 'create_tapel.php',
                                    data: { tapel: tapel, smt: smt, tahun: tahun },
                                    success: function (response) {
                                        if (response.trim() == "success") {
                                            alert("Tahun Pelajaran Baru Berhasil Dibuat dan Diaktifkan! Silahkan Login Ulang untuk memperbaharui sesi.");
                                            window.location.href = 'exit.php';
                                        } else {
                                            alert("Gagal: " + response);
                                        }
                                    },
                                    error: function () {
                                        alert("Terjadi kesalahan koneksi.");
                                    }
                                });
                            });
                        });
                    </script>

                    <!-- Modal New Tapel -->
                    <div class="modal fade" id="modalNewTapel" tabindex="-1" aria-labelledby="exampleModalLabel"
                        aria-hidden="true" data-backdrop="static" data-keyboard="false">
                        <div class="modal-dialog">
                            <div class="modal-content ">
                                <div class="modal-header bg-info">
                                    <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-calendar-alt"></i>
                                        Deteksi Tahun Pelajaran Baru</h5>
                                </div>
                                <div class="modal-body">
                                    <p>Sistem mendeteksi bahwa saat ini sudah memasuki periode:</p>
                                    <h3>Tahun Pelajaran: <b><?php echo $new_tapel; ?></b></h3>
                                    <h3>Semester: <b><?php echo $new_smt == '1' ? '1 (Ganjil)' : '2 (Genap)'; ?></b></h3>
                                    <p>Data ini belum ada di database. Apakah Anda ingin membuatnya dan mengaktifkannya
                                        sekarang?</p>
                                </div>
                                <div class="modal-footer justify-content-between">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Nanti
                                        Saja</button>
                                    <button type="button" class="btn btn-primary" id="btnCreateTapel"><b>Ya, Buat &
                                            Aktifkan</b></button>
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
        <aside class="main-sidebar sidebar-light-primary elevation-4">
            <!-- Brand Logo -->
            <a href="dashboard" class="brand-link d-flex flex-column align-items-center text-center py-3">
                <img src="images/logo.png" alt="smpn171" class="brand-image img-circle elevation-3 mb-2"
                    style="opacity: .7; float: none; margin-left: 0;">
                <span class="brand-text font-weight-light text-wrap" style="line-height: 1.2;">Sistem Arsip Data
                </span>
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
                                echo "<a href='profil' class='d-block'>" . $nama . "</a>";
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
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        <?php
                        // $route already set at the top
                        if (!$triggerForceChange) {
                            ?>
                            <!-- Main Menu Header -->
                            <li class="nav-header">MENU UTAMA</li>

                            <!-- Data Siswa (Admin Only) -->
                            <?php if ($lv == "1") { ?>
                                <li class="nav-item">
                                    <a href="datasiswa" class="nav-link <?php echo ($route == 'datasiswa') ? 'active' : ''; ?>"
                                        id="1">
                                        <i class="nav-icon fas fa-address-card"></i>
                                        <p>Data Siswa</p>
                                    </a>
                                </li>
                                <!--<li class="nav-item">
                                    <a href="../compress" class="nav-link" target="_blank">
                                        <i class=" nav-icon fas fa-tools"></i>
                                        <p>SAD PDF</p>
                                    </a>
                                </li>-->
                            <?php } ?>

                            <!-- Arsip Data Menu -->
                            <?php $is_arsip = (strpos($route, 'arsipdata') === 0 || $route == 'dataprestasi' || $route == 'inputlegalisir'); ?>
                            <li class="nav-item has-treeview <?php echo $is_arsip ? 'menu-open' : ''; ?>">
                                <a href="arsipdata" class="nav-link <?php echo $is_arsip ? 'active' : ''; ?>" id="2">
                                    <i class="nav-icon fas fa-file-archive"></i>
                                    <p>
                                        Arsip Data
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="arsipdata/inputprestasi"
                                            class="nav-link <?php echo ($route == 'arsipdata/inputprestasi' || $route == 'dataprestasi') ? 'active' : ''; ?>"
                                            id="3">
                                            <i class="nav-icon fas fa-trophy"></i>
                                            <p>Input Prestasi</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="arsipdata/inputlegalisir"
                                            class="nav-link <?php echo ($route == 'arsipdata/inputlegalisir' || $route == 'inputlegalisir') ? 'active' : ''; ?>"
                                            id="5">
                                            <i class="nav-icon fas fa-stamp"></i>
                                            <p>Input Legalisir</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <!-- Administrator Section -->
                            <li class="nav-header">ADMINISTRATOR</li>
                            <!-- Print Menu -->
                            <?php $is_print = (strpos($route, 'print') === 0 || $route == 'laporanprestasi' || $route == 'laporanlegalisir'); ?>
                            <li class="nav-item has-treeview <?php echo $is_print ? 'menu-open' : ''; ?>">
                                <a href="print" class="nav-link <?php echo $is_print ? 'active' : ''; ?>" id="6">
                                    <i class="nav-icon fas fa-print"></i>
                                    <p>
                                        Print
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="#" class="nav-link" data-toggle="modal" data-target="#modalDaftarHadir"
                                            id="7">
                                            <i class="nav-icon fas fa-clipboard-list"></i>
                                            <p>Daftar Hadir</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href='print/laporanprestasi'
                                            class="nav-link <?php echo ($route == 'print/laporanprestasi' || $route == 'laporanprestasi') ? 'active' : ''; ?>"
                                            id="8">
                                            <i class="nav-icon fas fa-clipboard-list"></i>
                                            <p>Laporan Prestasi</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href='print/laporanlegalisir'
                                            class="nav-link <?php echo ($route == 'print/laporanlegalisir' || $route == 'laporanlegalisir') ? 'active' : ''; ?>"
                                            id="9">
                                            <i class="nav-icon fas fa-clipboard-list"></i>
                                            <p>Laporan Legalisir</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <!-- Management Menu -->
                            <?php if ($lv == "1" || $lv == "2") { ?>
                                <?php $is_mgt = (strpos($route, 'management') === 0 || in_array($route, ['usermanagement', 'datasekolah', 'pengaturan', 'profil', 'uploadsiswa', 'uploaduser', 'uploadfoto'])); ?>
                                <li class="nav-item has-treeview <?php echo $is_mgt ? 'menu-open' : ''; ?>">
                                    <a href="management" class="nav-link <?php echo $is_mgt ? 'active' : ''; ?>" id="9">
                                        <i class="nav-icon fas fa-layer-group"></i>
                                        <p>
                                            Management
                                            <i class="right fas fa-angle-left"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        <?php if ($lv == "1") { ?>
                                            <li class="nav-item">
                                                <a href="management/usermanagement"
                                                    class="nav-link <?php echo ($route == 'management/usermanagement' || $route == 'usermanagement') ? 'active' : ''; ?>"
                                                    id="10">
                                                    <i class="nav-icon fas fa-users-cog"></i>
                                                    <p>User Staff & Admin</p>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="management/datasekolah"
                                                    class="nav-link <?php echo ($route == 'management/datasekolah' || $route == 'datasekolah') ? 'active' : ''; ?>"
                                                    id="11">
                                                    <i class="nav-icon fas fa-school"></i>
                                                    <p>Data Sekolah</p>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="management/settings"
                                                    class="nav-link <?php echo ($route == 'management/settings' || $route == 'pengaturan') ? 'active' : ''; ?>"
                                                    id="12">
                                                    <i class="nav-icon fas fa-cogs"></i>
                                                    <p>Settings</p>
                                                </a>
                                            </li>
                                        <?php } ?>

                                        <li class="nav-item">
                                            <a href="management/profil"
                                                class="nav-link <?php echo ($route == 'management/profil' || $route == 'profil') ? 'active' : ''; ?>"
                                                id="13">
                                                <i class="nav-icon fas fa-user-edit"></i>
                                                <p>Profile</p>
                                            </a>
                                        </li>

                                        <?php if ($lv == "1") { ?>
                                            <li class="nav-item">
                                                <a href="management/uploadsiswa"
                                                    class="nav-link <?php echo ($route == 'management/uploadsiswa' || $route == 'uploadsiswa') ? 'active' : ''; ?>"
                                                    id="14">
                                                    <i class="nav-icon fas fa-file-excel"></i>
                                                    <p>Upload Excel Siswa</p>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="management/uploaduser"
                                                    class="nav-link <?php echo ($route == 'management/uploaduser' || $route == 'uploaduser') ? 'active' : ''; ?>"
                                                    id="15">
                                                    <i class="nav-icon fas fa-file-excel"></i>
                                                    <p>Upload Data User</p>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="management/uploadfoto"
                                                    class="nav-link <?php echo ($route == 'management/uploadfoto' || $route == 'uploadfoto') ? 'active' : ''; ?>"
                                                    id="16">
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
                                <?php $is_system = (strpos($route, 'system') === 0 || in_array($route, ['database', 'checkupdate', 'activitylog'])); ?>
                                <li class="nav-item has-treeview <?php echo $is_system ? 'menu-open' : ''; ?>">
                                    <a href="system" class="nav-link <?php echo $is_system ? 'active' : ''; ?>" id="17">
                                        <i class="nav-icon fas fa-cogs"></i>
                                        <p>
                                            System
                                            <i class="right fas fa-angle-left"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        <li class="nav-item">
                                            <a href="system/database"
                                                class="nav-link <?php echo ($route == 'system/database' || $route == 'database') ? 'active' : ''; ?>"
                                                id="18">
                                                <i class="nav-icon fas fa-database"></i>
                                                <p>Database</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="system/checkupdate"
                                                class="nav-link <?php echo ($route == 'system/checkupdate' || $route == 'checkupdate') ? 'active' : ''; ?>"
                                                id="19">
                                                <i class="nav-icon fas fa-sync-alt"></i>
                                                <p>Check Update</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="system/activitylog"
                                                class="nav-link <?php echo ($route == 'system/activitylog' || $route == 'activitylog') ? 'active' : ''; ?>"
                                                id="20">
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
        } else {
            ?>
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
                // $route already set above in nav
                switch ($route) {
                    case 'dashboard':
                    case 'arsipdata':
                    case 'print':
                    case 'management':
                    case 'system':
                    case 'sistem':
                        include "load.php";
                        break;

                    case 'datasiswa':
                        include "siswa.php";
                        break;

                    case 'arsipdata/inputprestasi':
                    case 'inputprestasi':
                    case 'dataprestasi':
                    case 'input':
                        if (isset($_GET['nis'])) {
                            include "inputprestasi.php";
                        } else {
                            include "dataprestasi.php";
                        }
                        break;

                    case 'arsipdata/inputlegalisir':
                    case 'inputlegalisir':
                        $_GET['aksi'] = 'tambah';
                        include "laporanlegalisir.php";
                        break;

                    case 'print/laporanprestasi':
                    case 'laporanprestasi':
                    case 'laporanpress':
                    case 'laporan':
                        include "laporanpress.php";
                        break;

                    case 'print/laporanlegalisir':
                    case 'laporanlegalisir':
                        include "laporanlegalisir.php";
                        break;

                    case 'management/usermanagement':
                    case 'usermanagement':
                    case 'user':
                        include "user.php";
                        break;

                    case 'management/datasekolah':
                    case 'datasekolah':
                    case 'datasek':
                        include "datasek.php";
                        break;

                    case 'management/settings':
                    case 'pengaturan':
                    case 'settings':
                        include "setting.php";
                        break;

                    case 'management/profil':
                    case 'profil':
                        include "profil.php";
                        break;

                    case 'management/uploadsiswa':
                    case 'uploadsiswa':
                        include "upload_siswa.php";
                        break;

                    case 'management/uploaduser':
                    case 'uploaduser':
                        include "upload_usera.php";
                        break;

                    case 'management/uploadfoto':
                    case 'uploadfoto':
                        include "upload_foto.php";
                        break;

                    case 'system/database':
                    case 'sistem/database':
                    case 'database':
                    case 'brd':
                        include "brd.php";
                        break;

                    case 'system/checkupdate':
                    case 'sistem/checkupdate':
                    case 'checkupdate':
                        include "chckupdate.php";
                        break;

                    case 'system/activitylog':
                    case 'sistem/activitylog':
                    case 'activitylog':
                    case 'activity':
                        include "activity_log.php";
                        break;

                    case 'press':
                        include "prosespress.php";
                        break;
                    case 'viewpress':
                        include "view_pres.php";
                        break;
                    case 'editpress':
                        include "edit_press.php";
                        break;
                    case 'legalisir':
                        include "laporanlegalisir.php";
                        break;
                    case 'editlegalisir':
                        include "edit_legalisir.php";
                        break;
                    case 'viewlegalisir':
                        include "view_legalisir.php";
                        break;
                    case 'dh':
                        include "daftar_hadir.php";
                        break;
                    case 'logout':
                        include "logout.php";
                        break;
                    case 'modul':
                        $mod = strtolower($_GET['modul'] ?? '');
                        if ($mod === 'uploaduser')
                            include "upload_usera.php";
                        elseif ($mod === 'uploadfoto')
                            include "upload_foto.php";
                        elseif ($mod === 'profile' || $mod === 'profil')
                            include "profil.php";
                        elseif ($mod === 'settings' || $mod === 'setting' || $mod === 'pengaturan')
                            include "setting.php";
                        elseif ($mod === 'activitylog' || $mod === 'log-aktivitas')
                            include "activity_log.php";
                        else
                            include "load.php";
                        break;
                    default:
                        include "load.php";
                        break;
                }
                ?>
            </div>
            <?php
        }
        ?>

        <!-- ==========================================
             FOOTER
             ========================================== -->
        <footer class="main-footer">
            <center>
                <strong>S.A.D <?php echo isset($ver) ? $ver : '1.0'; ?> - Copyright &copy; 2025</strong>
            </center>
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
    <script src="js/vendor.min.js"></script>
    <script src="js/select2.full.min.js"></script>
    <script src="plugins/toastr/toastr.min.js"></script>
    <!-- <script src="js/form-select2.min.js"></script> -->
    <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/fixedcolumns/3.2.6/js/dataTables.fixedColumns.min.js"></script>

    <style>
        #modalDaftarHadir .modal-header {
            background: linear-gradient(135deg, #2c3e50 0%, #01b2d1 100%);
            color: #fff;
            border-radius: 4px 4px 0 0;
        }

        #modalDaftarHadir .modal-header .close {
            color: #fff;
            opacity: .8;
        }

        #modalDaftarHadir .modal-header .close:hover {
            opacity: 1;
        }

        #modalDaftarHadir .form-label-sm {
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: .4px;
            margin-bottom: 4px;
            display: block;
        }

        #modalDaftarHadir .form-control {
            border-radius: 8px;
            border: 1.5px solid #cbd5e1;
            font-size: 13px;
        }

        #modalDaftarHadir .form-control:focus {
            border-color: #01b2d1;
            box-shadow: 0 0 0 3px rgba(1, 178, 209, .15);
        }

        #modalDaftarHadir .btn-buka {
            background: linear-gradient(135deg, #2c3e50 0%, #01b2d1 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 24px;
            font-weight: 700;
            font-size: 14px;
            transition: opacity .2s;
        }

        #modalDaftarHadir .btn-buka:hover {
            opacity: .88;
            color: #fff;
        }

        #modalDaftarHadir .preview-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 600;
        }
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
                var tgl = $('#dhTanggal').val() || '<?= date('Y-m-d') ?>';
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
            // AdminLTE 3 will automatically handle treeview state if we add classes.
            // But we already added menu-open and active via PHP for reliability.

            // Fix some UI inconsistencies when navigating categories
            if ($('.nav-link.active').length > 1) {
                // Ensure parents and children are both highlighted correctly
            }
        });

        if ($.fn.draggable) {
            $('.modal-dialog').draggable({
                handle: ".modal-header"
            });
        }
    </script>
<script>
    // Console notice for cleanup
    console.log("S.A.D System - UI Scripts Initialized");
</script>
</body>

</html>
<?php
// End of file
?>