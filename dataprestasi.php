<?php
include_once "cfg/konek.php";
?>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header box-shadow-0 bg-gradient-x-warning">
                    <h3 class="card-title text-white">Data Prestasi Siswa</h3>
                </div>
                <div class="card-body">
                    <table id="tableDataPrestasi" class="table table-striped table-hover" style="width:100%"> 
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIS</th>
                                <th>NISN</th>
                                <th>Nama Siswa</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
// Mengambil data siswa diurutkan berdasarkan nama (pd)
// Menggunakan subquery untuk cek status apakah sudah upload prestasi
$sqlSiswa = mysqli_query($sqlconn, "SELECT pd, nis, nisn, kelas, id, (SELECT COUNT(*) FROM prestasi WHERE prestasi.pd = siswa.pd AND prestasi.kelas = siswa.kelas) as jml_p FROM siswa ORDER BY pd ASC");

if ($sqlSiswa) {
    $noS = 1;
    while ($ds = mysqli_fetch_array($sqlSiswa)) {
        $has_prestasi = ($ds['jml_p'] > 0);
        $status_badge = $has_prestasi
            ? '<span class="badge bg-success badge-square">Sudah Upload</span>'
            : '<span class="badge bg-danger badge-square">Belum Upload</span>';
?>
                                <tr>
                                    <td class="text-center"><?php echo $noS++; ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($ds['nis']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($ds['nisn']); ?></td>
                                    <td class="text-left px-3"><?php echo htmlspecialchars($ds['pd']); ?></td>
                                    <td class="text-center"><?php echo $status_badge; ?></td>

                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a href="viewpress?urut=<?php echo $ds['id']; ?>" class="badge badge-success badge-square">
                                                Detail
                                            </a>
                                            <a href="arsipdata/inputprestasi?nis=<?php echo $ds['id']; ?>" class="badge badge-primary badge-square">
                                                Input
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php
    }
}
else {
    echo "<tr><td colspan='6' class='text-center'>Error: " . mysqli_error($sqlconn) . "</td></tr>";
}
?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
    <!-- Global scripts provided by index.php -->
    <script>
        $(document).ready(function() {
            var table = $('#tableDataPrestasi').DataTable( {
                scrollY:        450,
                scrollX:        true,
                scrollCollapse: true,
                paging:         false
            });

            // Tambahkan styling tambahan untuk kolom pencarian agar lebih premium
            $('.dataTables_filter input').addClass('form-control form-control-sm').css({
                'display': 'inline-block',
                'width': '200px',
                'margin-left': '10px',
                'border': '1px solid #ced4da'
            });
        });
        </script>