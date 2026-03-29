<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include "cfg/secure.php";
?>
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
                        $row = $query ? mysqli_fetch_assoc($query) : null;
                        $prestasi = $row ? $row['total'] : 0;
                    ?>
                    <h3 class="text-white"><?php echo $prestasi; ?></h3>
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
                        $row = $query ? mysqli_fetch_assoc($query) : null;
                        $legalisir = $row ? $row['total'] : 0;
                    ?>
                    <h3 class="text-white"><?php echo $legalisir; ?></h3>
                    <p>Laporan Legalisir</p>
                </div>
                <div class="icon"><i class="ion ion-archive"></i></div>
                <a href="?legalisir" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <!-- Box 3: Siswa (Only for Level 1) -->
        <?php if (isset($lv) && $lv == '1') { ?>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-4">
                <div class="inner">
                    <?php
    $query = mysqli_query($sqlconn, "SELECT COUNT(*) as total FROM siswa");
    $row = $query ? mysqli_fetch_assoc($query) : null;
    $siswa = $row ? $row['total'] : 0;
?>
                    <h3 class="text-white"><?php echo $siswa; ?></h3>
                    <p>Manajemen PD</p>
                </div>
                <div class="icon"><i class="ion ion-person-stalker"></i></div>
                <a href="datasiswa" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <!-- Box 4: User (Only for Level 1) -->
        <div class="col-lg-2 col-6">
            <div class="small-box bg-5">
                <div class="inner">
                    <?php
    $query = mysqli_query($sqlconn, "SELECT COUNT(*) as total FROM usera");
    $row = $query ? mysqli_fetch_assoc($query) : null;
    $user = $row ? $row['total'] : 0;
?>
                    <h3 class="text-white"><?php echo $user; ?></h3>
                    <p>Manajemen User</p>
                </div>
                <div class="icon"><i class="ion ion-person-stalker"></i></div>
                <a href="usermanagement" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <?php
}?>

    </div>
    <!-- /.row (stat cards) -->

    <!-- Panels row: Welcome, History Log | User Online + Statistics -->
    <div class="row">


        <!-- Left col -->
        <section class="col-lg-5 connectedSortable">

            <!-- Welcome Card -->
            <div class="card">
                <div class="card-header box-shadow-0 bg-gradient-x-info">
                    <h5 class="card-title text-white">Selamat Datang, <b><?php echo $nama; ?></b></h5>
                </div>
                <div class="card-body border">
                    <div class="card">
                        <div class="card-header border">
                            <h5 class="font-weight-bold"><i class="fas fa-info-circle mr-1"></i> &nbsp;Informasi Terbaru</h5>
                        </div>
                        <div class="card-body border">
                            Harap Teliti Sebelum Menginput Prestasi Siswa. Terima Kasih.
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.Welcome Card -->

            <!-- History Log Card -->
            <div class="card direct-chat">
                <div class="card-header box-shadow-0 bg-gradient-x-info">
                    <h5 class="card-title text-white">History Log</h5>
                </div>
                <div class="card-body">
                    <div class="direct-chat-messages">
                        <?php
                            if (isset($log1) && $log1 && mysqli_num_rows($log1) > 0) {
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
                                }
                            } else {
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
            <!-- Grafik Juara Berprestasi -->
            <div class="card">
                <div class="card-header box-shadow-0 bg-gradient-x-info">
                    <h5 class="card-title text-white">Grafik Siswa Berprestasi</h5>
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
                        <h6 class="font-weight-bold text-center mb-2"><i class="fas fa-percentage mr-1"></i>
                            Persentase Juara per Bulan</h6>
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
                            callback: function (v) { return v + '%'; }
                        },
                        scaleLabel: { display: true, labelString: 'Persentase (%)' }
                    }]
                },
                legend: { display: true, position: 'bottom' },
                tooltips: {
                    callbacks: {
                        label: function (ti, d) {
                            return d.datasets[ti.datasetIndex].label + ': ' + ti.yLabel + '%';
                        }
                    }
                }
            }
        });

        var tahunLoaded = false;

        function loadJuaraChart() {
            var bulan = $('#filterBulanJuara').val();
            var semester = $('#filterSemesterJuara').val();
            var tahun = $('#filterTpJuara').val();
            $.ajax({
                url: 'ajax_juara_chart.php',
                type: 'GET',
                data: { bulan: bulan, semester: semester, tahun: tahun },
                dataType: 'json',
                success: function (res) {
                    // Isi dropdown tahun (sekali saja)
                    if (!tahunLoaded && res.tahun_list && res.tahun_list.length > 0) {
                        var sel = $('#filterTpJuara');
                        sel.find('option:not(:first)').remove();
                        $.each(res.tahun_list, function (i, yr) {
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
                        $.each(res.monthly_stats, function (i, s) {
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