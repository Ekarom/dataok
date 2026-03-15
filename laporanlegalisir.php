<?php
include "cfg/konek.php";

// <<<-----------------POST TAMBAH--------------->>>
if (isset($_POST['add'])) {
    $no_surat = mysqli_real_escape_string($sqlconn, $_POST['no_surat']);
    $tgl_dokumen = mysqli_real_escape_string($sqlconn, $_POST['tgl_dokumen']);
    $ditujukan = mysqli_real_escape_string($sqlconn, $_POST['ditujukan']);
    $perihal = mysqli_real_escape_string($sqlconn, $_POST['perihal']);
    $pembuat = mysqli_real_escape_string($sqlconn, $_POST['pembuat']);

    $allowed_extensions = array('pdf');
    $pdf_list = array();
    $total_size = 0;
    $max_total_size = 100 * 1024 * 1024; // 100MB

    if (isset($_FILES['file']) && is_array($_FILES['file']['name'])) {
        $i = 0;
        if ($_FILES['file']['error'][$i] === UPLOAD_ERR_OK) {
            $name = $_FILES['file']['name'][$i];
            $ukuran = $_FILES['file']['size'][$i];
            $ekstensi = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            if ($ukuran <= 2 * 1024 * 1024) {
                if (in_array($ekstensi, $allowed_extensions)) {
                    $timestamp = date('Ymd');

                    // Determine year from DB name
                    $db_source = isset($_GET['db_year']) && !empty($_GET['db_year']) ? $_GET['db_year'] : (isset($_SESSION['database_asli']) ? $_SESSION['database_asli'] : 'dnet_ad' . date('Y'));
                    if (preg_match('/(\d{4})$/', $db_source, $matches)) {
                        $tahundb = $matches[1];
                    } else {
                        $tahundb = date('Y');
                    }

                    $target_dir = 'file/legalisir/' . $tahundb . '/';

                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }

                    $clean_surat = preg_replace('/[^a-zA-Z0-9_-]/', '_', $no_surat);
                    $clean_tuju = preg_replace('/[^a-zA-Z0-9_-]/', '_', $ditujukan);
                    $nama_file_bersih = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($name, PATHINFO_FILENAME));
                    $pdf_name = $tahundb . '/' . $timestamp . '_' . $clean_surat . '_' . $clean_tuju . '_' . $nama_file_bersih . '.' . $ekstensi;

                    if (move_uploaded_file($_FILES['file']['tmp_name'][$i], 'file/legalisir/' . $pdf_name)) {
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

    $simpan = mysqli_query($sqlconn, "insert into legalisir (no_surat,tgl_dokumen,ditujukan,perihal,pembuat,pdf) values('$no_surat','$tgl_dokumen','$ditujukan','$perihal','$pembuat','$pdf_string')");
    if ($simpan) {
        write_log("ADD", "Menambah data legalisir dengan no. surat : $no_surat ");
        if (isset($_POST['is_ajax'])) {
            echo "success";
            exit;
        }
        echo "<script>$(function() { toastr.success('Data Berhasil ditambahkan'); setTimeout(function(){ window.location.href='Print/Laporan Legalisir'; }, 3000); });</script>";
    } else {
        foreach ($pdf_list as $f) {
            unlink('file/legalisir/' . $f);
        }
        if (isset($_POST['is_ajax'])) {
            echo "error: " . mysqli_error($sqlconn);
            exit;
        }
        echo "<script>$(function() { toastr.error('Gagal simpan ke database: " . mysqli_escape_string($sqlconn, mysqli_error($sqlconn)) . "'); });</script>";
    }
}

// <<<-----------------POST UPDATE--------------->>>
if (isset($_POST['update2'])) {
    $id = $_POST['id'];
    $no_surat = mysqli_real_escape_string($sqlconn, $_POST['no_surat']);
    $tgl_dokumen = mysqli_real_escape_string($sqlconn, $_POST['tgl_dokumen']);
    $ditujukan = mysqli_real_escape_string($sqlconn, $_POST['ditujukan']);
    $perihal = mysqli_real_escape_string($sqlconn, $_POST['perihal']);
    $pembuat = mysqli_real_escape_string($sqlconn, $_POST['pembuat']);

    $pdf_list = array();
    $allowed_extensions = array('pdf');

    if (!empty($_FILES['file']['name'][0])) {
        // Hapus file lama
        $ih = mysqli_query($sqlconn, "SELECT pdf FROM legalisir WHERE id='$id'");
        $gt = mysqli_fetch_array($ih);
        $old_files = explode(',', $gt['pdf']);
        foreach ($old_files as $f) {
            if ($f != "" && file_exists('file/legalisir/' . $f)) {
                unlink('file/legalisir/' . $f);
            }
        }

        $i = 0;
        $name = $_FILES['file']['name'][$i];
        if ($_FILES['file']['error'][$i] === UPLOAD_ERR_OK) {
            $ukuran = $_FILES['file']['size'][$i];
            $ekstensi = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if ($ukuran <= 2 * 1024 * 1024) {
                if (in_array($ekstensi, $allowed_extensions)) {
                    $timestamp = date('Ymd');

                    // Determine year from DB name
                    $db_source = isset($_GET['db_year']) && !empty($_GET['db_year']) ? $_GET['db_year'] : (isset($_SESSION['database_asli']) ? $_SESSION['database_asli'] : 'dnet_ad' . date('Y'));
                    if (preg_match('/(\d{4})$/', $db_source, $matches)) {
                        $tahundb = $matches[1];
                    } else {
                        $tahundb = date('Y');
                    }

                    $target_dir = 'file/legalisir/' . $tahundb . '/';

                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }

                    $clean_surat = preg_replace('/[^a-zA-Z0-9_-]/', '_', $no_surat);
                    $clean_tuju = preg_replace('/[^a-zA-Z0-9_-]/', '_', $ditujukan);
                    $nama_file_bersih = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($name, PATHINFO_FILENAME));
                    $pdf_name = $tahundb . '/' . $timestamp . '_' . $clean_surat . '_' . $clean_tuju . '_' . $nama_file_bersih . '.' . $ekstensi;
                    if (move_uploaded_file($_FILES['file']['tmp_name'][$i], 'file/legalisir/' . $pdf_name)) {
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
        $update = mysqli_query($sqlconn, "update legalisir set no_surat='$no_surat',tgl_dokumen='$tgl_dokumen',ditujukan='$ditujukan',perihal='$perihal',pembuat='$pembuat',pdf='$pdf_string' where id='$id'");
    } else {
        $update = mysqli_query($sqlconn, "update legalisir set no_surat='$no_surat',tgl_dokumen='$tgl_dokumen',ditujukan='$ditujukan',perihal='$perihal',pembuat='$pembuat' where id='$id'");
    }

    if ($update) {
        $log_msg = "Update data legalisir dengan No Surat: $no_surat" . (!empty($pdf_list) ? " (Update File)" : "");
        write_log("EDIT", $log_msg);
        if (isset($_POST['is_ajax'])) {
            echo "success";
            exit;
        }
        echo "<script>$(function() { toastr.success('Data Berhasil diubah'); setTimeout(function(){ window.location.href='Print/Laporan Legalisir'; }, 3000); });</script>";
    } else {
        if (isset($_POST['is_ajax'])) {
            echo "error: " . mysqli_error($sqlconn);
            exit;
        }
        echo "<script>$(function() { toastr.error('Gagal update Data legalisir: " . mysqli_escape_string($sqlconn, mysqli_error($sqlconn)) . "'); });</script>";
    }
}

// <<<-----------------POST HAPUS--------------->>>
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus' && isset($_GET['urut'])) {
    $id = mysqli_real_escape_string($sqlconn, $_GET['urut']);
    $cek2 = mysqli_query($sqlconn, "SELECT pdf FROM legalisir WHERE id = '$id'");
    $r_hapus = mysqli_fetch_array($cek2);
    if ($r_hapus) {
        $old_files = explode(',', $r_hapus['pdf']);
        foreach ($old_files as $f) {
            if ($f != "" && file_exists('file/legalisir/' . $f)) {
                unlink('file/legalisir/' . $f);
            }
        }
    }
    $sql = mysqli_query($sqlconn, "delete from legalisir where id= '$id'");
    if ($sql) {
        if (isset($_REQUEST['is_ajax'])) {
            echo "success";
            exit;
        }
        echo '<script>$(function() { toastr.success("Data berhasil dihapus!"); setTimeout(function(){ window.location.href="Print/Laporan Legalisir"; }, 3000); });</script>';
    } else {
        if (isset($_REQUEST['is_ajax'])) {
            echo "error: Gagal menghapus";
            exit;
        }
        echo '<script>$(function() { toastr.error("Gagal menghapus data!"); setTimeout(function(){ window.history.back(); }, 3000); });</script>';
    }
}

// <<<-----------------POST HAPUS FILE--------------->>>
if (isset($_POST['aksi']) && $_POST['aksi'] == 'hapus_file') {
    $id = mysqli_real_escape_string($sqlconn, $_POST['id']);
    $file_to_delete = mysqli_real_escape_string($sqlconn, $_POST['file']);

    $q = mysqli_query($sqlconn, "SELECT pdf FROM legalisir WHERE id = '$id'");
    $data = mysqli_fetch_array($q);

    if ($data) {
        $pdf_arr = explode(',', $data['pdf']);
        $new_pdf_arr = array();

        foreach ($pdf_arr as $f) {
            if ($f == $file_to_delete) {
                if (file_exists("file/legalisir/" . $f)) {
                    unlink("file/legalisir/" . $f);
                }
            } else if (!empty($f)) {
                $new_pdf_arr[] = $f;
            }
        }

        $new_pdf_str = implode(',', $new_pdf_arr);
        $update = mysqli_query($sqlconn, "UPDATE legalisir SET pdf = '$new_pdf_str' WHERE id = '$id'");
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
    /* Action Buttons Styles */
    .btn-action {
        padding: 2px 6px !important;
        font-size: 10px !important;
        font-weight: 600;
        text-transform: capitalize;
        border-radius: 2px !important;
        min-width: 40px;
    }

    /* Select2 Fix */
    .select2-selection__rendered {
        color: #333 !important;
    }
</style>

<link rel="stylesheet" href="plugins/datatables-responsive/css/responsive.bootstrap4.css">
<!-- Content Wrapper. Contains page content -->


<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <?php
        $aksi = isset($_GET['aksi']) ? $_GET['aksi'] : '';

        if ($aksi == 'tambah') {
            include "inputlegalisir.php";
            ?>

            <?php
        } elseif ($aksi == 'edit') {
            include "edit_legalisir.php";
        } elseif ($aksi == 'view') {
            include "view_legalisir.php";
        } else {
            // Table View (Default)
            ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-menu-gradient d-flex align-items-center">
                            <?php if ($lv == "1" || $lv == "2") { ?>
                                <?php
                            } ?>

                        </div>

                        <!-- /.card-header -->
                        <div class="card-body text-nowrap">
                            <table id="leg" class="table table-striped table-sm" style="width:100%">
                                <thead>
                                    <tr align="center">
                                        <th>No</th>
                                        <th>No Surat</th>
                                        <th>Tanggal</th>
                                        <th>Ditujukan Kepada</th>
                                        <th>Perihal</th>
                                        <th>Pembuat</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = mysqli_query($sqlconn, "select * from legalisir order by id");
                                    $no = 0;
                                    while ($s = mysqli_fetch_array($sql)) {
                                        $no++;
                                        ?>
                                        <tr align="center">
                                            <td><?php echo $no; ?></td>
                                            <td><?php echo $s['no_surat']; ?></td>
                                            <td><?php echo $s['tgl_dokumen']; ?></td>
                                            <td><?php echo $s['ditujukan']; ?></td>
                                            <td><?php echo $s['perihal']; ?></td>
                                            <td><?php echo $s['pembuat']; ?></td>
                                            <td>
                                                <?php if ($lv == "1" || $lv == "2") { ?>
                                                    <div class="btn-group">
                                                        <a href='editlegalisir?urut=<?php echo $s['id']; ?>'>
                                                            <button type='button' class="btn btn-primary btn-action">Edit</button>
                                                        </a>
                                                        <a href='viewlegalisir?urut=<?php echo $s['id']; ?>'>
                                                            <button type='button' class="btn btn-info btn-action">View</button>
                                                        </a>
                                                        <a href="laporanlegalisir?aksi=hapus&urut=<?php echo $s['id']; ?>"
                                                            onclick="return confirm('Apakah Anda yakin untuk menghapus data?')">
                                                            <button type='button' class="btn btn-danger btn-action">Hapus</button>
                                                        </a>
                                                    </div>
                                                    <?php
                                                } ?>
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
            <?php
        } ?>
    </div>
</section>

<!-- The following modal definitions and their scripts are removed as they are replaced by direct page views -->
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- DataTables -->
<script src="plugins/datatables/jquery.dataTables.min.js"></script>

<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<script>
    function formatBytes(bytes, decimals = 1) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }

    window.handleFileSelect = function (input) {
        const files = Array.from(input.files);
        const pdfFiles = files.filter(f => f.type === 'application/pdf');
        window.selectedFilesAdd = pdfFiles;

        const listContainer = document.getElementById('file-list-display');
        const totalDisplay = document.getElementById('total-size-display');
        if (!listContainer) return;

        const maxFiles = 1;
        const maxSizeTotal = 2 * 1024 * 1024; // 2MB

        listContainer.innerHTML = '';
        let totalSize = 0;
        let filesToUse = pdfFiles;

        if (filesToUse.length > maxFiles) {
            alert(`Maksimal ${maxFiles} berkas yang dapat diunggah!`);
            filesToUse = filesToUse.slice(0, maxFiles);
        }

        if (filesToUse.length > 0) { listContainer.classList.remove('d-none'); } else { listContainer.classList.add('d-none'); }

        filesToUse.forEach((file, index) => {
            totalSize += file.size;
            const item = document.createElement('div');
            item.className = 'file-item-new';
            item.innerHTML = `
              <div class="file-info-new text-left">
                  <i class="fa fa-file-pdf fa-2x text-info"></i>
                  <div class="file-details-new">
                      <div class="file-name-new">${file.name}</div>
                      <div class="file-size-new">Ukuran Berkas: ${formatBytes(file.size)}</div>
                  </div>
              </div>
          `;
            listContainer.appendChild(item);
        });

        if (totalSize > maxSizeTotal) {
            alert(`Ukuran berkas melebihi 2 MB!`);
            input.value = "";
            listContainer.innerHTML = "";
            listContainer.classList.add('d-none');
            if (totalDisplay) totalDisplay.textContent = "0 B";
            return;
        }
        if (totalDisplay) totalDisplay.textContent = formatBytes(totalSize);
    }

    $(document).ready(function () {
        // Check Form Completion for Tambah View
        function checkFormCompletion() {
            const form = document.querySelector('form');
            const btnIncomplete = document.getElementById('btnIncomplete');
            const btnSave = document.getElementById('btnSave');

            if (!form || !btnIncomplete || !btnSave) return;

            const requiredInputs = form.querySelectorAll('[required]');
            let isComplete = true;

            requiredInputs.forEach(input => {
                if (!input.value.trim()) {
                    isComplete = false;
                }
            });

            if (isComplete) {
                btnIncomplete.style.display = 'none';
                btnSave.style.display = 'inline-block';
            } else {
                btnIncomplete.style.display = 'inline-block';
                btnSave.style.display = 'none';
            }
        }

        $('form').on('input change', 'input, select, textarea', function () {
            checkFormCompletion();
        });
        checkFormCompletion();

        // DataTables Initialization
        $('#leg').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
        });
    });
</script>