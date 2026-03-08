<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include "cfg/secure.php";
?>
<style>
    .small-box {
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }
    .card {
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }
    @keyframes blink {
        0%   { opacity: 1; transform: scale(1); }
        50%  { opacity: 0.5; transform: scale(0.9); }
        100% { opacity: 1; transform: scale(1); }
    }
    .online-indicator {
        height: 10px;
        width: 10px;
        background-color: #28a745;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
        box-shadow: 0 0 5px #28a745;
        animation: blink 1.5s infinite ease-in-out;
    }
    .user-list-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px;
        border-bottom: 1px solid #f4f4f4;
    }
    .user-list-item:last-child { border-bottom: none; }
    .user-info { display: flex; align-items: center; }
    .user-role-badge { font-size: 0.8em; margin-left: 10px; }
</style>

<!-- Content Wrapper -->
<div class="content-wrapper">

    <!-- Page Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">

        <!-- Small stat cards -->
        <div class="row">

            <!-- Box 1: Prestasi -->
            <div class="col-lg-2 col-6">
                <div class="small-box bg-1">
                    <div class="inner">
                        <?php
$query = mysqli_query($sqlconn, "SELECT COUNT(*) as total FROM prestasi");
$row = mysqli_fetch_assoc($query);
$prestasi = $row['total'];
?>
                        <h3><?php echo $prestasi; ?></h3>
                        <p>Laporan Prestasi</p>
                    </div>
                    <div class="icon"><i class="ion ion-trophy"></i></div>
                    <a href="?press" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <!-- Box 2: Legalisir -->
            <div class="col-lg-2 col-6">
                <div class="small-box bg-3">
                    <div class="inner">
                        <?php
$query = mysqli_query($sqlconn, "SELECT COUNT(*) as total FROM legalisir");
$row = mysqli_fetch_assoc($query);
$legalisir = $row['total'];
?>
                        <h3><?php echo $legalisir; ?></h3>
                        <p>Laporan Legalisir</p>
                    </div>
                    <div class="icon"><i class="ion ion-archive"></i></div>
                    <a href="?legalisir" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <!-- Box 3: Siswa -->
            <div class="col-lg-2 col-6">
                <div class="small-box bg-4">
                    <div class="inner">
                        <?php
$query = mysqli_query($sqlconn, "SELECT COUNT(*) as total FROM siswa");
$row = mysqli_fetch_assoc($query);
$siswa = $row['total'];
?>
                        <h3><?php echo $siswa; ?></h3>
                        <p>Manajemen PD</p>
                    </div>
                    <div class="icon"><i class="ion ion-person-stalker"></i></div>
                    <a href="?siswa" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <!-- Box 4: User -->
            <div class="col-lg-2 col-6">
                <div class="small-box bg-5">
                    <div class="inner">
                        <?php
$query = mysqli_query($sqlconn, "SELECT COUNT(*) as total FROM usera");
$row = mysqli_fetch_assoc($query);
$user = $row['total'];
?>
                        <h3><?php echo $user; ?></h3>
                        <p>Manajemen User</p>
                    </div>
                    <div class="icon"><i class="ion ion-person-stalker"></i></div>
                    <a href="?user" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>

        </div>
        <!-- /.row (stat cards) -->

        <?php
        // =====================================================================
        // Statistik Prestasi Mendalam: Juara & Semester per Tahun Pelajaran
        // =====================================================================
        $tp_labels          = [];
        $smt1_counts        = [];
        $smt2_counts        = [];
        $juara_stats        = [];
        $total_all_prestasi = 0;

        $q_stats = mysqli_query($sqlconn, "SELECT dbname, tahun FROM dbset ORDER BY tahun ASC");
        if ($q_stats) {
            while ($r_s = mysqli_fetch_array($q_stats)) {
                $db_n = $r_s['dbname'];
                $db_t = $r_s['tahun'];

                $c_db = @new mysqli("localhost", "root", "", $db_n);
                if (!$c_db->connect_error) {
                    $table_check = $c_db->query("SHOW TABLES LIKE 'prestasi'");
                    if ($table_check && $table_check->num_rows > 0) {
                        $tp_labels[] = "TP " . $db_t;
                        $s1 = 0;
                        $s2 = 0;

                        $res_pres = $c_db->query("SELECT juara, tgl_kegiatan, bulan FROM prestasi");
                        if ($res_pres) {
                            while ($row = $res_pres->fetch_assoc()) {
                                $total_all_prestasi++;

                                $j = $row['juara'];
                                if (!isset($juara_stats[$j])) $juara_stats[$j] = 0;
                                $juara_stats[$j]++;

                                $month = 0;
                                if (!empty($row['tgl_kegiatan']) && $row['tgl_kegiatan'] !== '0000-00-00') {
                                    $month = (int) date('n', strtotime($row['tgl_kegiatan']));
                                } else {
                                    $m_map = ['Januari'=>1,'Februari'=>2,'Maret'=>3,'April'=>4,'Mei'=>5,'Juni'=>6,
                                              'Juli'=>7,'Agustus'=>8,'September'=>9,'Oktober'=>10,'November'=>11,'Desember'=>12];
                                    $month = $m_map[$row['bulan']] ?? 0;
                                }
                                if ($month >= 7 || $month == 0) $s1++; else $s2++;
                            }
                        }
                        $smt1_counts[] = $s1;
                        $smt2_counts[] = $s2;
                    }
                    $c_db->close();
                }
            }
        }

        $rank_labels = [];
        $rank_values = [];
        arsort($juara_stats);
        foreach ($juara_stats as $key => $val) {
            $rank_labels[] = "Juara " . $key;
            $rank_values[] = $val;
        }
        ?>

        <!-- Panels row: Welcome, History Log | User Online + Statistics -->
        <div class="row">

            <!-- Left col -->
            <section class="col-lg-5 connectedSortable">

                <!-- Welcome Card -->
                <div class="card">
                    <div class="card-header bg-menu-gradient">
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                        <h4 class="card-title">
                            <i class="fas fa-chart-pie mr-1"></i>
                            Selamat Datang, <b><?php echo $nama; ?></b>
                        </h4>
                    </div>
                    <div class="card-body border">
                        <div class="card">
                            <div class="card-header border">
                                <b class="card-title"><i class="fas fa-info-circle mr-1"></i> &nbsp;Informasi Terbaru</b>
                            </div>
                            <div class="card-body border">
                                Harap Teliti Sebelum Menginput Prestasi Siswa Terima Kasih
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.Welcome Card -->

                <!-- History Log Card -->
                <div class="card direct-chat">
                    <div class="card-header bg-menu-gradient">
                        <h3 class="card-title">
                            <i class="fas fa-history mr-1"></i> History Log
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="direct-chat-messages">
                            <?php
if (isset($log1) && isset($log5)) {
    $i = isset($log5['n1']) ? $log5['n1'] : 0;
    while ($log2 = mysqli_fetch_array($log1)) {
?>
                                    <div class="direct-chat-msg">
                                        <div class="direct-chat-infos clearfix">
                                            <span class="direct-chat-name float-left"><?php echo htmlspecialchars($log2['nama']); ?></span>
                                            <span class="direct-chat-timestamp float-right"><?php echo $log2['waktu']; ?></span>
                                        </div>
                                        <img class="direct-chat-img" src="images/info.png" alt="message user image">
                                        <div class="direct-chat-text">
                                            <?php echo htmlspecialchars($log2['info']); ?>
                                        </div>
                                    </div>
                                    <?php
        $i--;
    }
}
else {
    echo '<div class="p-3 text-center text-muted">No history logs available</div>';
}
?>
                        </div>
                    </div>
                </div>
                <!-- /.History Log Card -->

            </section>
            <!-- /.Left col -->

            <!-- Right col -->
            <section class="col-lg-7 connectedSortable">

                <!-- User Online Card -->
                <div class="card mb-3">
                    <div class="card-header bg-menu-gradient">
                        <h3 class="card-title">
                            <i class="fas fa-users mr-1"></i> User Online
                        </h3>
                        <?php
$timeout = 300;
$current_time = time();

if (isset($sqlconn) && $sqlconn) {
    $check_col = mysqli_query($sqlconn, "SHOW COLUMNS FROM usera LIKE 'userid'");
    $user_col = (mysqli_num_rows($check_col) > 0) ? 'userid' : 'username';

    if (isset($_SESSION['skradm'])) {
        $user_id_txn = $_SESSION['skradm'];
        $upd = mysqli_query($sqlconn, "UPDATE usera SET last_activity = '$current_time' WHERE $user_col = '$user_id_txn'");
        if (!$upd) {
            $db_error = "Error updating status. " . mysqli_error($sqlconn);
        }
    }

    $page = isset($_GET['online_page']) ? (int)$_GET['online_page'] : 1;
    $limit = 5;
    $offset = ($page - 1) * $limit;

    $online_count = 0;
    $count_query = mysqli_query($sqlconn, "SELECT COUNT(*) as total FROM usera WHERE last_activity > ($current_time - $timeout)");
    if ($count_query) {
        $row = mysqli_fetch_assoc($count_query);
        $online_count = $row['total'];
    }
    else {
        $db_error = mysqli_error($sqlconn);
    }
    $total_pages = ceil($online_count / $limit);
}
else {
    $online_count = 0;
    $total_pages = 0;
    $db_error = "Database connection not available.";
}
?>
                        <div class="card-tools">
                            <span class="badge badge-light" title="<?php echo $online_count; ?> Users Online"><?php echo $online_count; ?></span>
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <ul class="nav flex-column">
                            <?php
if (isset($db_error)) {
    echo '<li class="nav-item p-3 text-danger">';
    echo '<strong>Database Error:</strong> ' . htmlspecialchars($db_error) . '<br>';
    if (strpos($db_error, 'Unknown column') !== false) {
        echo '<small>Tabel <code>usera</code> tidak memiliki kolom <code>last_activity</code>.<br>Jalankan: <code>ALTER TABLE usera ADD COLUMN last_activity INT(11);</code></small>';
    }
    echo '</li>';
}
else {
    $query_online = mysqli_query($sqlconn, "SELECT * FROM usera WHERE last_activity > ($current_time - $timeout) ORDER BY last_activity DESC LIMIT $limit OFFSET $offset");
    if ($query_online && mysqli_num_rows($query_online) > 0) {
        while ($user = mysqli_fetch_assoc($query_online)) {
            $display_time = date('H:i', strtotime($user['lastlogin']));
?>
                                        <li class="nav-item user-list-item">
                                            <div class="user-info d-flex align-items-center">
                                                <span class="online-indicator" title="Online"></span>
                                                <div class="d-flex flex-column ml-2">
                                                    <span class="font-weight-bold" style="line-height: 1.2;"><?php echo htmlspecialchars($user['nama']); ?></span>
                                                    <small class="text-muted mt-1">
                                                        <span class="badge badge-success user-role-badge" style="font-size: 12px; padding: 2px 5px;">
                                                            <?php echo($user['level'] == '1') ? 'ADMIN' : 'STAFF'; ?>
                                                        </span>
                                                    </small>
                                                </div>
                                            </div>
                                            <small class="text-muted"><i class="far fa-clock mr-1"></i><?php echo $display_time; ?></small>
                                        </li>
                                        <?php
        }
    }
    else {
        echo '<li class="nav-item p-3 text-center text-muted">No users online right now.</li>';
    }
}
?>
                        </ul>
                    </div>
                    <?php if (!isset($db_error) && $online_count > 0): ?>
                    <div class="card-footer clearfix">
                        <ul class="pagination pagination-sm m-0 float-right">
                            <li class="page-item <?php echo($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?online_page=<?php echo $page - 1; ?>">&laquo;</a>
                            </li>
                            <li class="page-item disabled">
                                <a class="page-link" href="#">Hal <?php echo $page; ?> / <?php echo($total_pages > 0 ? $total_pages : 1); ?></a>
                            </li>
                            <li class="page-item <?php echo($page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?online_page=<?php echo $page + 1; ?>">&raquo;</a>
                            </li>
                        </ul>
                    </div>
                    <?php
endif; ?>
                </div>
                <!-- /.User Online Card -->

                <!-- Statistics: Semester Analysis -->
                <div class="card card-outline card-info">
                    <div class="card-header bg-menu-gradient">
                        <h3 class="card-title"><i class="fas fa-chart-line mr-1"></i> Analisis Prestasi per Semester</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Stacked Bar Chart -->
                            <div class="col-md-8">
                                <div class="chart">
                                    <canvas id="semesterChart" style="min-height: 250px; height: 250px; max-height: 250px;"></canvas>
                                </div>
                            </div>
                            <!-- Doughnut Rank Chart -->
                            <div class="col-md-4">
                                <div class="text-center mb-2">
                                    <span class="badge badge-warning p-2">Total: <?php echo $total_all_prestasi; ?> Sertifikat</span>
                                </div>
                                <canvas id="rankChart" style="min-height: 160px; height: 160px; max-height: 160px;"></canvas>
                                <div class="mt-2 pt-2 border-top">
                                    <?php
                                    $colors = ['#f39c12','#00c0ef','#3c8dbc','#00a65a','#f56954','#d2d6de'];
                                    foreach ($rank_labels as $i => $rl):
                                        if ($i > 5) break;
                                        $pct = ($total_all_prestasi > 0) ? round(($rank_values[$i] / $total_all_prestasi) * 100, 1) : 0;
                                    ?>
                                    <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.85em;">
                                        <span><i class="fas fa-circle mr-1" style="color:<?php echo $colors[$i]; ?>"></i> <?php echo $rl; ?></span>
                                        <span class="badge badge-light"><?php echo $pct; ?>%</span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <!-- Semester Table -->
                        <div class="mt-3 table-responsive">
                            <table class="table table-sm table-bordered text-center mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Tahun Pelajaran</th>
                                        <th class="text-primary">Smt 1 (Ganjil)</th>
                                        <th class="text-success">Smt 2 (Genap)</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($tp_labels)): ?>
                                        <?php foreach ($tp_labels as $i => $tp): ?>
                                        <tr>
                                            <td class="font-weight-bold"><?php echo $tp; ?></td>
                                            <td><?php echo $smt1_counts[$i]; ?></td>
                                            <td><?php echo $smt2_counts[$i]; ?></td>
                                            <td class="font-weight-bold"><?php echo ($smt1_counts[$i] + $smt2_counts[$i]); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="4" class="text-muted text-center">Tidak ada data</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- /.Statistics Card -->

            </section>
            <!-- /.Right col -->

        </div>
        <!-- /.row (panels) -->


    </section>
    <!-- /.content -->

</div>
<!-- /.content-wrapper -->

<script>
$(document).ready(function () {
    // 1. Stacked Bar Chart (Semester 1 vs 2 per TP)
    var semCtx = document.getElementById('semesterChart').getContext('2d');
    new Chart(semCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($tp_labels); ?>,
            datasets: [
                {
                    label: 'Semester 1 (Ganjil)',
                    backgroundColor: '#01b2d1',
                    data: <?php echo json_encode($smt1_counts); ?>
                },
                {
                    label: 'Semester 2 (Genap)',
                    backgroundColor: '#28a745',
                    data: <?php echo json_encode($smt2_counts); ?>
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            tooltips: { mode: 'index', intersect: false },
            scales: {
                xAxes: [{ stacked: true }],
                yAxes: [{ stacked: true, ticks: { beginAtZero: true } }]
            },
            legend: { position: 'bottom' }
        }
    });

    // 2. Doughnut Chart (Rank Distribution)
    var rankCtx = document.getElementById('rankChart').getContext('2d');
    new Chart(rankCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($rank_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($rank_values); ?>,
                backgroundColor: ['#f39c12', '#00c0ef', '#3c8dbc', '#00a65a', '#f56954', '#d2d6de']
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            legend: { display: false },
            cutoutPercentage: 65
        }
    });
});
</script>
