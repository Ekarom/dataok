<?php
include_once "cfg/konek.php";
include_once "cfg/secure.php";

if (isset($_REQUEST['urut']) && $_REQUEST['urut']) {
    $id = $_REQUEST['urut'];

    // First attempt: try to find achievement by ID (urut)
    $sql = mysqli_query($sqlconn, "SELECT 
            prestasi.id,
            prestasi.prestasi, 
            prestasi.pd, 
            prestasi.kelas, 
            prestasi.tingkat, 
            prestasi.penyelenggara, 
            prestasi.lokasi,
            prestasi.juara, 
            prestasi.pdf,
            prestasi.jenisprestasi,
            prestasi.tgl_kegiatan,
            prestasi.bulan,
            siswa.photo 
        FROM prestasi 
        LEFT JOIN siswa ON prestasi.pd = siswa.pd AND prestasi.kelas = siswa.kelas
        WHERE prestasi.id = '$id'");

    $r = mysqli_fetch_array($sql);

    // Second attempt: if not found, it might be a Student ID from dataprestasi.php
    // Fetch the latest achievement for this student
    if (!$r) {
        $sqlSiswa = mysqli_query($sqlconn, "SELECT 
                prestasi.id,
                prestasi.prestasi, 
                siswa.pd, 
                siswa.kelas, 
                prestasi.tingkat, 
                prestasi.penyelenggara, 
                prestasi.lokasi,
                prestasi.juara, 
                prestasi.pdf,
                prestasi.jenisprestasi,
                prestasi.tgl_kegiatan,
                prestasi.bulan,
                siswa.photo 
            FROM siswa 
            LEFT JOIN prestasi ON prestasi.pd = siswa.pd AND prestasi.kelas = siswa.kelas
            WHERE siswa.id = '$id' 
            ORDER BY prestasi.id DESC LIMIT 1");
        $r = mysqli_fetch_array($sqlSiswa);
    }

    if ($r) {
        $photo = $r['photo'] ?? '';
        $nama = $r['pd'] ?? '';
        $kelas = $r['kelas'] ?? '';
        $prestasi = $r['prestasi'] ?? '';
        $tingkat = $r['tingkat'] ?? '';
        $penyelenggara = $r['penyelenggara'] ?? '';
        $lokasi = $r['lokasi'] ?? '';
        $juara = $r['juara'] ?? '';
        $pdf = $r['pdf'] ?? '';
        $jenisprestasi = $r['jenisprestasi'] ?? '';
        $tgl_kegiatan = $r['tgl_kegiatan'] ?? '';
        $bulan = $r['bulan'] ?? '';
    }
    else {
        $photo = '';
        $nama = '';
        $kelas = '';
        $prestasi = '';
        $tingkat = '';
        $lokasi = '';
        $juara = '';
        $pdf = '';
        $jenisprestasi = '';
        $tgl_kegiatan = '';
        $bulan = '';
    }
?>
<style>
    .form-control:disabled, .form-control[readonly] {
        background-color: #f8f9fa;
        opacity: 1;
        cursor: not-allowed;
    }
    .card-title {
        font-weight: 600;
    }
    .bg-menu-gradient {
        background: linear-gradient(135deg, #2c3e50 0%, #01b2d1 100%);
        color: #fff;
    }
    .profile-card {
        border-top: 3px solid #01b2d1;
    }
    .label-custom {
        font-weight: 700;
        color: #333;
    }
    .attachment-container {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #dee2e6;
        background: #fff;
        padding: 5px;
    }
    .attachment-img {
        max-width: 100%;
        display: block;
        transition: transform 0.3s ease;
    }
    .attachment-img:hover {
        transform: scale(1.02);
    }
    .alert-view {
        border-radius: 10px;
        font-size: 0.9rem;
    }
    .border-pemenang {
        border: 4px solid #ffd700 !important;
        box-shadow: 0 0 15px rgba(255, 215, 0, 0.4), inset 0 0 10px rgba(255, 255, 255, 0.5);
        background: #fff;
        position: relative;
        z-index: 2;
    }
    .award-frame-wrapper {
        position: relative;
        display: inline-block;
        padding: 20px;
    }
    .frame-star {
        position: absolute;
        color: #ffd700;
        text-shadow: 0 0 5px rgba(255, 215, 0, 0.5);
        z-index: 1;
    }
    .star-top-left { top: 0; left: 0; font-size: 1.2rem; }
    .star-top-right { top: 0; right: 0; font-size: 1.2rem; }
    .star-bottom-left { bottom: 0; left: 0; font-size: 1.2rem; }
    .star-bottom-right { bottom: 0; right: 0; font-size: 1.2rem; }
    .star-center-top { top: -10px; left: 50%; transform: translateX(-50%); font-size: 1.5rem; }

    .view-section {
        background: #fff;
        padding: 15px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        margin-bottom: 20px;
        border: 1px solid #eef0f2;
    }
    .view-section-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: #495057;
        margin-bottom: 20px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #007bff;
        display: inline-block;
        padding-bottom: 3px;
    }
</style>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Detail Prestasi</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active">Detail Prestasi</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-menu-gradient">
                    <div class="card-tools ml-auto">
                        <a href="?input" class="btn btn-warning btn-sm">
                            <i class="fa fa-arrow-left mr-1"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body view-form-container" style="background-color: #f8f9fa;">
                    <form>
                        <input type="hidden" name="id" value="<?php echo $r['id'] ?? ''; ?>">
                        
                        <div class="row">
                            <!-- Kolom Kiri: Foto & Identitas Singkat -->
                            <div class="col-md-4 text-center">
                                <div class="view-section" style="height: 100%;">
                                    <center><span class="view-section-title"><i class="fa fa-user-graduate mr-1"></i> Informasi Siswa</span></center>
                                    <center>
                                        <div class="award-frame-wrapper">
                                            <i class="fas fa-star frame-star star-center-top"></i>
                                            <i class="fas fa-star frame-star star-top-left"></i>
                                            <i class="fas fa-star frame-star star-top-right"></i>
                                            <i class="fas fa-star frame-star star-bottom-left"></i>
                                            <i class="fas fa-star frame-star star-bottom-right"></i>
                                            <?php if (!empty($photo) && file_exists('file/fotopd/' . $photo)) { ?>
                                                <a href="file/fotopd/<?php echo $photo; ?>" target="_blank" title="Klik untuk melihat foto penuh">
                                                    <img class='profile-user-img img-fluid img-circle shadow-sm border-pemenang' style='width: 150px; height: 150px; object-fit: cover; cursor: pointer;' src="file/fotopd/<?php echo $photo; ?>">
                                                </a>
                                            <?php
    }
    else { ?>
                                                <a href="images/default.png" target="_blank" title="Klik untuk melihat foto penuh">
                                                    <img class="profile-user-img img-fluid img-circle border-pemenang" style='width: 150px; height: 150px; object-fit: cover; cursor: pointer;' src="images/default.png" alt="User profile picture">
                                                </a>
                                            <?php
    }?>
                                        </div>
                                    </center>
                                    <div class="mt-4">
                                        <span style="font-size: 16px; font-weight: bold; text-decoration: underline; color: #495057;">Nama Lengkap</span>
                                        <div class="mt-1 badge bg-menu-gradient px-3 py-2" style="font-size: 14px; font-weight: bold; width: 100%; white-space: normal;"><?php echo $nama; ?></div>
                                        
                                        <hr style="border-top: 1px dashed #ced4da; margin: 15px 0;">
                                        
                                        <span style="font-size: 16px; font-weight: bold; text-decoration: underline; color: #495057;">Kelas</span>
                                        <div class="mt-1 badge bg-menu-gradient px-3 py-2" style="font-size: 14px; font-weight: bold; width: 100%;"><?php echo $kelas; ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Kolom Kanan: Detail & Lampiran -->
                            <div class="col-md-8">
                                <div class="view-section">
                                    <center><span class="view-section-title"><i class="fa fa-medal mr-1"></i> Detail Prestasi</span></center>
                                    
                                    <div class="mt-2">
                                        <div class="form-group row mb-4">
                                            <label class="col-sm-4 col-form-label label-custom">Prestasi</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control form-control-sm" value="<?php echo $prestasi; ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="form-group row mb-4">
                                            <label class="col-sm-4 col-form-label label-custom">Jenis Prestasi</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control form-control-sm" value="<?php echo $jenisprestasi; ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="form-group row mb-4">
                                            <label class="col-sm-4 col-form-label label-custom">Tingkat</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control form-control-sm" value="<?php echo $tingkat; ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="form-group row mb-4">
                                            <label class="col-sm-4 col-form-label label-custom">Tanggal Kegiatan</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control form-control-sm" value="<?php echo !empty($tgl_kegiatan) ? date('m/d/Y', strtotime($tgl_kegiatan)) : '-'; ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="form-group row mb-4">
                                            <label class="col-sm-4 col-form-label label-custom">Nama Kegiatan</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control form-control-sm" value="<?php echo htmlspecialchars($r['nama_kegiatan'] ?? '-'); ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="form-group row mb-4">
                                            <label class="col-sm-4 col-form-label label-custom">Penyelenggara</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control form-control-sm" value="<?php echo $penyelenggara; ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="form-group row mb-4">
                                            <label class="col-sm-4 col-form-label label-custom">Lokasi</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control form-control-sm" value="<?php echo $lokasi; ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="form-group row mb-4">
                                            <label class="col-sm-4 col-form-label label-custom">Juara Ke-</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control form-control-sm" value="<?php
    if (in_array($juara, ['1', '2', '3', '4'])) {
        echo 'Juara ' . $juara;
    }
    else if (in_array($juara, ['Harapan 1', 'Harapan 2', 'Harapan 3'])) {
        echo 'Juara ' . $juara;
    }
    else {
        echo $juara;
    }
?>" readonly>
                                            </div>
                                        </div>

                                        <div class="form-group row mb-4">
                                            <label class="col-sm-4 col-form-label label-custom">Bulan</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control form-control-sm" value="<?php echo $bulan; ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="form-group row mb-4">
                                            <label class="col-sm-4 col-form-label label-custom">Lampiran</label>
                                            <div class="col-sm-8">
                                                <?php
    if (!empty($pdf)) {
        $files = explode(',', $pdf);
        foreach ($files as $index => $f) {
            if (!empty($f)) {
                $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                $is_image = in_array($ext, ['jpg', 'jpeg', 'png']);
                if ($is_image) {
                    echo "<div class='attachment-container mb-3 text-center'><a href='file/prestasi/$f' target='_blank' title='Klik untuk melihat gambar penuh'><img src='file/prestasi/$f' class='attachment-img'></a></div>";
                }
                else {
                    echo "<div class='attachment-container mb-3'><embed type='application/pdf' src='file/prestasi/$f' width='100%' height='500px' style='border:none;'></div>";
                }
            }
        }
    }
    else {
        echo "<div class='alert alert-secondary text-center alert-view m-0'><i class='fa fa-info-circle mr-1'></i> Tidak ada lampiran berkas.</div>";
    }
?>
                                            </div>
                                        </div>
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

<?php
}?>

