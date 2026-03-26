<?php
// Laporan Prestasi - Global DataTables assets provided by index.php
?>


    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    
                    <!-- Filter Section -->
                    <div class="card">
                        <div class="card-header bg-menu-gradient">
                            <h3 class="card-title text-bold"><i class="fas fa-filter mr-1"></i> Rekap Prestasi</h3>
                        </div>
                                          <div class="card-body text-nowrap">
                            <div class="row">
                                <div class="col-md-2 mb-2">
                                    <select id="rekap_triwulan" class="form-control" onchange="updateMonthsByQuarter(this.value)">
                                        <option value="">- Triwulan -</option>
                                        <option value="1">Triwulan I</option>
                                        <option value="2">Triwulan II</option>
                                        <option value="3">Triwulan III</option>
                                        <option value="4">Triwulan IV</option>
                                    </select>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <select id="rekap_m1" class="form-control">
                                        <option value="Januari">Januari</option>
                                        <option value="Februari">Februari</option>
                                        <option value="Maret">Maret</option>
                                        <option value="April">April</option>
                                        <option value="Mei">Mei</option>
                                        <option value="Juni">Juni</option>
                                        <option value="Juli">Juli</option>
                                        <option value="Agustus">Agustus</option>
                                        <option value="September">September</option>
                                        <option value="Oktober">Oktober</option>
                                        <option value="November">November</option>
                                        <option value="Desember">Desember</option>
                                    </select>
                                </div>
                                <div class="col-md-1 text-center py-2">
                                    <span class="text-muted">s/d</span>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <select id="rekap_m2" class="form-control">
                                        <option value="Januari">Januari</option>
                                        <option value="Februari">Februari</option>
                                        <option value="Maret">Maret</option>
                                        <option value="April">April</option>
                                        <option value="Mei">Mei</option>
                                        <option value="Juni">Juni</option>
                                        <option value="Juli">Juli</option>
                                        <option value="Agustus">Agustus</option>
                                        <option value="September">September</option>
                                        <option value="Oktober">Oktober</option>
                                        <option value="November">November</option>
                                        <option value="Desember" selected>Desember</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <select id="rekap_kejuaraan" class="form-control">
                                        <option value="">- Semua Kejuaraan -</option>
                                        <?php
$q_kej = mysqli_query($sqlconn, "SELECT DISTINCT prestasi FROM prestasi WHERE prestasi != '' ORDER BY prestasi ASC");
while ($rk = mysqli_fetch_array($q_kej)) {
    echo "<option value='" . htmlspecialchars($rk['prestasi'], ENT_QUOTES) . "'>" . $rk['prestasi'] . "</option>";
}
?>
                                    </select>
                                </div>
                                <div class="col-md-1 mb-2">
                                    <input type="number" id="rekap_y" class="form-control" value="<?php echo date('Y'); ?>">
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-dark btn-block" onclick="printRekapTriwulan()">
                                        <i class="fa fa-print"></i>
                                    </button>
                                </div>
                     <div class="card-body text-nowrap">
<div class="table-responsive">
                                   <table id="example2" class="table table-striped table-hover table-sm" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th width="3%">No</th>
                                            <th width="15%">Nama Peserta Didik</th>
                                            <th width="5%">Kelas</th>
                                            <th width="8%">Pencapaian</th>
                                            <th width="8%">Jenis</th>
                                            <th width="12%">Nama Kegiatan</th>
                                            <th width="8%">Tanggal</th>
                                            <th width="8%">Tingkat</th>
                                            <th width="12%">Penyelenggara</th>
                                            <th width="10%">Lokasi</th>
                                            <th width="8%">Bulan</th>
                                            <th width="3%">Log</th>
                                        </tr>
                                    </thead>
                                    <tbody class="align-middle">
                                        <?php
$sql = mysqli_query($sqlconn, "SELECT * FROM prestasi ORDER BY id DESC");
if ($sql) {
    $no = 0;
    while ($s = mysqli_fetch_array($sql)) {
        $no++;
        $tgl_fmt = (!empty($s['tgl_kegiatan']) && $s['tgl_kegiatan'] != '0000-00-00') ? date('d-m-Y', strtotime($s['tgl_kegiatan'])) : '-';
?>
                                                <tr class="text-center">
                                                    <td><?php echo $no; ?></td>
                                                    <td class="text-left font-weight-bold"><?php echo htmlspecialchars($s['pd']); ?></td>
                                                    <td>
                                                        <span class="badge badge-secondary"><?php echo htmlspecialchars($s['kelas']); ?></span>
                                                    </td>
                                                    <td>
                                                        <?php
                                                           $juara_val = $s['juara'];
                                                           $juara_badge = '<span class="badge badge-warning">' . htmlspecialchars($juara_val) . '</span>';
                                                           if (in_array($juara_val, ['1', '2', '3', '4'])) {
                                                               $juara_badge = '<span class="badge bg-warning text-dark">Juara ' . $juara_val . '</span>';
                                                           } else if (in_array($juara_val, ['Harapan 1', 'Harapan 2', 'Harapan 3'])) {
                                                               $juara_badge = '<span class="badge bg-info">Juara ' . $juara_val . '</span>';
                                                           }
                                                           echo $juara_badge;
                                                        ?>
                                                    </td>
                                                    <td class="small"><?php echo htmlspecialchars($s['jenisprestasi']); ?></td>
                                                    <td class="text-left small font-weight-bold"><?php echo htmlspecialchars($s['nama_kegiatan']); ?></td>
                                                    <td class="small"><?php echo $tgl_fmt; ?></td>
                                                    <td>
                                                        <span class="badge badge-info"><?php echo htmlspecialchars($s['tingkat']); ?></span>
                                                    </td>
                                                    <td class="text-left small"><?php echo htmlspecialchars($s['penyelenggara']); ?></td>
                                                    <td class="text-left small"><?php echo htmlspecialchars($s['lokasi']); ?></td>
                                                    <td class="small"><?php echo htmlspecialchars($s['bulan']); ?></td>
                                                    <td class="small text-muted"><?php echo $s['date']; ?></td>
                                                </tr>
                                        <?php
    }
}
?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    $('#example2').DataTable({
        responsive: true,
    });
});

function updateMonthsByQuarter(q) {
    if (!q) return;
    const m1 = $('#rekap_m1');
    const m2 = $('#rekap_m2');
    
    if (q == '1') {
        m1.val('Januari');
        m2.val('Maret');
    } else if (q == '2') {
        m1.val('April');
        m2.val('Juni');
    } else if (q == '3') {
        m1.val('Juli');
        m2.val('September');
    } else if (q == '4') {
        m1.val('Oktober');
        m2.val('Desember');
    }
}

function printRekapTriwulan() {
    var m1 = $('#rekap_m1').val();
    var m2 = $('#rekap_m2').val();
    var y = $('#rekap_y').val();
    var tw = $('#rekap_triwulan').val();
    var kej = $('#rekap_kejuaraan').val();
    var db = '<?php echo isset($db_req) ? $db_req : ""; ?>';
    
    var url = 'print_rekap_triwulan.php?m1=' + m1 + '&m2=' + m2 + '&y=' + y + '&db=' + db;
    if (tw) url += '&tw=' + tw;
    if (kej) url += '&kej=' + encodeURIComponent(kej);
    
    window.open(url, '_blank');
}
</script>
