<?php
include_once "cfg/konek.php";
?>
<style>
    /* Styling headers to match screenshot */
    #example2 thead th {
        background-color: #5c6771 !important;
        color: #ffffff !important;
        text-transform: uppercase;
        font-size: 0.85rem;
        font-weight: 700;
        border: 1px solid #dee2e6;
        vertical-align: middle;
        text-align: center;
    }

    /* Styling cells */
    #example2 tbody td {
        vertical-align: middle;
        font-size: 0.9rem;
    }

    /* Style for action buttons to match screenshot boxes */
    .btn-action-container {
        display: flex;
        justify-content: center;
        gap: 3px;
    }

    .btn-action {
        padding: 2px 6px !important;
        font-size: 10px !important;
        font-weight: 600;
        text-transform: capitalize;
        border-radius: 2px !important;
        min-width: 40px;
    }
</style>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-none">
                <div class="card-header box-shadow-0 bg-gradient-x-warning">
                    <h5 class="card-title text-white">Input Data Prestasi</h5>
                </div>
                <div class="card-body p-0">
                    <div class="card-body">
                        <div class="dataTables_wrapper">
                            <table id="example2" class="table table-bordered table-striped table-hover text-nowrap"
                                style="width:100%">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>NIS</th>
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
                                    $noS = 1;
                                    while ($ds = mysqli_fetch_array($sqlSiswa)) {
                                        $has_prestasi = ($ds['jml_p'] > 0);
                                        $status_badge = $has_prestasi
                                            ? '<span class="badge bg-success">Sudah Upload</span>'
                                            : '<span class="badge bg-danger">Belum Upload</span>';
                                        ?>
                                        <tr>
                                            <td class="text-center"><?php echo $noS++; ?></td>
                                            <td class="text-center"><?php echo htmlspecialchars($ds['nis']); ?></td>
                                            <td class="text-left px-3"><?php echo htmlspecialchars($ds['pd']); ?></td>
                                            <td class="text-center"><?php echo $status_badge; ?></td>

                                            <td class="text-center">
                                                <div class="btn-group">

                                                    <a href="viewpress?urut=<?php echo $ds['id']; ?>"
                                                        class="btn badge bg-success flat" title="Detail Prestasi">
                                                        Detail
                                                    </a>
                                                    <a href="arsipdata/inputprestasi?nis=<?php echo $ds['id']; ?>"
                                                        class="btn badge bg-primary flat" title="Input Prestasi">
                                                        Input
                                                    </a>
                                                    <a href="editpress?urut=<?php echo $ds['id']; ?>"
                                                        class="btn badge bg-warning flat" title="Edit Prestasi">
                                                        Edit
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Global scripts provided by index.php -->
    <script>
        $(document).ready(function () {
            var table = $('#example2').DataTable({
                "paging": false,
                "scrollY": "450px",
                "scrollCollapse": true,
                "lengthChange": false,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": false,
                "scrollX": true,
                "language": {
                    "search": "Search:",
                    "paginate": {
                    }
                }
            });
            
            // Adjust columns on window resize to keep header/body in sync
            $(window).on('resize', function() {
                table.columns.adjust();
            });
            
            // Initial adjustment
            setTimeout(function() {
                table.columns.adjust();
            }, 500);
        });
    </script>