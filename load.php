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
$tp_labels = [];
$smt1_counts = [];
$smt2_counts = [];
$juara_stats = [];
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
                        if (!isset($juara_stats[$j]))
                            $juara_stats[$j] = 0;
                        $juara_stats[$j]++;

                        $month = 0;
                        if (!empty($row['tgl_kegiatan']) && $row['tgl_kegiatan'] !== '0000-00-00') {
                            $month = (int)date('n', strtotime($row['tgl_kegiatan']));
                        }
                        else {
                            $m_map = ['Januari' => 1, 'Februari' => 2, 'Maret' => 3, 'April' => 4, 'Mei' => 5, 'Juni' => 6,
                                'Juli' => 7, 'Agustus' => 8, 'September' => 9, 'Oktober' => 10, 'November' => 11, 'Desember' => 12];
                            $month = $m_map[$row['bulan']] ?? 0;
                        }
                        if ($month >= 7 || $month == 0)
                            $s1++;
                        else
                            $s2++;
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

// =====================================================================
// Data dropdown Tahun Pelajaran untuk Grafik Rata-rata Nilai
// =====================================================================
$tp_list = [];
$q_tp = mysqli_query($sqlconn, "SELECT dbname, tahun FROM dbset ORDER BY tahun DESC");
if ($q_tp) {
    while ($r_tp = mysqli_fetch_assoc($q_tp)) {
        $tp_list[] = $r_tp;
    }
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
            <section class="col-lg-5 connectedSortable">

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


                <!-- Grafik Juara Berprestasi -->
                <div class="card">
                    <div class="card-header bg-menu-gradient">
                        <h3 class="card-title"><i class="fas fa-trophy mr-1"></i> Grafik Siswa Berprestasi</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filter Row -->
                        <div class="row mb-3">
                            <div class="col-md-4 col-6 mb-2">
                                <select id="filterBulanJuara" class="form-control form-control-sm">
                                    <option value="">Semua Bulan</option>
                                    <option value="1">Januari</option>
                                    <option value="2">Februari</option>
                                    <option value="3">Maret</option>
                                    <option value="4">April</option>
                                    <option value="5">Mei</option>
                                    <option value="6">Juni</option>
                                    <option value="7">Juli</option>
                                    <option value="8">Agustus</option>
                                    <option value="9">September</option>
                                    <option value="10">Oktober</option>
                                    <option value="11">November</option>
                                    <option value="12">Desember</option>
                                </select>
                            </div>
                            <div class="col-md-4 col-6 mb-2">
                                <select id="filterSemesterJuara" class="form-control form-control-sm">
                                    <option value="">Semua Semester</option>
                                    <option value="1">Semester 1 (Ganjil)</option>
                                    <option value="2">Semester 2 (Genap)</option>
                                </select>
                            </div>
                            <div class="col-md-4 col-6 mb-2">
                                <select id="filterTpJuara" class="form-control form-control-sm">
                                    <option value="">Semua Tahun</option>
                                </select>
                            </div>
                        </div>
                        <!-- Chart -->
                        <div class="chart">
                            <canvas id="juaraChart" style="min-height: 280px; height: 280px; max-height: 280px;"></canvas>
                        </div>
                        <!-- Tabel Persentase Juara per Bulan -->
                        <div class="mt-3 table-responsive" id="tabelPersenJuara" style="display:none;">
                            <h6 class="font-weight-bold text-center mb-2"><i class="fas fa-percentage mr-1"></i> Persentase Juara per Bulan</h6>
                            <table class="table table-sm table-bordered table-striped text-center mb-0">
                                <thead>
                                    <tr>
                                        <th>Bulan</th>
                                        <th style="color:#f39c12">Juara 1</th>
                                        <th style="color:#00c0ef">Juara 2</th>
                                        <th style="color:#3c8dbc">Juara 3</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody id="bodyPersenJuara"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- /.Grafik Juara Berprestasi -->

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
    // Grafik Persentase Siswa Berprestasi per Bulan
    var juaraCtx = document.getElementById('juaraChart').getContext('2d');
    var juaraChart = new Chart(juaraCtx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [
                { label: 'Juara 1', backgroundColor: '#f39c12', data: [] },
                { label: 'Juara 2', backgroundColor: '#00c0ef', data: [] },
                { label: 'Juara 3', backgroundColor: '#3c8dbc', data: [] }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                xAxes: [{ ticks: { autoSkip: false } }],
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        max: 100,
                        callback: function(v) { return v + '%'; }
                    },
                    scaleLabel: { display: true, labelString: 'Persentase (%)' }
                }]
            },
            legend: { display: true, position: 'bottom' },
            tooltips: {
                callbacks: {
                    label: function(ti, d) {
                        return d.datasets[ti.datasetIndex].label + ': ' + ti.yLabel + '%';
                    }
                }
            }
        }
    });

    var tahunLoaded = false;

    function loadJuaraChart() {
        var bulan    = $('#filterBulanJuara').val();
        var semester = $('#filterSemesterJuara').val();
        var tahun    = $('#filterTpJuara').val();
        $.ajax({
            url: 'ajax_juara_chart.php',
            type: 'GET',
            data: { bulan: bulan, semester: semester, tahun: tahun },
            dataType: 'json',
            success: function(res) {
                // Isi dropdown tahun (sekali saja)
                if (!tahunLoaded && res.tahun_list && res.tahun_list.length > 0) {
                    var sel = $('#filterTpJuara');
                    sel.find('option:not(:first)').remove();
                    $.each(res.tahun_list, function(i, yr) {
                        sel.append('<option value="' + yr + '">' + yr + '</option>');
                    });
                    tahunLoaded = true;
                }

                // Update chart datasets
                juaraChart.data.labels = res.labels;
                if (res.datasets && res.datasets.length >= 3) {
                    juaraChart.data.datasets[0].data = res.datasets[0].data;
                    juaraChart.data.datasets[1].data = res.datasets[1].data;
                    juaraChart.data.datasets[2].data = res.datasets[2].data;
                }
                juaraChart.update();

                // Render tabel persentase juara per bulan
                var tbody = $('#bodyPersenJuara');
                tbody.empty();
                if (res.monthly_stats && res.monthly_stats.length > 0) {
                    $.each(res.monthly_stats, function(i, s) {
                        tbody.append(
                            '<tr>' +
                            '<td class="font-weight-bold">' + s.bulan + '</td>' +
                            '<td><span class="badge" style="background:#f39c12;color:#fff;font-size:12px;">' + s.juara_1 + ' <small>(' + s.pct_1 + '%)</small></span></td>' +
                            '<td><span class="badge" style="background:#00c0ef;color:#fff;font-size:12px;">' + s.juara_2 + ' <small>(' + s.pct_2 + '%)</small></span></td>' +
                            '<td><span class="badge" style="background:#3c8dbc;color:#fff;font-size:12px;">' + s.juara_3 + ' <small>(' + s.pct_3 + '%)</small></span></td>' +
                            '<td class="font-weight-bold">' + s.total + '</td>' +
                            '</tr>'
                        );
                    });
                    $('#tabelPersenJuara').show();
                } else {
                    $('#tabelPersenJuara').hide();
                }
            }
        });
    }

    loadJuaraChart();
    $('#filterBulanJuara, #filterSemesterJuara, #filterTpJuara').on('change', loadJuaraChart);
});
</script>
