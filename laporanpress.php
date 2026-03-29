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
                        <div class="card-header box-shadow-0 bg-gradient-x-info">
                            <h5 class="card-title text-white">Laporan Data Prestasi Peserta Didik</h5>
                        </div>
                        <div class="card-body">
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
                                    <select class="form-control " id="rekap_m1">
                                        <option value="">- Pilih Bulan -</option>
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
                                <div class="col text-center">
                                    <b>s/d</b>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <select class="form-control" id="rekap_m2">
                                        <option value="">- Pilih Bulan -</option>
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
                                <div class="col-md-3 mb-2">
                                    <select class="form-control" id="rekap_kejuaraan">
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
                                <div class="col-md-1 mb-2">
                                    <button type="button" class="btn btn-dark btn-block" onclick="printRekapTriwulan()">
                                        <i class="fa fa-print"></i>
                                    </button>
                                </div>
                            </div>                            
<div class="card-body">
<table class="table table-bordered" style="width:100%">
                        <thead class="bg-gradient-x-secondary">
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
        }
        else if (in_array($juara_val, ['Harapan 1', 'Harapan 2', 'Harapan 3'])) {
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
            // Initialize Select2 with Placeholders (with existence check)
        const initS2 = () => {
            if (typeof $.fn.select2 !== 'undefined') {
                $("#rekap_kejuaraan").select2({
                    placeholder: "Pilih Jenis Prestasi",
                    allowClear: false,
                    width: '100%',
                    minimumResultsForSearch: 0
                });
                $("#rekap_m1").select2({
                    placeholder: "Pilih Bulan Awal",
                    allowClear: false,
                    width: '100%',
                    minimumResultsForSearch: 0
                });
                $("#rekap_m2").select2({
                    placeholder: "Pilih Bulan Akhir",
                    allowClear: false,
                    width: '100%',
                    minimumResultsForSearch: 0
                });
                $("#rekap_triwulan").select2({
                    placeholder: "Pilih Triwulan",
                    allowClear: false,
                    width: '100%',
                    minimumResultsForSearch: 0
                });
            } else {
                console.warn("Select2 not found, retrying...");
                setTimeout(initS2, 100);
            }
        };
        initS2();

        $(document).ready(function() {
            $('table.table').DataTable( {
            scrollY:        450,
            scrollX:        true,
            scrollCollapse: true,
            paging:         false,
            // fixedColumns:   {
            //     leftColumns: 3
            // }
        } );
            // Tambahkan styling tambahan untuk kolom pencarian agar lebih premium
            $('.dataTables_filter input').addClass('form-control form-control-sm').css({
                'display': 'inline-block',
                'width': '200px',
                'margin-left': '10px',
                'border': '1px solid #ced4da'
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
    var db = '<?php echo $database ?? ""; ?>';
    
    var url = 'print_rekap_triwulan.php?m1=' + m1 + '&m2=' + m2 + '&y=' + y + '&db=' + db;
    if (tw) url += '&tw=' + tw;
    if (kej) url += '&kej=' + encodeURIComponent(kej);
    
    window.open(url, '_blank');
}
</script>
