<?php
include_once "cfg/konek.php";
include_once "cfg/secure.php";

// Switch Database if requested
$db_req = isset($_POST['db_year']) ? $_POST['db_year'] : (isset($_GET['db_year']) ? $_GET['db_year'] : '');
if (!empty($db_req)) {
    $target_db = mysqli_real_escape_string($sqlconn, $db_req);
    $check_db = mysqli_query($sqlconn, "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$target_db'");
    if (mysqli_num_rows($check_db) > 0) {
        mysqli_select_db($sqlconn, $target_db);
    }
}

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

// Define tahun pelajaran
$tpl = date("Y");

// <<<-----------------POST TAMBAH--------------->>>
if (isset($_POST['save'])) {
    $pd = mysqli_real_escape_string($sqlconn, $_POST['pd']);
    $kelas = mysqli_real_escape_string($sqlconn, $_POST['kelas']);
    $juara = mysqli_real_escape_string($sqlconn, $_POST['juara']);
    $jenisprestasi = mysqli_real_escape_string($sqlconn, $_POST['jenisprestasi']);
    // Convert DD-MM-YYYY to YYYY-MM-DD for database
    $raw_date = $_POST['tgl_kegiatan'];
    $tgl_kegiatan = date('Y-m-d', strtotime(str_replace('/', '-', $raw_date))); // Handle / or - separator
    $tgl_kegiatan = mysqli_real_escape_string($sqlconn, $tgl_kegiatan);
    $prestasi = mysqli_real_escape_string($sqlconn, $_POST['prestasi']);
    $kegiatan = mysqli_real_escape_string($sqlconn, $_POST['nama_kegiatan']);
    $tingkat = mysqli_real_escape_string($sqlconn, $_POST['tingkat']);
    $penyelenggara =mysqli_real_escape_string($sqlconn, $_POST['penyelenggara']);
    $lokasi = mysqli_real_escape_string($sqlconn, $_POST['lokasi']);
    $bulan = mysqli_real_escape_string($sqlconn, $_POST['bulan']);

    // Soal file
    $allowed_extensions = array('pdf', 'jpg', 'jpeg', 'png');
    $pdf_list = [];
    
    if (isset($_FILES['file']) && is_array($_FILES['file']['name'])) {
        $allowed_limit = 2; // Allow up to 2 files
        $file_count = count($_FILES['file']['name']);
        
        for ($i = 0; $i < $file_count && $i < $allowed_limit; $i++) {
            if (isset($_FILES['file']['error'][$i]) && $_FILES['file']['error'][$i] === UPLOAD_ERR_OK) {
                $nama_file_asli = $_FILES['file']['name'][$i];
                $ekstensi = strtolower(pathinfo($nama_file_asli, PATHINFO_EXTENSION));
                $ukuran = $_FILES['file']['size'][$i];
                $file_tmp = $_FILES['file']['tmp_name'][$i];

                if ($ukuran <= 2 * 1024 * 1024) {
                    if (in_array($ekstensi, $allowed_extensions)) {
                        $timestamp = date('Ymd_His'); // Added His to prevent collision with multiple files
                        
                        // Determine year from DB name
                        $db_source = !empty($db_req) ? $db_req : (isset($_SESSION['database_asli']) ? $_SESSION['database_asli'] : 'dnet_ad'.date('Y'));
                        if (preg_match('/(\d{4})$/', $db_source, $matches)) {
                            $tahundb = $matches[1];
                        } else {
                            $tahundb = date('Y');
                        }

                        $target_dir = 'file/prestasi/' . $tahundb . '/';
                        
                        if (!file_exists($target_dir)) {
                            mkdir($target_dir, 0777, true);
                        }

                        $pd_bersih = preg_replace('/[^a-zA-Z0-9_-]/', '_', $pd);
                        $nama_file_bersih = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($nama_file_asli, PATHINFO_FILENAME));
                        $pdf_name = $tahundb . '/' . $timestamp . '_' . $i . '_' . $pd_bersih . '_' . $nama_file_bersih . '.' . $ekstensi;

                        if (move_uploaded_file($file_tmp, 'file/prestasi/' . $pdf_name)) {
                            $pdf_list[] = $pdf_name;
                        } else {
                            $msg = "Gagal menyisipkan file $nama_file_asli ke server (Check permissions)!";
                            if(isset($_POST['is_ajax'])) { echo "error: " . $msg; exit; }
                            echo "<script>alert('$msg');window.history.back();</script>";
                            exit;
                        }
                    } else {
                        $msg = "Ekstensi file $nama_file_asli tidak diperbolehkan (Hanya PDF/Gambar)!";
                        if(isset($_POST['is_ajax'])) { echo "error: " . $msg; exit; }
                        echo "<script>alert('$msg');window.history.back();</script>";
                        exit;
                    }
                } else {
                    $msg = "Ukuran file $nama_file_asli melebihi 2MB (Limit Client)!";
                    if(isset($_POST['is_ajax'])) { echo "error: " . $msg; exit; }
                    echo "<script>alert('$msg');window.history.back();</script>";
                    exit;
                }
            } else if ($_FILES['file']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                $err_code = $_FILES['file']['error'][$i];
                $msg = "Terjadi kesalahan unggah ($nama_file_asli). ";
                if ($err_code == 1) $msg .= "Ukuran file melebihi batas server (upload_max_filesize di php.ini).";
                else $msg .= "Error Code: $err_code";
                
                if(isset($_POST['is_ajax'])) { echo "error: " . $msg; exit; }
                echo "<script>alert('$msg');window.history.back();</script>";
                exit;
            }
        }
    }

    $pdf_string = implode(',', $pdf_list);

    $addtotable = mysqli_query($sqlconn, "INSERT INTO prestasi (pd, kelas, juara, jenisprestasi, prestasi, nama_kegiatan, tingkat, penyelenggara, tgl_kegiatan, lokasi, bulan, pdf) VALUES ('$pd', '$kelas', '$juara', '$jenisprestasi', '$prestasi', '$kegiatan', '$tingkat', '$penyelenggara', '$tgl_kegiatan', '$lokasi', '$bulan', '$pdf_string')");
    
    if ($addtotable) {
        write_log("ADD", "Menambah Data Prestasi Atas Nama: $pd");
        if(isset($_POST['is_ajax'])) { echo "success"; exit; }
        echo '<script>$(function() { toastr.success("Data berhasil ditambahkan"); setTimeout(function(){ window.location.href = "?press"; }, 3000); });</script>';
    } else {
        foreach ($pdf_list as $f) { unlink('file/prestasi/' . $f); }
        if(isset($_POST['is_ajax'])) { echo "error: " . mysqli_error($sqlconn); exit; }
        echo "<script>$(function() { toastr.error('Gagal menyimpan ke database! " . mysqli_escape_string($sqlconn, mysqli_error($sqlconn)) . "'); setTimeout(function(){ window.history.back(); }, 3000); });</script>";
    }
}

// <<<-----------------POST UPDATE--------------->>>
if (isset($_POST['update2'])) {
    $id = mysqli_real_escape_string($sqlconn, $_POST['id']);
    $pd = mysqli_real_escape_string($sqlconn, $_POST['pd']);
    $kelas = mysqli_real_escape_string($sqlconn, $_POST['kelas']);
    $juara = mysqli_real_escape_string($sqlconn, $_POST['juara']);
    $jenisprestasi = mysqli_real_escape_string($sqlconn, $_POST['jenisprestasi']);
    // Convert DD-MM-YYYY to YYYY-MM-DD for database
    $raw_date = $_POST['tgl_kegiatan'];
    $tgl_kegiatan = date('Y-m-d', strtotime(str_replace('/', '-', $raw_date))); // Handle / or - separator
    $tgl_kegiatan = mysqli_real_escape_string($sqlconn, $tgl_kegiatan);
    $prestasi = mysqli_real_escape_string($sqlconn, $_POST['prestasi']);
    $kegiatan = mysqli_real_escape_string($sqlconn, $_POST['nama_kegiatan']);
    $tingkat = mysqli_real_escape_string($sqlconn, $_POST['tingkat']);
    $penyelenggara = mysqli_real_escape_string($sqlconn, $_POST['penyelenggara']);
    $lokasi = mysqli_real_escape_string($sqlconn, $_POST['lokasi']);
    $bulan = mysqli_real_escape_string($sqlconn, $_POST['bulan']);

    // Soal file
    $allowed_extensions = array('pdf', 'jpg', 'jpeg', 'png');
    $pdf_list = [];
    
    // Check for existing files (essential for additive update)
    $q_old = mysqli_query($sqlconn, "SELECT pdf FROM prestasi WHERE id='$id'");
    $d_old = mysqli_fetch_array($q_old);
    if ($d_old && !empty($d_old['pdf'])) {
        $pdf_list = explode(',', $d_old['pdf']);
    }

    // Check if new files are uploaded
    if (isset($_FILES['file']) && is_array($_FILES['file']['name']) && !empty($_FILES['file']['name'][0])) {
        $allowed_limit = 2; // Allow up to 2 files TOTAL
        $current_count = count($pdf_list);
        $file_count = count($_FILES['file']['name']);

        for ($i = 0; $i < $file_count && ($current_count + count($pdf_list) - $current_count) < $allowed_limit; $i++) {
            $name = $_FILES['file']['name'][$i];
            if (isset($_FILES['file']['error'][$i]) && $_FILES['file']['error'][$i] === UPLOAD_ERR_OK) {
                $ekstensi = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                $ukuran = $_FILES['file']['size'][$i];
                if ($ukuran <= 2 * 1024 * 1024) {
                    if (in_array($ekstensi, $allowed_extensions)) {
                        $timestamp = date('Ymd_His');
                        
                        // Determine year from DB name
                        $db_source = !empty($db_req) ? $db_req : (isset($_SESSION['database_asli']) ? $_SESSION['database_asli'] : 'dnet_ad'.date('Y'));
                        if (preg_match('/(\d{4})$/', $db_source, $matches)) {
                            $tahundb = $matches[1];
                        } else {
                            $tahundb = date('Y');
                        }

                        $target_dir = 'file/prestasi/' . $tahundb . '/';
                        
                        if (!file_exists($target_dir)) {
                            mkdir($target_dir, 0777, true);
                        }

                        $pd_bersih = preg_replace('/[^a-zA-Z0-9_-]/', '_', $pd);
                        $nama_file_bersih = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($name, PATHINFO_FILENAME));
                        $pdf_name = $tahundb . '/' . $timestamp . '_' . $i . '_' . $pd_bersih . '_' . $nama_file_bersih . '.' . $ekstensi;
                        
                        if (move_uploaded_file($_FILES['file']['tmp_name'][$i], 'file/prestasi/' . $pdf_name)) {
                            $pdf_list[] = $pdf_name;
                        } else {
                            $msg = "Gagal menyisipkan file $name ke server (Check permissions)!";
                            if(isset($_POST['is_ajax'])) { echo "error: " . $msg; exit; }
                            echo "<script>alert('$msg');window.history.back();</script>";
                            exit;
                        }
                    } else {
                        $msg = "Ekstensi file $name tidak diperbolehkan (Hanya PDF/Gambar)!";
                        if(isset($_POST['is_ajax'])) { echo "error: " . $msg; exit; }
                        echo "<script>alert('$msg');window.history.back();</script>";
                        exit;
                    }
                } else {
                    $msg = "Ukuran file $name melebihi 2MB (Limit Client)!";
                    if(isset($_POST['is_ajax'])) { echo "error: " . $msg; exit; }
                    echo "<script>alert('$msg');window.history.back();</script>";
                    exit;
                }
            } else if ($_FILES['file']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                $err_code = $_FILES['file']['error'][$i];
                $msg = "Terjadi kesalahan unggah ($name). ";
                if ($err_code == 1) $msg .= "Ukuran file melebihi batas server (upload_max_filesize di php.ini).";
                else $msg .= "Error Code: $err_code";
                
                if(isset($_POST['is_ajax'])) { echo "error: " . $msg; exit; }
                echo "<script>alert('$msg');window.history.back();</script>";
                exit;
            }
        }
    }
    $pdf_string = implode(',', array_filter($pdf_list));
    $update = mysqli_query($sqlconn, "UPDATE prestasi SET pd='$pd', kelas='$kelas', juara='$juara', jenisprestasi='$jenisprestasi', prestasi='$prestasi', nama_kegiatan='$kegiatan', tingkat='$tingkat', penyelenggara='$penyelenggara', tgl_kegiatan='$tgl_kegiatan', lokasi='$lokasi', bulan='$bulan', pdf='$pdf_string' WHERE id='$id'");

    if ($update) {
        $log_msg = "Update data prestasi: $pd ($prestasi)" . (!empty($pdf_list) ?: "");
        write_log("EDIT", $log_msg);
        if(isset($_POST['is_ajax'])) { echo "success"; exit; }
        echo "<script>$(function() { toastr.success('Data berhasil diupdate'); setTimeout(function(){ window.location.href='?press'; }, 3000); });</script>";
    } else {
        // If update failed, and new files were uploaded, try to delete them to prevent orphaned files
        foreach ($pdf_list as $f) { unlink('file/prestasi/' . $f); }
        if(isset($_POST['is_ajax'])) { echo "error: " . mysqli_error($sqlconn); exit; }
        echo "<script>$(function() { toastr.error('Gagal update data: " . mysqli_escape_string($sqlconn, mysqli_error($sqlconn)) . "'); });</script>";
    }
}


// <<<-----------------POST HAPUS--------------->>>
if (isset($_REQUEST['aksi']) && $_REQUEST['aksi'] == 'hapus' && isset($_REQUEST['urut'])) {
    $id_hapus = mysqli_real_escape_string($sqlconn, $_REQUEST['urut']);
    $cek3 = mysqli_query($sqlconn, "SELECT pd, pdf FROM prestasi WHERE id = '$id_hapus'");
    $cek3_data = mysqli_fetch_array($cek3);
    
    if($cek3_data) {
        $pdf_str = $cek3_data['pdf'];
        $nama_pd = $cek3_data['pd'];

        if (!empty($pdf_str)) {
            $pdf_arr = explode(',', $pdf_str);
            foreach ($pdf_arr as $file_del) {
                if (!empty($file_del) && file_exists("file/prestasi/" . $file_del)) {
                    unlink("file/prestasi/" . $file_del);
                }
            }
        }

        $sql = mysqli_query($sqlconn, "DELETE FROM prestasi WHERE id = '$id_hapus'");
        if($sql) {
            write_log("DELETE", "Menghapus data prestasi ID: $id_hapus ($nama_pd)");
            if(isset($_REQUEST['is_ajax'])) { echo "success"; exit; }
            echo '<script>$(function() { toastr.success("Data berhasil dihapus!"); setTimeout(function(){ window.location.href="?press"; }, 3000); });</script>';
        } else {
            if(isset($_REQUEST['is_ajax'])) { echo "error: Gagal menghapus"; exit; }
            echo '<script>$(function() { toastr.error("Gagal menghapus data!"); window.history.back(); });</script>';
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

    $q = mysqli_query($sqlconn, "SELECT pdf FROM prestasi WHERE id = '$id'");
    $data = mysqli_fetch_array($q);
    
    if ($data) {
        $pdf_arr = explode(',', $data['pdf']);
        $new_pdf_arr = array();
        
        foreach ($pdf_arr as $f) {
            if ($f == $file_to_delete) {
                // Hapus file fisik
                if (file_exists("file/prestasi/" . $f)) {
                    unlink("file/prestasi/" . $f);
                }
            } else if (!empty($f)) {
                $new_pdf_arr[] = $f;
            }
        }
        
        $new_pdf_str = implode(',', $new_pdf_arr);
        $update = mysqli_query($sqlconn, "UPDATE prestasi SET pdf = '$new_pdf_str' WHERE id = '$id'");
        
        if ($update) {
            echo "success";
        } else {
            echo "error: " . mysqli_error($sqlconn);
        }
    } else {
        echo "error: data not found";
    }
    exit;
}
?>
<style>
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

    /* PRESTASI PREMIUM UI STYLES */
    .modal-input-prestasi .modal-content { border-radius: 12px; border: none; overflow: hidden; }
    .modal-input-prestasi .modal-header { background: linear-gradient(90deg, #1e3c72 0%, #2a5298 100%); color: #fff; border: none; padding: 12px 20px; }
    .modal-input-prestasi .modal-title { font-size: 1.1rem; font-weight: 700; letter-spacing: 0.5px; }
    
    .profile-card-prestasi { background: #ffffffff; border: 1px solid #e9ecef; border-top: 3px solid #007bff; border-radius: 8px; padding: 25px 15px; height: 100%; box-shadow: 0 4px 12px rgba(0,0,0,0.05); text-align: center; }
    .profile-img-prestasi { width: 140px; height: 140px; border-radius: 50%; object-fit: cover; border: 4px solid #fff; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 20px; }
    .profile-name-prestasi { font-size: 1.2rem; font-weight: 800; color: #2d3436; line-height: 1.2; margin-bottom: 15px; text-transform: uppercase; }
    .profile-divider { border-top: 1px solid #f1f1f1; margin: 15px 0; }
    .profile-nis-label { font-weight: 700; color: #333; font-size: 0.95rem; }
    .profile-nis-value { color: #0984e3; font-weight: 700; font-size: 0.95rem; }
    
    .form-prestasi-premium label { font-weight: 700; color: #333; margin-bottom: 5px; font-size: 0.95rem; display: flex; align-items: center; }
    .form-prestasi-premium .form-control { border-radius: 4px; border: 1px solid #ced4da; padding: 8px 12px; font-size: 0.9rem; }
    .form-prestasi-premium .form-control:focus { box-shadow: 0 0 0 0.2rem rgba(9, 132, 227, 0.15); border-color: #0984e3; }
    
    .input-group-premium .form-control { border-right: none; }
    .input-group-premium .btn { border-left: none; background: #f8f9fa; color: #636e72; border: 1px solid #ced4da; font-weight: 600; cursor: pointer; border-radius: 0 4px 4px 0 !important; }
    .input-group-premium .btn:hover { background: #e9ecef; }
    
    .premium-form-row { margin-bottom: 18px; }
    .premium-form-left-col { width: 33%; padding-top: 8px; }
    .premium-form-right-col { width: 67%; }

    .modal-footer-premium { background: #fbfbfb; border-top: 1px solid #f1f1f1; padding: 15px 25px; }
</style>
    <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.css">
    <link rel="stylesheet" href="plugins/datatables-responsive/css/responsive.bootstrap4.css">

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Data Prestasi</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active">Data Prestasi</li>
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
                    <div class="card-header bg-menu-gradient d-flex align-items-center">
                        <?php if ($lv == "1" || $lv == "2") { ?>
                            <div>
                                <a href='#siswa' data-toggle='modal'>
                                    <button type='button' class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i>&nbsp;Input Prestasi</button>
                                </a>
                            </div>
                        <?php } ?>
                        
                    </div>

                    <!-- /.card-header -->
<div class="card-body">
    <!-- Rekap Prestasi Section (Refined Pill Shape) -->
    <div class="rekap-pill-container mb-4">
        <div class="row align-items-center">
            <div class="col-md-auto pl-4 pr-3">
                <span class="label-rekap">Rekap Prestasi</span>
            </div>
            <div class="col">
                <div class="row align-items-center">
                    <div class="col-md-2">
                        <select id="rekap_triwulan" class="form-control form-control-sm custom-select-rekap" style="border-radius: 20px !important;" onchange="updateMonthsByQuarter(this.value)">
                            <option value="">- Triwulan -</option>
                            <option value="1">Triwulan I</option>
                            <option value="2">Triwulan II</option>
                            <option value="3">Triwulan III</option>
                            <option value="4">Triwulan IV</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="rekap_m1" class="form-control form-control-sm custom-select-rekap" style="border-radius: 20px !important;">
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
                    <div class="col-md-auto px-2 label-sd text-muted">
                        s/d
                    </div>
                    <div class="col-md-2">
                        <select id="rekap_m2" class="form-control form-control-sm custom-select-rekap" style="border-radius: 20px !important;">
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
                    <div class="col-md-2">
                        <select id="rekap_kejuaraan" class="form-control form-control-sm custom-select-rekap" style="border-radius: 20px !important;">
                            <option value="">- Semua Kejuaraan -</option>
                            <?php 
                            $q_kej = mysqli_query($sqlconn, "SELECT DISTINCT prestasi FROM prestasi WHERE prestasi != '' ORDER BY prestasi ASC");
                            while($rk = mysqli_fetch_array($q_kej)) {
                                echo "<option value='".htmlspecialchars($rk['prestasi'], ENT_QUOTES)."'>".$rk['prestasi']."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <input type="number" id="rekap_y" class="form-control form-control-sm custom-input-rekap" style="border-radius: 20px !important;" value="<?php echo date('Y'); ?>">
                    </div>
                    <div class="col-md-auto ml-auto pr-2">
                        <button type="button" class="btn btn-rekap-print" onclick="printRekapTriwulan()"><i class="fa fa-print"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .rekap-pill-container {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 50px;
            padding: 8px 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .label-rekap {
            font-weight: 800;
            color: #2d3436;
            font-size: 0.95rem;
            white-space: nowrap;
        }
        .custom-select-rekap, .custom-input-rekap {
            border: 1px solid #dfe6e9 !important;
            height: 32px !important;
            font-size: 0.85rem !important;
            background-color: #f8f9fa !important;
        }
        .btn-rekap-print {
            background: #2d3436;
            color: #fff;
            border-radius: 50% !important;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            border: none;
        }
        .btn-rekap-print:hover {
            background: #000;
            transform: scale(1.1);
            color: #fff;
        }
        .card-rekap-summary {
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
    </style>

    <div class="table-responsive">
            <table id="pres" class="table table-striped" style="width:100%"> 
                <thead>
                <tr align="center">
                                        <th>No</th>
                                        <th width="10%">Nama</th>
                                        <th width="5%">Kelas</th>
                                        <th width="5%">Juara</th>
                                        <th width="10%">Jenis Prestasi</th>
                                        <th width="10%">Nama Kegiatan</th>
                                        <th width="10%">Tanggal</th>
                                        <th width="10%">Tingkat</th>
                                        <th width="17%">Penyelenggara</th>
                                        <th width="10%">Lokasi</th>
                                        <th width="10%">Bulan</th>
                                        <th width="10%">Log</th>
                                        <?php if (isset($lv) && ($lv == "1" || $lv == "2")) { ?>
                                            <th>Edit</th>
                                            <th>View</th>
                                        <?php } ?>
                                        <th>Del</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = mysqli_query($sqlconn, "SELECT * FROM prestasi ORDER BY id");
                                    if ($sql) {
                                        $no = 0;
                                        while ($s = mysqli_fetch_array($sql)) {
                                            $no++;
                                    ?>
                                        <tr>
                                            <td><?php echo $no; ?></td>
                                            <td><?php echo $s['pd']; ?></td>
                                            <td><?php echo $s['kelas']; ?></td>
                                            <td><?php echo $s['juara']; ?></td>
                                            <td><?php echo $s['jenisprestasi']; ?></td>
                                            <td><?php echo $s['nama_kegiatan']; ?></td>
                                            <td><?php echo $s['tgl_kegiatan']; ?></td>
                                            <td><?php echo $s['tingkat']; ?></td>
                                            <td><?php echo $s['penyelenggara']; ?></td>
                                            <td><?php echo $s['lokasi']; ?></td>
                                            <td><?php echo $s['bulan']; ?></td>
                                            <td><?php echo isset($s['date']) ? $s['date'] : ''; ?></td>

                                            <?php if (isset($lv) && ($lv == "1" || $lv == "2")) { ?>
                                                <td>
                                                    <a href='#myEdit' id='custId' data-toggle='modal' data-id='<?php echo $s['id']; ?>'>
                                                        <button type="button" class="btn btn-info btn-sm btn-flat"><i class="fa fa-edit"></i></button>
                                                    </a>
                                                </td>
                                                <td>
                                                    <a href='#myView1' id='custId' data-toggle='modal' data-id='<?php echo $s['id']; ?>'>
                                                        <button type="button" class="btn btn-warning btn-sm btn-flat"><i class="fa fa-eye"></i></button>
                                                    </a>
                                                </td>
                                            <?php } ?>

                                            <td>
                                                <a href="?press&aksi=hapus&urut=<?php echo $s['id']; ?>">
                                                    <button type="button" class="btn btn-danger btn-sm btn-flat" onclick="return confirm('Apakah anda yakin ingin menghapus data ini?');"><i class="fa fa-trash"></i></button>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php 
                                        }
                                    } else {
                                        echo "<tr><td colspan='15' class='text-center text-danger'>Terjadi error saat load table (atau tidak ada data) : " . mysqli_error($sqlconn) . "</td></tr>";
                                    } 
                                    ?>
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
<div id="myEdit" class="modal fade" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-menu-gradient">
                <b class="modal-title">Edit Dokumen</b>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="fetched-data"></div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#myEdit').on('show.bs.modal', function(e) {
            var rowid = $(e.relatedTarget).data('id');
            $.ajax({
                type: 'post',
                url: 'edit_press.php',
                data: 'urut=' + rowid,
                success: function(data) {
                    $('.fetched-data').html(data);
                }
            });
        });
    });
</script>

<!-- Modal View -->
<div class="modal fade" id="myView1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-menu-gradient">
                <b class="modal-title">Lihat Dokumen</b>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="fetched-data"></div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#myView1').on('show.bs.modal', function(e) {
            var rowid = $(e.relatedTarget).data('id');
            $.ajax({
                type: 'post',
                url: 'view_pres.php',
                data: 'urut=' + rowid,
                success: function(data) {
                    $('.fetched-data').html(data);
                }
            });
        });
    });
</script>


<!-- Modal Cari Siswa -->
<div class="modal fade" id="siswa" role="dialog" data-backdrop="static">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header bg-menu-gradient">
        <b>Cari Siswa Berprestasi</b>
        <button class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="fetched-data"></div>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function () {
    $('#siswa').on('show.bs.modal', function (e) {
      $.ajax({
        type: 'post',
        url: 'dataprestasi.php',
        success: function (data) {
          $('#siswa .fetched-data').html(data);
        }
      });
    });
  });
</script>


<!-- Modal Input Prestasi -->
<div class="modal fade" id="inputpres" role="dialog" data-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-menu-gradient">
        <b>Input Data Prestasi</b>
        <button class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="fetched-input"></div>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function () {
    $('#inputpres').on('show.bs.modal', function (e) {
      // Handle both standard trigger (relatedTarget) and manual trigger (.data('id'))
      var nis = $(e.relatedTarget).data('id') || $(this).data('id');
      var db_year = '<?php echo isset($_GET['db_year']) ? $_GET['db_year'] : ''; ?>';
      
      // Clear previous content to avoid flickering
      $('.fetched-input').html('<div class="text-center p-4"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Memuat form...</p></div>');
      
      $.ajax({
        type: 'post',
        url: 'inputprestasi.php',
        data: { nis: nis, db_year: db_year },
        success: function (data) {
          $('.fetched-input').html(data);
        },
        error: function() {
          $('.fetched-input').html('<div class="alert alert-danger">Gagal memuat form. Silahkan coba lagi.</div>');
        }
      });
    });
  });
</script>



<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- DataTables -->
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    if ($.fn.DataTable.isDataTable('#press')) {
        $('#press').DataTable().destroy();
    }
    $('#press').DataTable({
        responsive: true
    });

    // AJAX Handling for Input Form (Delegated event)
    $(document).on('submit', '#inputpres form', function(e) {
        e.preventDefault();

        // Ensure files are synced from our array to the input
        if (typeof updateInputFiles === 'function') { updateInputFiles(); }

        var formData = new FormData(this);
        formData.append('save', 'true'); 
        formData.append('is_ajax', 'true'); 

        $.ajax({
            url: 'prestasifix.php', 
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.trim() == 'success') {
                    toastr.success("Data Berhasil Ditambahkan");
                    setTimeout(function(){
                        window.location.href = "?press";
                    }, 3000);
                } else {
                    toastr.error("Gagal Menyimpan: " + response);
                }
            },
            error: function() {
                toastr.error("Terjadi kesalahan server");
            }
        });
    });

    // AJAX Handling for Edit Form (Delegated event)
    $(document).on('submit', '#myEdit form', function(e) {
        e.preventDefault();
        
        // Ensure files are synced from our edit array to the input
        var id = $(this).find('input[name="id"]').val();
        if (typeof updateInputFilesEdit === 'function') { updateInputFilesEdit(id); }
        
        var formData = new FormData(this);
        formData.append('update2', 'true'); 
        formData.append('is_ajax', 'true'); 

        $.ajax({
            url: 'prestasifix.php', 
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.trim() == 'success') {
                    toastr.success("Data Berhasil Diupdate");
                    setTimeout(function(){
                        window.location.href = "?press";
                    }, 3000);
                } else {
                    toastr.error("Gagal Diupdate: " + response);
                }
            },
            error: function() {
                toastr.error("Terjadi kesalahan server");
            }
        });
    });

    // AJAX Handling for Delete
    $(document).on('click', 'a[href*="aksi=hapus"]', function(e) {
        e.preventDefault();
        var deleteUrl = $(this).attr('href');
        deleteUrl = deleteUrl.replace('?press', 'prestasifix.php?');
        
        $.ajax({
            url: deleteUrl + '&is_ajax=true',
            type: 'GET',
            success: function(response) {
                if (response.trim() == 'success') {
                    toastr.success("Data berhasil dihapus!");
                    setTimeout(function(){
                        window.location.href = "?press";
                    }, 3000);
                } else {
                    toastr.error("Gagal menghapus data: " + response);
                }
            },
            error: function() {
                toastr.error("Terjadi kesalahan server saat menghapus");
            }
        });
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

function printRekap() {
    var m1 = $('#rekap_m1').val();
    var m2 = $('#rekap_m2').val();
    var y = $('#rekap_y').val();
    var db = '<?php echo $db_req; ?>';
    window.open('print_rekap_pres.php?m1=' + m1 + '&m2=' + m2 + '&y=' + y + '&db=' + db, '_blank');
}

function printRekapTriwulan() {
    var m1 = $('#rekap_m1').val();
    var m2 = $('#rekap_m2').val();
    var y = $('#rekap_y').val();
    var tw = $('#rekap_triwulan').val();
    var kej = $('#rekap_kejuaraan').val();
    var db = '<?php echo $db_req; ?>';
    
    var url = 'print_rekap_triwulan.php?m1=' + m1 + '&m2=' + m2 + '&y=' + y + '&db=' + db;
    if (tw) url += '&tw=' + tw;
    if (kej) url += '&kej=' + encodeURIComponent(kej);
    
    window.open(url, '_blank');
}
</script>
