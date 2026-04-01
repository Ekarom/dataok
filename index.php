<?php
session_start();

include "cfg/konek.php";
include "cfg/secure.php";
include "cfg/tapel.php";
include "cfg/routes.php";

// Ambil daftar kelas untuk modal daftar hadir
$dh_kelas_list = [];
$rk_dh = @mysqli_query($sqlconn, "SELECT DISTINCT kelas FROM siswa WHERE kelas != '' ORDER BY kelas ASC");
if ($rk_dh) {
    while ($k = mysqli_fetch_assoc($rk_dh)) {
        $dh_kelas_list[] = $k['kelas'];
    }
}

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

$user = $_SESSION['skradm'];

// mengambil data berdasarkan id
// dan menampilkan data ke dalam form modal bootstrap
$user_safe = mysqli_real_escape_string($sqlconn, $user ?? '');
$sqlp = mysqli_query($sqlconn, "SELECT * FROM usera WHERE userid = '$user_safe'");
$p = ($sqlp && mysqli_num_rows($sqlp) > 0) ? mysqli_fetch_array($sqlp) : [];
$poto = $p['poto'] ?? '';
$lv = $p['level'] ?? '';
$nuser = $p['userid'] ?? '';
$nama = $p['nama'] ?? '';
$passworddb = $p['password'] ?? '';

$sqlp_siswa = mysqli_query($sqlconn, "SELECT * FROM siswa WHERE pd = '$user_safe'");
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
    <link rel="stylesheet" href="plugins/css/select2.min.css">
    <link rel="stylesheet" href="plugins/css/datatables.min.css">

    <!-- Core Bootstrap & Extensions -->
    <link rel="stylesheet" href="plugins/css/components.min.css">
    <link rel="stylesheet" href="plugins/css/bootstrap.min.css">
    <link rel="stylesheet" href="plugins/css/bootstrap-extended.min.css">
    <link rel="stylesheet" href="plugins/css/colors.min.css">
    <link rel="stylesheet" href="plugins/css/palette-gradient.min.css">
    <link rel="stylesheet" href="plugins/css/style.min.css">
    <link rel="stylesheet" href="https://maxcdn.icons8.com/fonts/line-awesome/1.1/css/line-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">


    <!-- Custom CSS -->
    <link rel="stylesheet" href="plugins/css/main.css">
    <link rel="stylesheet" href="custom.css">

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
        <aside class="main-sidebar sidebar-dark-primary elevation">
            <!-- Brand Logo -->
            <a href="dashboard" class="brand-link d-flex flex-column align-items-center text-center py-2">
                <img src="images/logo.png" alt="smpn171" class="brand-image img-circle elevation-3 mb-1"
                    style="opacity: .7; float: none; margin-left: 0;">
                <span class="brand-text font-weight-light text-wrap" style="line-height: 1.2;">Sistem Arsip Data</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- User Panel -->
                <div class="user-panel mt-1 pb-1 mb-1 d-flex">
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
                <nav class="">
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
                                    <i class="nav-icon fas fa-edit"></i>
                                    <p>
                                        Input
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
                $include_file = get_route_include($route, $route_map);
                if (file_exists($include_file)) {
                    include $include_file;
                } else {
                    include "load.php";
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
            <div class="text-center">
                <strong>S.A.D <?php echo isset($ver) ? htmlspecialchars($ver, ENT_QUOTES, 'UTF-8') : '1.0'; ?> -
                    Copyright &copy; <?php echo date('Y'); ?></strong>
            </div>
        </footer>
    </div>
    <!-- ./wrapper -->

    <!-- Modal Daftar Hadir -->
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
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnBukaDH">
                        <i class="fas fa-print mr-1"></i>Tampilkan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        $.widget.bridge('uibutton', $.ui.button)
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
    <script src="plugins/chart.js/Chart.min.js"></script>
    <script src="js/vendor.min.js"></script>
    <script src="js/select2.full.min.js"></script>
    <script src="plugins/toastr/toastr.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/fixedcolumns/3.2.6/js/dataTables.fixedColumns.min.js"></script>

    <script>
        $(document).ready(function () {
            // -- Tombol Daftar Hadir Preview/Print
            $('#btnBukaDH').on('click', function () {
                var kelas = $('#dhKelasVal').val();
                var tgl = $('#dhTanggal').val() || '<?= date('Y-m-d') ?>';
                var judul = $('#dhJenisCetak').val();
                var url = 'daftar_hadir.php?kelas=' + encodeURIComponent(kelas) + '&tanggal=' + encodeURIComponent(tgl) + '&judul=' + encodeURIComponent(judul);
                window.open(url, '_blank');
                $('#modalDaftarHadir').modal('hide');
            });

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