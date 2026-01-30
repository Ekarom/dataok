    <?php

    include_once "cfg/konek.php";

    include_once "cfg/secure.php";



    // Get IP Address function

    function get_client_ip() {

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {

            return $_SERVER['HTTP_CLIENT_IP'];

        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {

            return $_SERVER['HTTP_X_FORWARDED_FOR'];

        } else {

            return $_SERVER['REMOTE_ADDR'];

        }

    }



    // Set timezone

    date_default_timezone_set("Asia/Jakarta");



    // <<<-----------------POST TAMBAH--------------->>>

    if(isset($_POST['tamdata'])){
        $no_surat = mysqli_real_escape_string($sqlconn, $_POST['no_surat']);
        $judul = mysqli_real_escape_string($sqlconn, $_POST['judul']);
        $tujuan = mysqli_real_escape_string($sqlconn, $_POST['tujuan']);
        $tgl_dokumen = mysqli_real_escape_string($sqlconn, $_POST['tgl_dokumen']);

        $allowed_extensions = array('pdf');
        $pdf_list = array();
        $total_size = 0;
        $max_total_size = 100 * 1024 * 1024; // 100MB

        if (isset($_FILES['file']) && is_array($_FILES['file']['name'])) {
            // Only process the FIRST file
            $i = 0;
            if ($_FILES['file']['error'][$i] === UPLOAD_ERR_OK) {
                $nama_file_asli = $_FILES['file']['name'][$i];
                $dot = explode('.', $nama_file_asli);
                $ekstensi = strtolower(end($dot));
                $ukuran = $_FILES['file']['size'][$i];
                $file_tmp = $_FILES['file']['tmp_name'][$i];

                if ($ukuran <= 2 * 1024 * 1024) { // 2MB Limit
                    if (in_array($ekstensi, $allowed_extensions)) {
                        $timestamp = date('Ymd');
                        
                        // Determine year from DB name
                        $db_source = isset($_GET['db_year']) && !empty($_GET['db_year']) ? $_GET['db_year'] : (isset($_SESSION['database_asli']) ? $_SESSION['database_asli'] : 'dnet_ad'.date('Y'));
                        if (preg_match('/(\d{4})$/', $db_source, $matches)) {
                            $tahundb = $matches[1];
                        } else {
                            $tahundb = date('Y');
                        }

                        $target_dir = 'file/usulan/' . $tahundb . '/';
                        
                        if (!file_exists($target_dir)) {
                            mkdir($target_dir, 0777, true);
                        }

                        $no_surat_bersih = preg_replace('/[^a-zA-Z0-9_-]/', '_', $no_surat);
                        $nama_file_bersih = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($nama_file_asli, PATHINFO_FILENAME));
                        $pdf_name = $tahundb . '/' . $timestamp . '_' . $no_surat_bersih . '_' . $nama_file_bersih . '.' . $ekstensi;

                        if (move_uploaded_file($file_tmp, 'file/usulan/' . $pdf_name)) {
                            $pdf_list[] = $pdf_name;
                        } else {
                            echo "<script>alert('Gagal menyisipkan file ke server (Check permissions)!');window.history.back();</script>";
                            exit;
                        }
                    } else {
                        echo "<script>alert('Ekstensi file tidak diperbolehkan (Hanya PDF)!');window.history.back();</script>";
                        exit;
                    }
                } else {
                    echo "<script>alert('Ukuran file melebihi 2MB!');window.history.back();</script>";
                    exit;
                }
            } else if ($_FILES['file']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                $err_code = $_FILES['file']['error'][$i];
                echo "<script>alert('Terjadi kesalahan unggah file (Error Code: $err_code)!');window.history.back();</script>";
                exit;
            }
        }

        $pdf_string = implode(',', $pdf_list);

        $addtotable = mysqli_query($sqlconn, "INSERT INTO usulan (no_surat, judul, tujuan, tgl_dokumen, pdf) VALUES ('$no_surat', '$judul', '$tujuan', '$tgl_dokumen', '$pdf_string')");
        
        if ($addtotable) {
            write_log("ADD", "Menambah data usulan baru: $no_surat ($judul)");
            if(isset($_POST['is_ajax'])) { echo "success"; exit; }
            echo '<script>$(function() { toastr.success("Data berhasil ditambahkan"); setTimeout(function(){ window.location.href = "?modul=usulan"; }, 3000); });</script>';
        } else {
            foreach ($pdf_list as $f) { unlink('file/usulan/' . $f); }
            if(isset($_POST['is_ajax'])) { echo "error: " . mysqli_error($sqlconn); exit; }
            echo "<script>$(function() { toastr.error('Gagal menyimpan ke database! " . mysqli_escape_string($sqlconn, mysqli_error($sqlconn)) . "'); setTimeout(function(){ window.history.back(); }, 3000); });</script>";
        }
    }



    // <<<-----------------POST UPDATE--------------->>>

    if(isset($_POST['update'])){
        $id = mysqli_real_escape_string($sqlconn, $_POST['id']);
        $no_surat = mysqli_real_escape_string($sqlconn, $_POST['no_surat']);
        $judul = mysqli_real_escape_string($sqlconn, $_POST['judul']);
        $tujuan = mysqli_real_escape_string($sqlconn, $_POST['tujuan']);
        $tgl_dokumen = mysqli_real_escape_string($sqlconn, $_POST['tgl_dokumen']);

        $pdf_list = array();
        $allowed_extensions = array('pdf');

        if (!empty($_FILES['file']['name'][0])) {
            // Hapus file lama jika ada upload baru
            $gt = mysqli_fetch_array(mysqli_query($sqlconn, "SELECT pdf FROM usulan WHERE id='$id'"));
            $old_files = explode(',', $gt['pdf']);
            foreach ($old_files as $f) {
                if ($f != "" && file_exists('file/usulan/' . $f)) { unlink('file/usulan/' . $f); }
            }

            // Process only first file
            $i = 0;
            $name = $_FILES['file']['name'][$i];
            if ($_FILES['file']['error'][$i] === UPLOAD_ERR_OK) {
                $ukuran = $_FILES['file']['size'][$i];
                $ekstensi = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if ($ukuran <= 2 * 1024 * 1024) {
                    if (in_array($ekstensi, $allowed_extensions)) {
                        $timestamp = date('Ymd');
                        
                        // Determine year from DB name
                        $db_source = isset($_GET['db_year']) && !empty($_GET['db_year']) ? $_GET['db_year'] : (isset($_SESSION['database_asli']) ? $_SESSION['database_asli'] : 'dnet_ad'.date('Y'));
                        if (preg_match('/(\d{4})$/', $db_source, $matches)) {
                            $tahundb = $matches[1];
                        } else {
                            $tahundb = date('Y');
                        }

                        $target_dir = 'file/usulan/' . $tahundb . '/';
                        
                        if (!file_exists($target_dir)) {
                            mkdir($target_dir, 0777, true);
                        }

                        $no_surat_bersih = preg_replace('/[^a-zA-Z0-9_-]/', '_', $no_surat);
                        $nama_file_bersih = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($name, PATHINFO_FILENAME));
                        $pdf_name = $tahundb . '/' . $timestamp . '_' . $no_surat_bersih . '_' . $nama_file_bersih . '.' . $ekstensi;

                        if (move_uploaded_file($_FILES['file']['tmp_name'][$i], 'file/usulan/' . $pdf_name)) {
                            $pdf_list[] = $pdf_name;
                        } else {
                            echo "<script>alert('Gagal menyisipkan file ke server (Check permissions)!');window.history.back();</script>";
                            exit;
                        }
                    } else {
                        echo "<script>alert('Ekstensi file tidak diperbolehkan (Hanya PDF)!');window.history.back();</script>";
                        exit;
                    }
                } else {
                    echo "<script>alert('Ukuran file melebihi 2MB!');window.history.back();</script>";
                    exit;
                }
            } else if ($_FILES['file']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                $err_code = $_FILES['file']['error'][$i];
                echo "<script>alert('Terjadi kesalahan unggah file (Error Code: $err_code)!');window.history.back();</script>";
                exit;
            }
            $pdf_string = implode(',', $pdf_list);
            $update = mysqli_query($sqlconn, "UPDATE usulan SET no_surat='$no_surat', judul='$judul', tujuan='$tujuan', tgl_dokumen='$tgl_dokumen', pdf='$pdf_string' WHERE id='$id'");
        } else {
            $update = mysqli_query($sqlconn, "UPDATE usulan SET no_surat='$no_surat', judul='$judul', tujuan='$tujuan', tgl_dokumen='$tgl_dokumen' WHERE id='$id'");
        }

        if ($update) {
            $log_msg = "Update data usulan: $no_surat" . (!empty($pdf_list) ? " (dengan lampiran baru)" : "");
            write_log("EDIT", $log_msg);
            if(isset($_POST['is_ajax'])) { echo "success"; exit; }
            echo "<script>$(function() { toastr.success('Data berhasil diupdate'); setTimeout(function(){ window.location.href = '?modul=usulan'; }, 3000); });</script>";
        } else {
            if(isset($_POST['is_ajax'])) { echo "error: " . mysqli_error($sqlconn); exit; }
            echo "<script>$(function() { toastr.error('Gagal update data! " . mysqli_escape_string($sqlconn, mysqli_error($sqlconn)) . "'); });</script>";
        }
    }



    // <<<-----------------DELETE--------------->>>

    if(isset($_REQUEST['aksi'])){
        $id_hapus = mysqli_real_escape_string($sqlconn, $_REQUEST['urut']);
        $cek = mysqli_query($sqlconn,"SELECT * FROM usulan WHERE id = '$id_hapus'");
        $cek1 = mysqli_fetch_array($cek);
        if($cek1) {
            $pdf_list = explode(',', $cek1['pdf']);
            $no_surat_hapus = $cek1['no_surat'];
            foreach ($pdf_list as $f) {
                if($f !== "" && file_exists("file/usulan/".$f)) { unlink("file/usulan/".$f); }
            }
            $sql = mysqli_query($sqlconn,"DELETE FROM usulan WHERE id = '$id_hapus'");
            if($sql) {
                write_log("DELETE", "Menghapus data usulan ID: $id_hapus ($no_surat_hapus)");
                if(isset($_REQUEST['is_ajax'])) { echo "success"; exit; }
                echo '<script>$(function() { toastr.success("Data berhasil dihapus!"); setTimeout(function(){ window.location.href="?modul=usulan"; }, 3000); });</script>';
            } else {
                if(isset($_REQUEST['is_ajax'])) { echo "error: Gagal menghapus"; exit; }
                echo '<script>$(function() { toastr.error("Gagal menghapus data!"); setTimeout(function(){ window.history.back(); }, 3000); });</script>';
            }
        } else {
            if(isset($_REQUEST['is_ajax'])) { echo "error: Data tidak ditemukan"; exit; }
            echo '<script>alert("Data tidak ditemukan!");window.history.back();</script>';
        }
    }

    // <<<-----------------POST HAPUS FILE--------------->>>
    if (isset($_POST['aksi']) && $_POST['aksi'] == 'hapus_file') {
        $id = mysqli_real_escape_string($sqlconn, $_POST['id']);
        $file_to_delete = mysqli_real_escape_string($sqlconn, $_POST['file']);

        $q = mysqli_query($sqlconn, "SELECT pdf FROM usulan WHERE id = '$id'");
        $data = mysqli_fetch_array($q);
        
        if ($data) {
            $pdf_arr = explode(',', $data['pdf']);
            $new_pdf_arr = array();
            
            foreach ($pdf_arr as $f) {
                if ($f == $file_to_delete) {
                    if (file_exists("file/usulan/" . $f)) {
                        unlink("file/usulan/" . $f);
                    }
                } else if (!empty($f)) {
                    $new_pdf_arr[] = $f;
                }
            }
            
            $new_pdf_str = implode(',', $new_pdf_arr);
            $update = mysqli_query($sqlconn, "UPDATE usulan SET pdf = '$new_pdf_str' WHERE id = '$id'");
            if ($update) { echo "success"; } else { echo "error: " . mysqli_error($sqlconn); }
        } else {
            echo "error: data not found";
        }
        exit;
    }
    ?>



    <div class="content-wrapper">

        <!-- Content Header (Page header) -->

        <section class="content-header">

            <div class="container-fluid">

                <div class="row mb-2">

                    <div class="col-sm-6">

                        <h1>Data usulan</h1>

                    </div>

                    <div class="col-sm-6">

                        <ol class="breadcrumb float-sm-right">

                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>

                            <li class="breadcrumb-item active">Data usulan</li>

                        </ol>

                    </div>

                </div>

            </div>

        </section>

    

    <link rel="stylesheet" href="plugins/datatables-responsive/css/responsive.bootstrap4.css">
        <!-- Main content -->
        <section class="content">

            <div class="row">

                <div class="col-12">

                    <div class="card">

                        <div class="card-header bg-menu-gradient">

                            <?php if($lv=="1" || $lv=="2"){ ?>           

                            <a href='#myTam' id='custId' data-toggle='modal' data-id=''>

                                <button type='button' class="btn btn-primary btn-flat btn-sm"><i class="fa fa-plus"></i>&nbsp;Tambah</button>

                            </a> 

                            <?php } ?>
                            <div class="card-tools ml-auto">
                                <!-- Custom CSS to fix Select2 text color in header -->
                                <style>
                                    .select2-selection__rendered {
                                        color: #333 !important;
                                    }
                                    /* File Uploader Premium UI Styles */
                                    .file-upload-wrapper { width: 100%; margin-bottom: 20px; }
                                    .file-upload-selector { background: #f8f9fa; display: flex; align-items: center; border: 1px solid #ced4da; border-radius: 4px 4px 0 0; }
                                    .upload-area { display: flex; align-items: center; width: 100%; padding: 8px 12px; }
                                    .btn-light-info { background-color: #e3f2fd; color: #0288d1; border: 1px solid #b3e5fc; font-weight: 600; font-size: 13px; padding: 5px 12px; white-space: nowrap; border-radius: 4px; transition: all 0.2s; }
                                    .btn-light-info:hover { background-color: #b3e5fc; color: #01579b; }
                                    .dropzone { flex-grow: 1; text-align: center; color: #6c757d; font-size: 13px; border-left: 1px solid #dee2e6; margin-left: 10px; padding-left: 10px; cursor: pointer; }
                                    .file-list-uploaded { border: 1px solid #ced4da; border-top: none; background: #fff; max-height: 200px; overflow-y: auto; }
                                    .file-item-new { display: flex; align-items: center; padding: 10px 15px; border-bottom: 1px solid #f1f1f1; transition: background 0.2s; }
                                    .file-item-new:hover { background-color: #fcfcfc; }
                                    .file-info-new { display: flex; align-items: center; flex-grow: 1; min-width: 0; }
                                    .file-details-new { margin-left: 12px; overflow: hidden; }
                                    .file-name-new { font-size: 13px; font-weight: 600; color: #333; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block; }
                                    .file-size-new { font-size: 11px; color: #888; display: block; }
                                    .delete-btn-new { color: #dc3545; cursor: pointer; font-size: 16px; padding: 5px; margin-left: 10px; transition: color 0.2s; }
                                    .delete-btn-new:hover { color: #bd2130; }
                                    .uploader-footer { padding: 8px 12px; background: #f8f9fa; border: 1px solid #ced4da; border-top: none; border-radius: 0 0 4px 4px; font-size: 12px; line-height: 1.5; }
                                    .font-600 { font-weight: 600; }
                                    .text-blck { color: #333; }
                                    .hidden-file-input { display: none; }
                                    .file-upload-selector.dragover { background-color: #e8f0fe; border-color: #4285f4; }
                                    .fs-xs { font-size: 13px; }
                                    .fs-nano { font-size: 11px; }
                                    .mt-1 { margin-top: 0.25rem !important; }

                                    .total-text {
                                        font-weight: 700;
                                        font-size: 15px;
                                        color: #2d3436;
                                        margin-bottom: 5px;
                                    }
                                    .limit-note {
                                        font-size: 14px;
                                        color: #636e72;
                                    }
                                </style>
                                <select class="form-control form-control-sm" style="width: 110px;" onchange="if(this.value) window.location.href='?modul=usulan&db_year='+this.value">
                                    <option value="">Pilih Tahun</option>
                                    <?php
                                    // Ambil daftar database tahun dari dbset (dnet_ad2025 ke atas)
                                    // Define $database variable explicitly if not set, using the session default or current DB
                                    if (!isset($database)) {
                                        $database = isset($_SESSION['database_asli']) ? $_SESSION['database_asli'] : 'dnet_ad'.date("Y");
                                    }
                                    
                                    // Gunakan $database (DB utama) agar tetap bisa baca tabel dbset meskipun koneksi sedang di-switch ke DB tahunan
                                    $q_db = mysqli_query($sqlconn, "SELECT dbname, tahun FROM $database.dbset WHERE dbname LIKE 'dnet_ad%' AND tahun >= 2025 ORDER BY tahun DESC");
                                    
                                    // Tentukan DB yang sedang aktif untuk di-select
                                    $active_db = isset($_GET['db_year']) && !empty($_GET['db_year']) ? $_GET['db_year'] : $database;

                                    if (mysqli_num_rows($q_db) > 0) {
                                        while ($row_db = mysqli_fetch_array($q_db)) {
                                            $selected = ($active_db == $row_db['dbname']) ? 'selected' : '';
                                            echo "<option value='{$row_db['dbname']}' $selected>{$row_db['tahun']}</option>";
                                        }
                                    } else {
                                        echo "<option value='' disabled>-- Tidak ada data tahun 2025+ --</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
            

                        <!-- /.card-header -->

    <div class="card-body text-nowrap">
                <table id="us" class="table table-striped table-sm" style="width:100%"> 
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>No.Surat</th>
                                            <th>Judul</th>
                                            <th>Tujuan</th>
                                            <th>Dikirim</th>
                                            <th>Log</th>
                                            <th>Edit</th>
                                            <th>View</th>

                                            <th>Delete</th>

                                        </tr>

                                    </thead>

                                    <tbody>

                                        <?php 

                                        $sql = mysqli_query($sqlconn,"select * from usulan order by id");

                                        $no = 0;

                                        while($s = mysqli_fetch_array($sql)){

                                            $no++;

                                        ?>   

                                        <tr>

                                            <td><?php echo $no;?></td>    

                                            <td><?php echo $s['no_surat'];?></td>

                                            <td><?php echo $s['judul'];?></td>

                                            <td><?php echo $s['tujuan'];?></td>

                                            <td><?php echo $s['tgl_dokumen'];?></td>

                                            <td><?php echo $s['date'];?></td>

                                            

                                            <?php if($lv=="1" || $lv=="2"){ ?>                                              

                                            <td class="text-center">

                                                <?php echo "<a href='#myEdit2' id='custId' data-toggle='modal' data-id='$s[id]'>"; ?>

                                                <button type="button" class="btn btn-primary btn-flat btn-sm"><i class="fa fa-edit"></i></button>

                                                </a>

                                            </td>                

                                            <td class="text-center">

                                                <?php echo "<a href='#myView2' id='custId' data-toggle='modal' data-id='$s[id]'>"; ?>

                                                <button type="button" class="btn btn-warning btn-flat btn-sm"><i class="fa fa-eye"></i></button>

                                                </a>

                                            </td>

                                            <td class="text-center">

                                                <a href="?modul=Data_usulan&aksi=hapus&urut=<?php echo $s['id']; ?>">

                                                    <button type="button" class="btn btn-danger btn-flat btn-sm" onclick="return confirm('Apakah anda yakin ingin menghapus data ini?');"><i class="fa fa-trash"></i></button>

                                                </a>

                                            </td>

                                            <?php } ?>

                                        </tr>

                                        <?php } ?>         

                                    </tbody>

                                </table>

                            </div>

                        </div>

                        <!-- /.card-body -->

                    </div>

                    <!-- /.card -->

                </div>

                <!-- /.col -->

            </div>

            <!-- /.row -->

        </section>

        <!-- /.content -->

    </div>

    <!-- /.content-wrapper -->



    <!-- Modal Edit -->

    <div class="modal fade" id="myEdit2" role="dialog" data-backdrop="static">

        <div class="modal-dialog modal-lg" role="document">

            <div class="modal-content">

                <div class="modal-header bg-menu-gradient"> 

                    <h5 class="modal-title">Edit Dokumen</h5>

                </div>

                <div class="modal-body">

                    <div class="fetched-data"></div>

                </div>

            </div>

        </div>

    </div>



    <script type="text/javascript">

    $(document).ready(function(){

        $('#myEdit2').on('show.bs.modal', function (e) {

            var rowid = $(e.relatedTarget).data('id');

            $.ajax({

                type : 'post',

                url : 'edit_usulan.php',

                data :  'urut='+ rowid,

                success : function(data){

                    $('.fetched-data').html(data);

                }

            });

        });

    });

    </script>



    <!-- Modal View -->

    <div class="modal fade" id="myView2" role="dialog" data-backdrop="static">

        <div class="modal-dialog modal-lg" role="document">

            <div class="modal-content">

                <div class="modal-header bg-menu-gradient"> 

                    <h5 class="modal-title">Lihat Dokumen</h5>

                </div>

                <div class="modal-body">

                    <div class="fetched-data"></div>

                </div>

            </div>

        </div>

    </div>



    <script type="text/javascript">

    $(document).ready(function(){

        $('#myView2').on('show.bs.modal', function (e) {

            var rowid = $(e.relatedTarget).data('id');

            $.ajax({

                type : 'post',

                url : 'view_usul.php',

                data :  'urut='+ rowid,

                success : function(data){

                    $('.fetched-data').html(data);

                }

            });

        });

    });

    </script>



    <!-- Modal Tambah -->

    <div class="modal fade" id="myTam" role="dialog" data-backdrop="static">

        <div class="modal-dialog modal-lg" role="document">

            <div class="modal-content">

                <div class="modal-header bg-primary">
                    <b class="modal-title w-100 text-center"><i class="fa fa-plus"></i>&nbsp; Tambah Dokumen</b>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                

                <form name="sisd" method="post" enctype="multipart/form-data">

                    <div class="modal-body">
                        <!-- Section 1: No Surat & Tanggal -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fa fa-file-alt"></i> No. Surat</label>
                                    <input type="text" name="no_surat" class="form-control form-control-sm warna" required placeholder="Contoh: 001/US/2026">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fa fa-calendar-alt"></i> Tanggal Dikirim</label>
                                    <input type="date" name="tgl_dokumen" class="form-control form-control-sm warna" required>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Judul -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label><i class="fa fa-heading"></i> Judul usulan</label>
                                    <input type="text" name="judul" class="form-control warna" placeholder="Contoh: Pengajuan Lab Komputer Baru" required>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Tujuan -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label><i class="fa fa-paper-plane"></i> Tujuan / Instansi</label>
                                    <textarea name="tujuan" rows="3" class="form-control warna" placeholder="Deskripsi instansi atau pihak yang dituju..." required></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Section 4: Lampiran -->
                        <div class="file-upload-wrapper" id="drop-area">
                            <div class="file-upload-selector border rounded-top">
                                <div class="upload-area d-flex align-items-center">
                                    <div style="flex-shrink: 0;">
                                        <button type="button" class="btn btn-sm btn-light-info" onclick="document.getElementById('fileInput').click()">Pilih File...</button>
                                    </div>
                                    <div class="dropzone text-center prevent-select">
                                        <span class="upload-text"><i class="fa fa-cloud-upload-alt mr-1"></i> atau drag & drop berkas disini.</span>
                                    </div>
                                </div>
                            </div>
                            <div id="file-list-display" class="file-list-uploaded d-none">
                                <!-- File items will be injected here -->
                            </div>
                            <div class="uploader-footer border rounded-bottom p-2 border-top-0">
                                <div class="fs-xs"><span class="font-600 text-blck">Total : </span><span id="total-size-display">0 B</span></div>
                                <div class="fs-nano text-muted">
                                    Lampirkan berkas <span class="font-600 text-blck">.pdf</span> maksimal <span class="font-600 text-blck">1</span> berkas dan ukuran maksimal <span class="font-600 text-blck">2.0 MB</span>
                                </div>
                            </div>
                            <input type="file" name="file[]" id="fileInput" class="hidden-file-input" accept=".pdf" multiple onchange="handleFileSelect(this)">
                        </div>
                    </div>

                    

                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn bg-gradient-danger custom" data-dismiss="modal">Batal</button>
                        <button type="button" id="btnIncomplete" class="btn bg-gradient-secondary custom" style="cursor: not-allowed;">Belum Lengkap</button>
                        <button type="submit" id="btnSave" class="btn bg-gradient-primary custom" name="tamdata" style="display: none;">Simpan</button>
                    </div>
                    
                    <script>
                    function checkFormCompletion() {
                        const form = document.querySelector('#myTam form');
                        const no_surat = form.querySelector('[name="no_surat"]').value.trim();
                        const judul = form.querySelector('[name="judul"]').value.trim();
                        const tujuan = form.querySelector('[name="tujuan"]').value.trim();
                        const tgl_dokumen = form.querySelector('[name="tgl_dokumen"]').value;

                        const isComplete = no_surat !== "" && 
                                        judul !== "" && 
                                        tujuan !== "" && 
                                        tgl_dokumen !== "";

                        const btnIncomplete = document.getElementById('btnIncomplete');
                        const btnSave = document.getElementById('btnSave');

                        if (isComplete) {
                            btnIncomplete.style.display = 'none';
                            btnSave.style.display = 'block';
                        } else {
                            btnIncomplete.style.display = 'block';
                            btnSave.style.display = 'none';
                        }
                    }

                    // Initialize and listen for changes
                    $(document).ready(function() {
                        $('#myTam form').on('change keyup', 'input, select, textarea', function() {
                            checkFormCompletion();
                        });
                        checkFormCompletion();
                        
                        // Re-check when modal opens
                        $('#myTam').on('shown.bs.modal', function () {
                            checkFormCompletion();
                        });
                    });
                    </script>

                </form>

            </div>

        </div>

    </div>
    <!-- Bootstrap 4 -->
    <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables -->
    <script src="plugins/datatables/jquery.dataTables.min.js"></script>

    <script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<script>
    let selectedFiles = [];

    function formatBytes(bytes, decimals = 1) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }

    function handleFileSelect(input) {
        const files = Array.from(input.files);
        const pdfFiles = files.filter(f => f.type === 'application/pdf');
        
        if (files.length !== pdfFiles.length) {
            toastr.error('Hanya berkas PDF yang diperbolehkan!');
        }

        selectedFiles = pdfFiles;
        updateInputFiles();
        renderFileList();
    }

    function renderFileList() {
        const listContainer = document.getElementById('file-list-display');
        const totalDisplay = document.getElementById('total-size-display');
        if (!listContainer) return;
        
        const maxFiles = 1;
        const maxSizeTotal = 2 * 1024 * 1024; // 2MB

        listContainer.innerHTML = '';
        let totalSize = 0;

        if (selectedFiles.length > maxFiles) {
            alert(`Maksimal ${maxFiles} berkas yang dapat diunggah!`);
            selectedFiles = selectedFiles.slice(0, maxFiles);
            updateInputFiles();
        }

        if (selectedFiles.length > 0) { listContainer.classList.remove('d-none'); } else { listContainer.classList.add('d-none'); }

        selectedFiles.forEach((file, index) => {
            totalSize += file.size;
            const item = document.createElement('div');
            item.className = 'file-item-new';
            item.id = `file-item-${index}`; 
            item.innerHTML = `
                <div class="file-info-new">
                    <i class="fa fa-file-pdf fa-2x text-info"></i>
                    <div class="file-details-new">
                        <div class="file-name-new">${file.name}</div>
                        <div class="file-size-new">Ukuran Berkas: ${formatBytes(file.size)}</div>
                        <div class="upload-status" id="status-${index}">
                            <div class="fs-nano mt-1" style="color: #27ae60;"><i class="fa fa-circle-notch fa-spin mr-1"></i> Sedang mengunggah...</div>
                        </div>
                    </div>
                </div>
                <div class="delete-btn-new ml-auto" onclick="removeFile(${index})">
                    <i class="fa fa-trash-alt"></i>
                </div>
            `;
            listContainer.appendChild(item);

            // Simulate upload delay
            setTimeout(() => {
                const statusEl = document.getElementById(`status-${index}`);
                const nameEl = item.querySelector('.file-name-new');
                if (statusEl) { statusEl.remove(); }
                if (nameEl) {
                    const fileUrl = URL.createObjectURL(file);
                    nameEl.innerHTML = `<a href="${fileUrl}" target="_blank" class="text-info text-decoration-none">${file.name} <i class="fa fa-external-link-alt ml-1 fs-nano"></i></a>`;
                }
            }, 1500); 
        });

        if (totalSize > maxSizeTotal) { 
            alert(`Ukuran berkas melebihi 2 MB!`);
            selectedFiles = [];
            updateInputFiles();
            renderFileList();
            return;
        }
        if (totalDisplay) totalDisplay.textContent = formatBytes(totalSize);
    }

    function removeFile(index) {
        selectedFiles.splice(index, 1);
        updateInputFiles();
        renderFileList();
    }

    function updateInputFiles() {
        const input = document.getElementById('fileInput');
        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => dataTransfer.items.add(file));
        input.files = dataTransfer.files;
    }

    // Setup Drag and Drop
    var dropArea = document.getElementById('drop-area');
    if (dropArea) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });
        function preventDefaults(e) { e.preventDefault(); e.stopPropagation(); }
        ['dragenter', 'dragover'].forEach(eventName => { dropArea.addEventListener(eventName, () => dropArea.classList.add('dragover'), false); });
        ['dragleave', 'drop'].forEach(eventName => { dropArea.addEventListener(eventName, () => dropArea.classList.remove('dragover'), false); });
        dropArea.addEventListener('drop', handleDrop, false);
        function handleDrop(e) {
            var files = e.dataTransfer.files;
            if (files.length > 0) {
                const dataTransfer = new DataTransfer();
                Array.from(document.getElementById('fileInput').files).forEach(file => dataTransfer.items.add(file));
                Array.from(files).forEach(file => dataTransfer.items.add(file));
                document.getElementById('fileInput').files = dataTransfer.files;
                handleFileSelect(document.getElementById('fileInput'));
            }
        }
    }

        // AJAX Handling for Add Form
        $('#myTam form').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            formData.append('tamdata', 'true'); 
            formData.append('is_ajax', 'true'); 

            $.ajax({
                url: 'usulan.php', 
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.trim() == 'success') {
                        toastr.success("Data berhasil ditambahkan");
                        setTimeout(function(){ window.location.href = "?modul=usulan"; }, 3000);
                    } else {
                        toastr.error("Gagal menyimpan: " + response);
                    }
                },
                error: function() { toastr.error("Terjadi kesalahan server"); }
            });
        });

        // AJAX Handling for Edit Form (Delegated event)
        $(document).on('submit', '#myEdit2 form', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            formData.append('update', 'true'); 
            formData.append('is_ajax', 'true'); 

            $.ajax({
                url: 'usulan.php', 
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.trim() == 'success') {
                        toastr.success("Data berhasil diupdate");
                        setTimeout(function(){ window.location.href = "?modul=usulan"; }, 3000);
                    } else {
                        toastr.error("Gagal update data: " + response);
                    }
                },
                error: function() { toastr.error("Terjadi kesalahan server"); }
            });
        });

        // AJAX Handling for Delete
        $(document).on('click', 'a[href*="aksi=hapus"]', function(e) {
            e.preventDefault();
            var deleteUrl = $(this).attr('href');
            // Replace "?modul=..." with "usulan.php?" to start parameters correctly
            deleteUrl = deleteUrl.replace(/\?modul=[^&]+/, 'usulan.php?');
            
            $.ajax({
                url: deleteUrl + '&is_ajax=true',
                type: 'GET',
                success: function(response) {
                    if (response.trim() == 'success') {
                        toastr.success("Data berhasil dihapus!");
                        setTimeout(function(){ window.location.href = "?modul=usulan"; }, 3000);
                    } else {
                        toastr.error("Gagal menghapus data: " + response);
                    }
                },
                error: function() { toastr.error("Terjadi kesalahan server saat menghapus"); }
            });
        });
    </script>


