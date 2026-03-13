<?php
include_once "cfg/konek.php";

?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Input Data Prestasi</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active">Input Data Prestasi</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-menu-gradient">
</div>
                     <div class="card-body text-nowrap">
    <table id="example2" class="table table-striped table-hover table-sm " style="width:100%">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="10%">Kelas</th>
                <th width="15%">NIS</th>
                <th width="10%">NISN</th>
                <th width="40%">Nama Siswa</th>
                <th width="20%">Aksi</th>
            </tr>
        </thead>
        <tbody class="align-middle">
            <?php
// Mengambil data siswa diurutkan berdasarkan nama (pd)
$sqlSiswa = mysqli_query($sqlconn, "SELECT pd, nis,nisn, kelas, id FROM siswa ORDER BY pd ASC");
$noS = 1;
while ($ds = mysqli_fetch_array($sqlSiswa)) {
?>
                <tr class="text-center">
                    <td><?php echo $noS++; ?></td>
                    <td><span class="badge badge-secondary px-2 shadow-xs"><?php echo htmlspecialchars($ds['kelas']); ?></span></td>
                    <td><?php echo htmlspecialchars($ds['nis']); ?></td>
                    <td><?php echo htmlspecialchars($ds['nisn']); ?></td>
                    <td class="text-left font-weight-bold"><?php echo htmlspecialchars($ds['pd']); ?></td>

                    <td>
                      <div class="btn-group">
                        <a href="?input&nis=<?php echo $ds['id']; ?>" class="btn badge badge-success btn-sm">
                            Input
                        </a>
                        <a href="?editpress&urut=<?php echo $ds['id']; ?>" class="btn badge badge-primary btn-sm">
                            Edit
                        </a>
                        <a href="?viewpress&urut=<?php echo $ds['id']; ?>" class="btn badge badge-info btn-sm">
                            Detail
                        </a>
                      </div>
                    </td>
                </tr>
            <?php
}?>
        </tbody>
    </table>
</div>
</div>
</div>
</div>
</div>
<!-- Global scripts provided by index.php -->
 <script>
  $(document).ready(function () {
    $('#example2').DataTable();
  });
</script>