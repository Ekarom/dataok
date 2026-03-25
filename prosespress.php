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
function get_client_ip()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else {
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
    $penyelenggara = mysqli_real_escape_string($sqlconn, $_POST['penyelenggara']);
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
                        $db_source = !empty($db_req) ? $db_req : (isset($_SESSION['database_asli']) ? $_SESSION['database_asli'] : 'dnet_ad' . date('Y'));
                        if (preg_match('/(\d{4})$/', $db_source, $matches)) {
                            $tahundb = $matches[1];
                        }
                        else {
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
                        }
                        else {
                            $msg = "Gagal menyisipkan file $nama_file_asli ke server (Check permissions)!";
                            if (isset($_POST['is_ajax'])) {
                                echo "error: " . $msg;
                                exit;
                            }
                            echo "<script>alert('$msg');window.history.back();</script>";
                            exit;
                        }
                    }
                    else {
                        $msg = "Ekstensi file $nama_file_asli tidak diperbolehkan (Hanya PDF/Gambar)!";
                        if (isset($_POST['is_ajax'])) {
                            echo "error: " . $msg;
                            exit;
                        }
                        echo "<script>alert('$msg');window.history.back();</script>";
                        exit;
                    }
                }
                else {
                    $msg = "Ukuran file $nama_file_asli melebihi 2MB (Limit Client)!";
                    if (isset($_POST['is_ajax'])) {
                        echo "error: " . $msg;
                        exit;
                    }
                    echo "<script>alert('$msg');window.history.back();</script>";
                    exit;
                }
            }
            else if ($_FILES['file']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                $err_code = $_FILES['file']['error'][$i];
                $msg = "Terjadi kesalahan unggah ($nama_file_asli). ";
                if ($err_code == 1)
                    $msg .= "Ukuran file melebihi batas server (upload_max_filesize di php.ini).";
                else
                    $msg .= "Error Code: $err_code";

                if (isset($_POST['is_ajax'])) {
                    echo "error: " . $msg;
                    exit;
                }
                echo "<script>alert('$msg');window.history.back();</script>";
                exit;
            }
        }
    }

    $pdf_string = implode(',', $pdf_list);

    $addtotable = mysqli_query($sqlconn, "INSERT INTO prestasi (pd, kelas, juara, jenisprestasi, prestasi, nama_kegiatan, tingkat, penyelenggara, tgl_kegiatan, lokasi, bulan, pdf) VALUES ('$pd', '$kelas', '$juara', '$jenisprestasi', '$prestasi', '$kegiatan', '$tingkat', '$penyelenggara', '$tgl_kegiatan', '$lokasi', '$bulan', '$pdf_string')");

    if ($addtotable) {
        write_log("ADD", "Menambah Data Prestasi Atas Nama: $pd");
        if (isset($_POST['is_ajax'])) {
            echo "success";
            exit;
        }
        echo '<script>$(function() { toastr.success("Data berhasil ditambahkan"); setTimeout(function(){ window.location.href = "?press"; }, 3000); });</script>';
    }
    else {
        foreach ($pdf_list as $f) {
            unlink('file/prestasi/' . $f);
        }
        if (isset($_POST['is_ajax'])) {
            echo "error: " . mysqli_error($sqlconn);
            exit;
        }
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
                        $db_source = !empty($db_req) ? $db_req : (isset($_SESSION['database_asli']) ? $_SESSION['database_asli'] : 'dnet_ad' . date('Y'));
                        if (preg_match('/(\d{4})$/', $db_source, $matches)) {
                            $tahundb = $matches[1];
                        }
                        else {
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
                        }
                        else {
                            $msg = "Gagal menyisipkan file $name ke server (Check permissions)!";
                            if (isset($_POST['is_ajax'])) {
                                echo "error: " . $msg;
                                exit;
                            }
                            echo "<script>alert('$msg');window.history.back();</script>";
                            exit;
                        }
                    }
                    else {
                        $msg = "Ekstensi file $name tidak diperbolehkan (Hanya PDF/Gambar)!";
                        if (isset($_POST['is_ajax'])) {
                            echo "error: " . $msg;
                            exit;
                        }
                        echo "<script>alert('$msg');window.history.back();</script>";
                        exit;
                    }
                }
                else {
                    $msg = "Ukuran file $name melebihi 2MB (Limit Client)!";
                    if (isset($_POST['is_ajax'])) {
                        echo "error: " . $msg;
                        exit;
                    }
                    echo "<script>alert('$msg');window.history.back();</script>";
                    exit;
                }
            }
            else if ($_FILES['file']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                $err_code = $_FILES['file']['error'][$i];
                $msg = "Terjadi kesalahan unggah ($name). ";
                if ($err_code == 1)
                    $msg .= "Ukuran file melebihi batas server (upload_max_filesize di php.ini).";
                else
                    $msg .= "Error Code: $err_code";

                if (isset($_POST['is_ajax'])) {
                    echo "error: " . $msg;
                    exit;
                }
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
        if (isset($_POST['is_ajax'])) {
            echo "success";
            exit;
        }
        echo "<script>$(function() { toastr.success('Data berhasil diupdate'); setTimeout(function(){ window.location.href='?press'; }, 3000); });</script>";
    }
    else {
        // If update failed, and new files were uploaded, try to delete them to prevent orphaned files
        foreach ($pdf_list as $f) {
            unlink('file/prestasi/' . $f);
        }
        if (isset($_POST['is_ajax'])) {
            echo "error: " . mysqli_error($sqlconn);
            exit;
        }
        echo "<script>$(function() { toastr.error('Gagal update data: " . mysqli_escape_string($sqlconn, mysqli_error($sqlconn)) . "'); });</script>";
    }
}


// <<<-----------------POST HAPUS--------------->>>
if (isset($_REQUEST['aksi']) && $_REQUEST['aksi'] == 'hapus' && isset($_REQUEST['urut'])) {
    $id_hapus = mysqli_real_escape_string($sqlconn, $_REQUEST['urut']);
    $cek3 = mysqli_query($sqlconn, "SELECT pd, pdf FROM prestasi WHERE id = '$id_hapus'");
    $cek3_data = mysqli_fetch_array($cek3);

    if ($cek3_data) {
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
        if ($sql) {
            write_log("DELETE", "Menghapus data prestasi ID: $id_hapus ($nama_pd)");
            if (isset($_REQUEST['is_ajax'])) {
                echo "success";
                exit;
            }
            echo '<script>$(function() { toastr.success("Data berhasil dihapus!"); setTimeout(function(){ window.location.href="?press"; }, 3000); });</script>';
        }
        else {
            if (isset($_REQUEST['is_ajax'])) {
                echo "error: Gagal menghapus";
                exit;
            }
            echo '<script>$(function() { toastr.error("Gagal menghapus data!"); window.history.back(); });</script>';
        }
    }
    else {
        if (isset($_REQUEST['is_ajax'])) {
            echo "error: Data tidak ditemukan";
            exit;
        }
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
            }
            else if (!empty($f)) {
                $new_pdf_arr[] = $f;
            }
        }

        $new_pdf_str = implode(',', $new_pdf_arr);
        $update = mysqli_query($sqlconn, "UPDATE prestasi SET pdf = '$new_pdf_str' WHERE id = '$id'");

        if ($update) {
            echo "success";
        }
        else {
            echo "error: " . mysqli_error($sqlconn);
        }
    }
    else {
        echo "error: data not found";
    }
    exit;
}
?>
<style>
  
</style>

<script type="text/javascript">
    $(document).ready(function() {
        // Removed modal view1 JS event
    });
</script>


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
    $(document).on('submit', '#inputpresForm', function(e) {
        e.preventDefault();

        // Ensure files are synced from our array to the input
        if (typeof updateInputFiles === 'function') { updateInputFiles(); }

        var formData = new FormData(this);
        formData.append('save', 'true'); 
        formData.append('is_ajax', 'true'); 

        $.ajax({
            url: 'prosespress.php', 
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
    $(document).on('submit', '#formEditPrestasi', function(e) {
        e.preventDefault();
        
        // Ensure files are synced from our edit array to the input
        if (typeof window.updateInputFilesEdit === 'function') { window.updateInputFilesEdit(); }
        
        var formData = new FormData(this);
        formData.append('update2', 'true'); 
        formData.append('is_ajax', 'true'); 

        $.ajax({
            url: 'prosespress.php', 
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
        deleteUrl = deleteUrl.replace('?press', 'prosespress.php?');
        
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

</script>
