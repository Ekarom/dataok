<?php
include "cfg/konek.php";
include "cfg/secure.php";

if($_REQUEST['urut']) {

        $id = $_REQUEST['urut'];

        // mengambil data berdasarkan id

        // dan menampilkan data ke dalam form modal bootstrap

        //$sql = mysqli_query($sqlconn,"SELECT * FROM data_pd WHERE no_pes = '$id'");

        

        $sql = mysqli_query($sqlconn,"SELECT 
            prestasi.id,
            prestasi.prestasi, 
            prestasi.pd, 
            prestasi.kelas, 
            prestasi.tingkat, 
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
        
        if ($r) {
            $photo = $r['photo'] ?? '';
            $nama = $r['pd'] ?? '';
            $kelas = $r['kelas'] ?? '';
            $prestasi = $r['prestasi'] ?? '';
            $tingkat = $r['tingkat'] ?? '';
            $lokasi = $r['lokasi'] ?? '';
            $juara = $r['juara'] ?? '';
            $pdf = $r['pdf'] ?? '';
            $jenisprestasi = $r['jenisprestasi'] ?? '';
            $tgl_kegiatan = $r['tgl_kegiatan'] ?? '';
            $bulan = $r['bulan'] ?? '';
        } else {
             // Handle unreachable case usually, or just empty
             $photo = ''; $nama = ''; $kelas = ''; $prestasi = ''; 
             $tingkat = ''; $lokasi = ''; $juara = ''; $pdf = '';
             $jenisprestasi = ''; $tgl_kegiatan = ''; $bulan = '';
        }
               
?>
<style>
    .modal-view-body {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 0 0 10px 10px;
    }
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
        margin-bottom: 15px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #007bff;
        display: inline-block;
        padding-bottom: 3px;
    }
    .view-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 4px;
        display: block;
    }
    .view-value {
        font-size: 0.9rem;
        color: #212529;
        font-weight: 500;
        padding: 8px 12px;
        background: #f1f3f5;
        border-radius: 8px;
        min-height: 38px;
        display: flex;
        align-items: center;
        border: 1px solid #e9ecef;
    }
    .view-icon {
        width: 20px;
        margin-right: 8px;
        color: #007bff;
        text-align: center;
    }
    .attachment-container {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #dee2e6;
        background: #fff;
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
</style>

<div class="modal-view-body">
    <form>
        <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
            
        <!-- Section 1: Data Siswa -->
        <div class="view-section">
            <center><span class="view-section-title"><i class="fa fa-user-graduate mr-1"></i> Informasi Siswa</span></center>
           <?php 

                         if($r['photo'] !="")

                         {

                         

                         ?>

                  <center><img  class='profile-user-img img-fluid img-circle shadow-sm' style='width: 150px; height: 150px; object-fit: cover; border: 2px solid #fff;' src="file/fotopd/<?php echo $r['photo']; ?>"></center>

                   <span id="status2" ></span>



 <?php }

 else

 {

?>

<center><img class="profile-user-img img-fluid img-circle" src="images/default.png" alt="User profile picture"></center>

                   <span id="status2" ></span>

                   <?php } ?>

                    <center><span style="font-size: 18px; font-weight: bold; text-decoration: underline;">Nama Siswa</span></center>
                    <center><span class="badge bg-menu-gradient"style="font-size: 15px; font-weight: bold;"><?php echo $r['pd']; ?></span></center>
                    <center><span style="font-size: 18px; font-weight: bold; text-decoration: underline;">Kelas</span></center>
                    <center><span class="badge bg-menu-gradient"style="font-size: 15px; font-weight: bold;"><?php echo $r['kelas']; ?></span></center>
 </div>

        <!-- Attachment Preview -->
        <div class="view-section">
            <center><span class="view-section-title"><i class="fa fa-paperclip mr-1"></i> Lampiran Berkas</span></center>
            <div class="mt-1">
                <?php 
                if (!empty($r['pdf'])) {
                    $files = explode(',', $r['pdf']);
                    foreach ($files as $index => $f) {
                        if (!empty($f)) {
                            $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                            $is_image = in_array($ext, ['jpg', 'jpeg', 'png']);                       
                            if ($is_image) {
                                echo "<div class='attachment-container mb-3 text-center'><img src='file/prestasi/$f' class='attachment-img'></div>";
                            } else {
                                echo "<div class='attachment-container mb-3'><embed type='application/pdf' src='file/prestasi/$f' width='100%' height='500px' style='border:none;'></div>";
                            }
                        }
                    }
                } else {
                    echo "<div class='alert alert-secondary text-center alert-view m-0'><i class='fa fa-info-circle mr-1'></i> Tidak ada lampiran berkas.</div>";
                }
                ?>
            </div>
        </div>
        <!-- Section 2: Detail Prestasi -->
        <div class="view-section">
            <center><span class="view-section-title"><i class="fa fa-medal mr-1"></i> Detail Prestasi</span></center>
            <div class="row mt-2">
                <div class="col-md-12 mb-3">
                    <label class="view-label">Nama Prestasi / Kegiatan</label>
                    <div class="view-value"><i class="fa fa-award view-icon"></i> <?php echo $r['prestasi']; ?></div>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="view-label">Jenis Prestasi</label>
                    <div class="view-value"><i class="fa fa-list-alt view-icon"></i> <?php echo $r['jenisprestasi']; ?></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="view-label">Juara</label>
                    <div class="view-value"><i class="fa fa-trophy view-icon" style="color:#ffc107;"></i> <?php echo $r['juara']; ?></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="view-label">Tingkat</label>
                    <div class="view-value"><i class="fa fa-layer-group view-icon"></i> <?php echo $r['tingkat']; ?></div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="view-label">Tanggal Pelaksanaan</label>
                    <div class="view-value"><i class="fa fa-calendar-check view-icon"></i> <?php echo date('d-m-Y', strtotime($r['tgl_kegiatan'])); ?></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="view-label">Bulan</label>
                    <div class="view-value"><i class="fa fa-calendar-alt view-icon"></i> <?php echo ($r['bulan']); ?></div>
                </div>
                <div class="col-md-12">
                    <label class="view-label">Lokasi Kegiatan</label>
                    <div class="view-value"><i class="fa fa-map-marker-alt view-icon" style="color:#dc3545;"></i> <?php echo $r['lokasi']; ?></div>
                </div>
            </div>
        </div>

        <div class="modal-footer px-0 pb-0">
            <button type="button" class="btn btn-secondary custom" data-dismiss="modal">Tutup</button>
        </div>
    </form>
</div>

<?php } ?>
