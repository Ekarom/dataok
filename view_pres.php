<?php
include_once "cfg/konek.php";
include_once "cfg/secure.php";

$user_role = $_SESSION['user_role'] ?? 'admin';
$skradm = $_SESSION['skradm'] ?? '';
$my_id = $_SESSION['student_id'] ?? ''; // We should pass this from index.php or fetch it

if (!isset($_REQUEST['urut']) || empty($_REQUEST['urut'])) {
    if ($user_role === 'siswa') {
        // Find own ID if not provided
        $q_me = mysqli_query($sqlconn, "SELECT id FROM siswa WHERE nis = '$skradm'");
        $r_me = mysqli_fetch_assoc($q_me);
        $id = $r_me['id'] ?? '';
    } else {
        echo '<script>$(function() { toastr.warning("Pilih peserta didik terlebih dahulu."); setTimeout(function() { window.location.href = "arsipdata/inputprestasi"; }, 2000); });</script>';
        return;
    }
} else {
    $id = $_REQUEST['urut'];
    // SECURITY: If student, they can ONLY see their own ID
    if ($user_role === 'siswa') {
        $q_me = mysqli_query($sqlconn, "SELECT id FROM siswa WHERE nis = '$skradm'");
        $r_me = mysqli_fetch_assoc($q_me);
        $id = $r_me['id'] ?? '';
    }
}

// Get Student Information
$sqlSiswa = mysqli_query($sqlconn, "SELECT * FROM siswa WHERE id = '$id'");
$rSiswa = mysqli_fetch_array($sqlSiswa);

// Redirect if no student found
if (!$rSiswa) {
    echo '<script>$(function() { toastr.warning("Data siswa tidak ditemukan!"); setTimeout(function() { window.location.href = "'.($user_role === 'admin' ? 'arsipdata/inputprestasi' : 'dashboard').'"; }, 2000); });</script>';
    return;
}

$photo = $rSiswa['photo'] ?? '';
$nama = $rSiswa['pd'] ?? '';
$kelas = $rSiswa['kelas'] ?? '';

// Fetch ALL achievements for this student
$sqlAchievements = mysqli_query($sqlconn, "SELECT * FROM prestasi WHERE pd = '$nama' AND kelas = '$kelas' ORDER BY tgl_kegiatan DESC");
$total_prestasi = mysqli_num_rows($sqlAchievements);

// Handle empty achievements
if ($total_prestasi == 0) {
    echo '<script>$(function() { toastr.error("Belum ada data prestasi terdaftar."); setTimeout(function() { window.location.href = "'.($user_role === 'admin' ? 'arsipdata/inputprestasi' : 'dashboard').'"; }, 2000); });</script>';
    return;
}
?>
<style>
    /* ... (Existing styles) ... */
</style>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="card shadow-sm border-0">
                <div class="card-header box-shadow-0 bg-gradient-x-info d-flex align-items-center justify-content-between">
                    <h5 class="card-title text-white mb-0"><?php echo ($user_role === 'admin' ? 'Detail Data Prestasi Siswa' : 'Daftar Prestasi Saya'); ?></h5>
                    <?php if ($user_role === 'admin') { ?>
                    <div class="card-action">
                        <a href="arsipdata/inputprestasi" class="btn btn-warning btn-sm rounded-pill shadow-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                    </div>
                    <?php } ?>
                </div>
                <div class="card-body view-form-container" style="background-color: #f8f9fa;">
                    <form>
                        <div class="row">
                            <!-- Kolom Kiri: Foto & Identitas Singkat -->
                            <div class="col-md-4 text-center">
                                <div class="view-section" style="height: 100%;">
                                    <center><span class="view-section-title"><i class="fa fa-user-graduate mr-1"></i> Informasi Siswa</span></center>
                                    <center>
                                            <?php if (!empty($photo) && file_exists('file/fotopd/' . $photo)) { ?>
                                                    <a href="file/fotopd/<?php echo $photo; ?>" target="_blank" title="Klik untuk melihat foto penuh">
                                                        <img class='profile-user-img img-fluid img-circle shadow-sm border-pemenang' style='width: 150px; height: 150px; object-fit: cover; cursor: pointer;' src="file/fotopd/<?php echo $photo; ?>">
                                                    </a>
                                                <?php
                                            } else { ?>
                                                    <a href="images/default.png" target="_blank" title="Klik untuk melihat foto penuh">
                                                        <img class="profile-user-img img-fluid img-circle border-pemenang" style='width: 150px; height: 150px; object-fit: cover; cursor: pointer;' src="images/default.png" alt="User profile picture">
                                                    </a>
                                                <?php
                                            } ?>
                                    </center>
                                    <div class="mt-4">
                                        <span style="font-size: 16px; font-weight: bold; text-decoration: underline; color: #495057;">Nama Lengkap</span>
                                        <div class="mt-1 badge bg-gradient-x-info text-white" style="font-size: 14px; font-weight: bold; width: 100%; white-space: normal;"><?php echo $nama; ?></div>
                                        
                                        <hr style="border-top: 1px dashed #ced4da; margin: 15px 0;">
                                        
                                        <span style="font-size: 16px; font-weight: bold; text-decoration: underline; color: #495057;">Kelas</span>
                                        <div class="mt-1 badge bg-gradient-x-info text-white" style="font-size: 14px; font-weight: bold; width: 100%;"><?php echo $kelas; ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Kolom Kanan: Detail & Lampiran -->
                            <div class="col-md-8">
                                <div class="view-section">
                                    <center><span class="view-section-title"><i class="fa fa-medal mr-1"></i> Daftar Prestasi</span></center>
                                    
                                    <div class="table-responsive">
                                        <table id="tabelPrestasi" class="table table-bordered table-striped table-hover w-100">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <?php if ($user_role === 'admin') { ?><th>Hapus</th><?php } ?>
                                                    <th>Prestasi</th>
                                                    <th>Jenis</th>
                                                    <th>Tingkat</th>
                                                    <th>Nama Kegiatan</th>
                                                    <th>Penyelenggara</th>
                                                    <th>Lokasi</th>
                                                    <th>Tanggal</th>
                                                    <th>Bulan</th>
                                                    <th>Juara</th>
                                                    <th>Dokumen</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $noP = 1;
                                                while ($rp = mysqli_fetch_array($sqlAchievements)) {
                                                    $tgl = !empty($rp['tgl_kegiatan']) ? date('d/m/Y', strtotime($rp['tgl_kegiatan'])) : '-';
                                                    $juara_val = $rp['juara'];
                                                    $juara_badge = $juara_val;
                                                    if (in_array($juara_val, ['1', '2', '3', '4'])) {
                                                        $juara_badge = '<span class="badge bg-warning text-dark">Juara ' . $juara_val . '</span>';
                                                    } else if (in_array($juara_val, ['Harapan 1', 'Harapan 2', 'Harapan 3'])) {
                                                        $juara_badge = '<span class="badge bg-info">Juara ' . $juara_val . '</span>';
                                                    }
                                                    
                                                    $berkas_html = '-';
                                                    if (!empty($rp['pdf'])) {
                                                        $berkas_link = "file/prestasi/" . $rp['pdf'];
                                                        $berkas_html = '<button type="button" class="badge badge-primary badge-square btn-view-pdf" data-url="'.$berkas_link.'" data-title="'.$rp['prestasi'].'" title="Lihat Berkas"><i class="fa fa-file"></i></button>';
                                                    }
                                                    $btn_hapus = '<button type="button" class="badge badge-danger badge-square btn-hapus" data-id="'.$rp['id'].'" data-name="'.$rp['prestasi'].'" title="Hapus Data"><i class="fa fa-trash"></i></button>';
                                                ?>
                                                    <tr>
                                                        <td class="text-center"><?php echo $noP++; ?></td>
                                                        <?php if ($user_role === 'admin') { ?><td class="text-center"><?php echo $btn_hapus; ?></td><?php } ?>
                                                        <td><?php echo $rp['prestasi']; ?></td>
                                                        <td><?php echo $rp['jenisprestasi']; ?></td>
                                                        <td><?php echo $rp['tingkat']; ?></td>
                                                        <td><?php echo htmlspecialchars($rp['nama_kegiatan'] ?? '-'); ?></td>
                                                        <td><?php echo $rp['penyelenggara']; ?></td>
                                                        <td><?php echo $rp['lokasi']; ?></td>
                                                        <td class="text-center"><?php echo $tgl; ?></td>
                                                        <td class="text-center"><?php echo $rp['bulan']; ?></td>
                                                        <td class="text-center"><?php echo $juara_badge; ?></td>
                                                        <td class="text-center"><?php echo $berkas_html; ?></td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div> <!-- /.col-md-8 -->

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    var table = $('#tabelPrestasi').DataTable({
        "paging": false,
        "lengthChange": false,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        
    });

    // Re-adjust columns after a short delay to ensure correct alignment
    setTimeout(function() {
        table.columns.adjust().draw();
    }, 500);

    // Handle Hapus Data
    $(document).on('click', '.btn-hapus', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        
        if (confirm('Apakah Anda yakin ingin menghapus prestasi "' + name + '"?')) {
            $.ajax({
                url: 'prosespress.php',
                type: 'GET',
                data: {
                    aksi: 'hapus',
                    urut: id,
                    is_ajax: 'true'
                },
                success: function(response) {
                    if (response.trim() == 'success') {
                        toastr.success("Data berhasil dihapus!");
                        // Reload data in table or refresh
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        toastr.error("Gagal menghapus data: " + response);
                    }
                },
                error: function() {
                    toastr.error("Terjadi kesalahan server saat menghapus.");
                }
            });
        }
    });

    // Handle View PDF in Modal
    $(document).on('click', '.btn-view-pdf', function() {
        var url = $(this).data('url');
        var title = $(this).data('title');
        
        $('#pdfModalTitle').text('Dokumen: ' + title);
        $('#pdfViewer').attr('src', url);
        $('#modalPDF').modal('show');
    });

    // Clear PDF viewer when modal is closed
    $('#modalPDF').on('hidden.bs.modal', function () {
        $('#pdfViewer').attr('src', '');
    });

    // Make the PDF modal draggable (safely)
    if ($.fn.draggable) {
        $("#modalPDF .modal-content").draggable({
            handle: ".modal-header"
        });
        $("#modalPDF .modal-header").css("cursor", "move");
    }
});
</script>

<!-- Modal PDF Viewer -->
<div class="modal fade" id="modalPDF" tabindex="-1" role="dialog" aria-labelledby="pdfModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0">
            <div class="modal-header box-shadow-0 bg-gradient-x-info">
                <h5 class="modal-title text-white">Detail Dokumen Prestasi (<?php echo $nama ?>)</h5>
            </div>
            <div class="modal-body p-0">
                <iframe id="pdfViewer" src="" frameborder="0" width="100%" height="600px"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>





