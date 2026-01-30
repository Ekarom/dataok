<?php

include "cfg/konek.php";

include "cfg/secure.php";



// Helper function to delete directory recursively

function deleteDirectory($dir) {

    if (!file_exists($dir)) return;

    $files = array_diff(scandir($dir), ['.', '..']);

    foreach ($files as $file) {

        $path = $dir . '/' . $file;

        is_dir($path) ? deleteDirectory($path) : unlink($path);

    }

    rmdir($dir);

}



// Create foto directory if not exists

$foto_dir = "file/fotopd/";

if (!file_exists($foto_dir)) {

    mkdir($foto_dir, 0755, true);

}

?>



<!-- Content Wrapper. Contains page content -->

<div class="content-wrapper">

    <!-- Content Header (Page header) -->

    <section class="content-header">

        <div class="container-fluid">

            <div class="row mb-2">

                <div class="col-sm-6">

                    <h1>Upload Foto Siswa</h1>

                </div>

                <div class="col-sm-6">

                    <ol class="breadcrumb float-sm-right">

                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>

                        <li class="breadcrumb-item active">Upload Foto Siswa</li>

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

                    <div class="card-header bg-menu-gradient text-white">

                        <h3 class="card-title mb-0">Upload Foto Siswa (ZIP)</h3>

                    </div>

                    <div class="card-body">

                        <!-- Instructions -->

                        <div class="alert alert-info">

                            <h5><i class="fas fa-info-circle"></i> Petunjuk Upload Foto</h5>

                            <ul class="mb-0">

                                <li>Upload file ZIP yang berisi foto-foto siswa</li>

                                <li>Nama file foto harus sesuai dengan <strong>NIS siswa</strong> (contoh: <code>12345.jpg</code>)</li>

                                <li>Format foto yang didukung: JPG, JPEG, PNG, GIF</li>

                                <li>Maksimal ukuran ZIP: 100 MB</li>

                            </ul>

                        </div>



                        <!-- Upload Form -->

                        <form method="post" enctype="multipart/form-data" action="?modul=Upload_Foto">

                            <div class="mb-3">

                                <label class="form-label">File ZIP Foto Siswa:</label>

                                <input name="zipfile" type="file" class="form-control" accept=".zip" required>

                                <small class="text-muted">Maksimal 100 MB</small>

                            </div>

                            <button name="upload" type="submit" class="btn btn-primary">

                                <i class="fas fa-upload"></i> Upload & Proses

                            </button>

                        </form>



                        <?php if (isset($_REQUEST['modul']) && $_REQUEST['modul'] == "Upload_Foto" && isset($_FILES['zipfile'])): ?>

                            <div class="mt-4">

                                <h5>Proses Upload Foto</h5>

                                

                                <!-- Progress bar -->

                                <div class="progress" style="height: 30px;">

                                    <div id="progress" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 

                                         role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">

                                        0%

                                    </div>

                                </div>

                                

                                <!-- Progress information -->

                                <div id="information" class="mt-2 text-muted"></div>

                            </div>



                            <?php

                            // Validate file upload

                            if (!isset($_FILES['zipfile']) || $_FILES['zipfile']['error'] !== UPLOAD_ERR_OK) {

                                echo '<div class="alert alert-danger mt-3">';

                                echo '<i class="fas fa-exclamation-triangle"></i> <strong>Error Upload:</strong> ';

                                

                                if (!isset($_FILES['zipfile'])) {

                                    echo 'File tidak ditemukan. Silakan pilih file ZIP terlebih dahulu.';

                                } else {

                                    switch ($_FILES['zipfile']['error']) {

                                        case UPLOAD_ERR_INI_SIZE:

                                            echo 'File terlalu besar. Maksimal ukuran yang diizinkan server: ' . ini_get('upload_max_filesize');

                                            break;

                                        case UPLOAD_ERR_FORM_SIZE:

                                            echo 'File terlalu besar. Maksimal 100 MB.';

                                            break;

                                        case UPLOAD_ERR_PARTIAL:

                                            echo 'File hanya terupload sebagian. Silakan coba lagi.';

                                            break;

                                        case UPLOAD_ERR_NO_FILE:

                                            echo 'Tidak ada file yang dipilih. Silakan pilih file ZIP.';

                                            break;

                                        case UPLOAD_ERR_NO_TMP_DIR:

                                            echo 'Folder temporary tidak ditemukan. Hubungi administrator.';

                                            break;

                                        case UPLOAD_ERR_CANT_WRITE:

                                            echo 'Gagal menulis file ke disk. Hubungi administrator.';

                                            break;

                                        case UPLOAD_ERR_EXTENSION:

                                            echo 'Upload dihentikan oleh ekstensi PHP. Hubungi administrator.';

                                            break;

                                        default:

                                            echo 'Upload gagal dengan error code: ' . $_FILES['zipfile']['error'];

                                    }

                                }

                                

                                echo '<br><small class="text-muted">Pengaturan server: upload_max_filesize = ' . ini_get('upload_max_filesize') . ', post_max_size = ' . ini_get('post_max_size') . '</small>';

                                echo '</div>';

                                goto skip_upload;

                            }



                            // Check file size (100MB max)

                            $max_size = 100 * 1024 * 1024; // 100 MB

                            if ($_FILES['zipfile']['size'] > $max_size) {

                                echo '<div class="alert alert-danger mt-3">';

                                echo '<i class="fas fa-exclamation-triangle"></i> Error: Ukuran file terlalu besar. Maksimal 100 MB.';

                                echo '</div>';

                                goto skip_upload;

                            }



                            // Validate MIME type

                            $finfo = finfo_open(FILEINFO_MIME_TYPE);

                            $mime_type = finfo_file($finfo, $_FILES['zipfile']['tmp_name']);

                            finfo_close($finfo);

                            

                            $allowed_mimes = ['application/zip', 'application/x-zip-compressed', 'multipart/x-zip'];

                            if (!in_array($mime_type, $allowed_mimes)) {

                                echo '<div class="alert alert-danger mt-3">';

                                echo '<i class="fas fa-exclamation-triangle"></i> Error: File harus berformat ZIP yang valid.';

                                echo '</div>';

                                goto skip_upload;

                            }



                            // Check file extension

                            $file_extension = strtolower(pathinfo($_FILES['zipfile']['name'], PATHINFO_EXTENSION));

                            if ($file_extension != 'zip') {

                                echo '<div class="alert alert-danger mt-3">';

                                echo '<i class="fas fa-exclamation-triangle"></i> Error: File harus berformat ZIP.';

                                echo '</div>';

                                goto skip_upload;

                            }



                            // Create temp directory

                            $temp_dir = "temp/upload_foto_" . time() . "/";

                            if (!file_exists($temp_dir)) {

                                mkdir($temp_dir, 0755, true);

                            }



                            // Extract ZIP

                            $zip = new ZipArchive;

                            $zip_path = $_FILES['zipfile']['tmp_name'];

                            

                            if ($zip->open($zip_path) === TRUE) {

                                $zip->extractTo($temp_dir);

                                $zip->close();

                                

                                echo '<div class="alert alert-success mt-3">';

                                echo '<i class="fas fa-check-circle"></i> ZIP berhasil diekstrak. Memproses foto...';

                                echo '</div>';

                            } else {

                                echo '<div class="alert alert-danger mt-3">';

                                echo '<i class="fas fa-exclamation-triangle"></i> Error: Gagal membuka file ZIP.';

                                echo '</div>';

                                goto skip_upload;

                            }



                            // Process photos

                            $sukses = 0;

                            $gagal = 0;

                            $tidak_ditemukan = 0;

                            

                            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

                            

                            try {

                                $files = new RecursiveIteratorIterator(

                                    new RecursiveDirectoryIterator($temp_dir, RecursiveDirectoryIterator::SKIP_DOTS)

                                );

                                $photo_files = [];

                                

                                foreach ($files as $file) {

                                    if ($file->isFile()) {

                                        $ext = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));

                                        if (in_array($ext, $allowed_extensions)) {

                                            $photo_files[] = $file->getPathname();

                                        }

                                    }

                                }

                            } catch (Exception $e) {

                                echo '<div class="alert alert-danger mt-3">';

                                echo '<i class="fas fa-exclamation-triangle"></i> Error: Gagal membaca direktori ZIP.';

                                echo '</div>';

                                deleteDirectory($temp_dir);

                                goto skip_upload;

                            }



                            $total_files = count($photo_files);

                            

                            if ($total_files == 0) {

                                echo '<div class="alert alert-warning mt-3">';

                                echo '<i class="fas fa-info-circle"></i> Tidak ada foto yang valid ditemukan dalam ZIP.';

                                echo '</div>';

                                deleteDirectory($temp_dir);

                                goto skip_upload;

                            }



                            // Check if destination directory is writable

                            if (!is_writable($foto_dir)) {

                                echo '<div class="alert alert-danger mt-3">';

                                echo '<i class="fas fa-exclamation-triangle"></i> Error: Direktori tujuan tidak dapat ditulis.';

                                echo '</div>';

                                deleteDirectory($temp_dir);

                                goto skip_upload;

                            }



                            echo "<div class='table-responsive mt-3'><table class='table table-sm table-bordered table-hover'>";

                            echo "<thead class='table-dark'><tr><th>Foto</th><th>NIS</th><th>Nama Siswa</th><th>Status</th></tr></thead><tbody>";



                            $counter = 0;

                            foreach ($photo_files as $photo_path) {

                                $counter++;

                                $photo_filename = basename($photo_path);

                                $nis = pathinfo($photo_filename, PATHINFO_FILENAME);



                                // Find student by NIS using prepared statement

                                $stmt = mysqli_prepare($sqlconn, "SELECT pd, nis FROM siswa WHERE nis = ? LIMIT 1");

                                mysqli_stmt_bind_param($stmt, "s", $nis);

                                mysqli_stmt_execute($stmt);

                                $result_siswa = mysqli_stmt_get_result($stmt);



                                if ($result_siswa && mysqli_num_rows($result_siswa) > 0) {

                                    $siswa = mysqli_fetch_assoc($result_siswa);

                                    

                                    // Sanitize filename

                                    $ext = strtolower(pathinfo($photo_filename, PATHINFO_EXTENSION));

                                    $new_filename = preg_replace('/[^a-zA-Z0-9]/', '', $nis) . '.' . $ext;

                                    $destination = $foto_dir . $new_filename;

                                    

                                    if (copy($photo_path, $destination)) {

                                        // Set proper file permissions

                                        chmod($destination, 0644);

                                        

                                        // Update database using prepared statement

                                        $update_stmt = mysqli_prepare($sqlconn, "UPDATE siswa SET photo = ? WHERE nis = ?");

                                        mysqli_stmt_bind_param($update_stmt, "ss", $new_filename, $nis);

                                        

                                        if (mysqli_stmt_execute($update_stmt)) {

                                            echo "<tr class='table-success'><td>" . htmlspecialchars($photo_filename) . "</td><td>" . htmlspecialchars($nis) . "</td><td><strong>" . htmlspecialchars($siswa['pd']) . "</strong></td>";

                                            echo "<td><span class='badge bg-success'>Sukses</span></td></tr>";

                                            $sukses++;

                                        } else {

                                            echo "<tr class='table-danger'><td>" . htmlspecialchars($photo_filename) . "</td><td>" . htmlspecialchars($nis) . "</td><td>" . htmlspecialchars($siswa['pd']) . "</td>";

                                            echo "<td><span class='badge bg-danger'>Error DB</span></td></tr>";

                                            $gagal++;

                                        }

                                        mysqli_stmt_close($update_stmt);

                                    } else {

                                        echo "<tr class='table-danger'><td>" . htmlspecialchars($photo_filename) . "</td><td>" . htmlspecialchars($nis) . "</td><td>" . htmlspecialchars($siswa['pd']) . "</td>";

                                        echo "<td><span class='badge bg-danger'>Error Copy</span></td></tr>";

                                        $gagal++;

                                    }

                                } else {

                                    echo "<tr><td>" . htmlspecialchars($photo_filename) . "</td><td>" . htmlspecialchars($nis) . "</td><td>-</td>";

                                    echo "<td><span class='badge bg-warning'>Tidak Ditemukan</span></td></tr>";

                                    $tidak_ditemukan++;

                                }

                                mysqli_stmt_close($stmt);



                                // Update progress (reduced memory usage)

                                $percent = intval($counter / $total_files * 100);

                                $safe_filename = htmlspecialchars($photo_filename);

                                echo '<script>

                                var progressBar = document.getElementById("progress");

                                progressBar.style.width = "' . $percent . '%";

                                progressBar.setAttribute("aria-valuenow", ' . $percent . ');

                                progressBar.textContent = "' . $percent . '%";

                                document.getElementById("information").innerHTML = "Memproses: <strong>' . $safe_filename . '</strong> ... <b>' . $counter . '</b> dari <b>' . $total_files . '</b> foto.";

                                </script>';



                                // Reduced buffer size for better memory management

                                echo str_repeat(' ', 1024);

                                flush();

                            }



                            echo "</tbody></table></div>";



                            // Clean up temp directory

                            deleteDirectory($temp_dir);



                            // Display summary

                            ?>

                            <div class="mt-3">

                                <?php if ($sukses > 0): ?>

                                    <div class="alert alert-success d-flex align-items-center">

                                        <i class="fas fa-check-circle me-2"></i>

                                        <div>Jumlah foto yang berhasil diupload: <strong><?php echo intval($sukses); ?></strong></div>

                                    </div>

                                <?php endif; ?>



                                <?php if ($tidak_ditemukan > 0): ?>

                                    <div class="alert alert-warning d-flex align-items-center">

                                        <i class="fas fa-exclamation-circle me-2"></i>

                                        <div>Jumlah foto yang tidak ditemukan siswa: <strong><?php echo intval($tidak_ditemukan); ?></strong></div>

                                    </div>

                                <?php endif; ?>



                                <?php if ($gagal > 0): ?>

                                    <div class="alert alert-danger d-flex align-items-center">

                                        <i class="fas fa-exclamation-triangle me-2"></i>

                                        <div>Jumlah foto yang gagal diupload: <strong><?php echo intval($gagal); ?></strong></div>

                                    </div>

                                <?php endif; ?>

                            </div>



                            <script>

                            document.getElementById("information").innerHTML = "<i class='fas fa-check text-success'></i> Proses upload foto selesai!";

                            </script>

                            

                            <?php skip_upload: ?>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </div>

    </section>

</div>

