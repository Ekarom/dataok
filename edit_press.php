<?php
include "cfg/konek.php";
include "cfg/secure.php";
include "prosespress.php";

/**
 * Handle AJAX file deletion
 */
if (isset($_POST['aksi']) && $_POST['aksi'] == 'hapus_file') {
    $id = mysqli_real_escape_string($sqlconn, $_POST['id']);
    $file_to_delete = mysqli_real_escape_string($sqlconn, $_POST['file']);

    $q = mysqli_query($sqlconn, "SELECT pdf FROM prestasi WHERE id = '$id'");
    $data = mysqli_fetch_array($q);

    if ($data) {
        $pdf_arr = explode(',', $data['pdf']);
        $new_pdf_arr = array();
        $deleted = false;

        foreach ($pdf_arr as $f) {
            if ($f == $file_to_delete) {
                if (!empty($f) && file_exists("file/prestasi/" . $f)) {
                    unlink("file/prestasi/" . $f);
                }
                $deleted = true;
            } else if (!empty($f)) {
                $new_pdf_arr[] = $f;
            }
        }

        if ($deleted) {
            $new_pdf_str = implode(',', $new_pdf_arr);
            $update = mysqli_query($sqlconn, "UPDATE prestasi SET pdf = '$new_pdf_str' WHERE id = '$id'");
            echo $update ? "success" : "error: " . mysqli_error($sqlconn);
        } else {
            echo "error: file not found in record";
        }
    } else {
        echo "error: data not found";
    }
    exit;
}

/**
 * Main Form Display Logic
 */
if (isset($_REQUEST['urut'])) {
    $id = mysqli_real_escape_string($sqlconn, $_REQUEST['urut']);
    $rql = mysqli_query($sqlconn, "SELECT * FROM prestasi WHERE id = '$id'");
    $r = mysqli_fetch_array($rql);

    // Jika tidak ditemukan, coba cari prestasi terbaru untuk siswa jika 'urut' adalah ID siswa
    if (!$r) {
        $sql_siswa = mysqli_query($sqlconn, "SELECT pd, kelas, nis FROM siswa WHERE id = '$id'");
        $d_siswa = mysqli_fetch_array($sql_siswa);
        if ($d_siswa) {
            $s_pd = mysqli_real_escape_string($sqlconn, $d_siswa['pd']);
            $s_kls = mysqli_real_escape_string($sqlconn, $d_siswa['kelas']);
            $s_nis = mysqli_real_escape_string($sqlconn, $d_siswa['nis']);
            $rql = mysqli_query($sqlconn, "SELECT * FROM prestasi WHERE pd = '$s_pd' AND kelas = '$s_kls' ORDER BY id DESC LIMIT 1");
            if ($rql) {
                $r = mysqli_fetch_array($rql);
            }
        }
    }

    if (!$r) {
        echo "<div class='alert alert-danger'>Data tidak ditemukan.</div>";
        exit;
    }

    $ting = $r['tingkat'];
    $pd_name = mysqli_real_escape_string($sqlconn, $r['pd']);
    $pd_kelas = mysqli_real_escape_string($sqlconn, $r['kelas']);
    $sql_pd = mysqli_query($sqlconn, "SELECT photo, nis FROM siswa WHERE pd = '$pd_name' AND kelas = '$pd_kelas'");
    $d_pd = mysqli_fetch_array($sql_pd);

    $photo_src = "images/male.png";
    if ($d_pd && !empty($d_pd['photo']) && file_exists("file/fotopd/" . $d_pd['photo'])) {
        $photo_src = "file/fotopd/" . $d_pd['photo'];
    }

    $nis_view = isset($d_pd['nis']) ? $d_pd['nis'] : '-';

    // Helper function for formatting bytes
    if (!function_exists('formatBytesPHP')) {
        function formatBytesPHP($bytes, $precision = 1)
        {
            if ($bytes <= 0)
                return '0 B';
            $units = array('B', 'KB', 'MB', 'GB', 'TB');
            $pow = floor(log($bytes, 1024));
            $pow = min($pow, count($units) - 1);
            $bytes /= pow(1024, $pow);
            return round($bytes, $precision) . ' ' . $units[$pow];
        }
    }
    ?>
    <style>
        .form-control.warna {
            background-color: #01b2d1 !important;
            color: #fff !important;
            border: none !important;
            border-radius: 4px !important;
        }

        .form-control.warna::placeholder {
            color: rgba(255, 255, 255, 0.7) !important;
        }

        .form-control.warna option {
            background-color: #fff !important;
            color: #333 !important;
        }

        .card-title {
            font-weight: 600;
        }

        .bg-menu-gradient {
            background: linear-gradient(135deg, #2c3e50 0%, #01b2d1 100%);
            color: #fff;
        }

        .profile-card {
            border-top: 3px solid #2c3e50;
        }

        .label-custom {
            font-weight: 700;
            color: #333;
        }

        /* Select2 Standard Form Control Styling */
        .select2-container .select2-selection--single {
            height: 38px !important;
            padding: 0.375rem 0.75rem !important;
            font-size: 1rem !important;
            line-height: 1.5 !important;
            border-radius: 0.25rem !important;
            border: 1px solid #ced4da;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 24px !important;
            padding-left: 0 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
        }

        /* Hover / Active highlight color specifically matching the image reference */
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #007bff !important;
            color: white !important;
        }
    </style>
    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <form method="post" enctype="multipart/form-data" id="formEditPrestasi">
                <div class="row">
                    <!-- Student Info Column -->
                    <div class="col-md-4">
                        <div class="card profile-card">
                            <div class="card-body box-profile">
                                <div class="text-center">
                                    <img class="profile-user-img img-fluid img-circle" src="<?php echo $photo_src; ?>"
                                        alt="User profile picture"
                                        style="width: 140px; height: 140px; object-fit: cover; border: 4px solid #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                                </div>
                                <h3 class="profile-username text-center mt-3"
                                    style="font-weight: 800; text-transform: uppercase; color: #444;">
                                    <?php echo $pd_name; ?>
                                </h3>
                                <div class="text-center mt-2">
                                    <small class="font-weight-bold d-block">KELAS</small>
                                    <span class="badge bg-menu-gradient"><?php echo $pd_kelas; ?></span>
                                </div>
                                <hr>
                                <div class="text-center">
                                    <small class="font-weight-bold d-block">NIS</small>
                                    <span class="badge bg-menu-gradient"><?php echo $nis_view; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Input Form Column -->
                    <div class="col-md-8">
                        <div class="card h-100">
                            <div class="card-header bg-menu-gradient d-flex align-items-center">
                                <h3 class="card-title text-white">Edit Prestasi</h3>
                                <div class="card-tools ml-auto">
                                    <a href="arsipdata/inputprestasi" class="btn btn-warning btn-sm rounded-pill">
                                        <i class="fa fa-arrow-left mr-1"></i> Kembali
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                                <input type="hidden" name="pd" value="<?php echo $pd_name; ?>">
                                <input type="hidden" name="kelas" value="<?php echo $pd_kelas; ?>">
                                <input type="hidden" name="db_year"
                                    value="<?php echo isset($_REQUEST['db_year']) ? $_REQUEST['db_year'] : ''; ?>">

                                <div class="form-group row mb-4">
                                    <label for="prestasi" class="col-sm-4 col-form-label label-custom">Prestasi</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" name="prestasi"
                                            value="<?php echo $r['prestasi']; ?>"
                                            placeholder="Contoh: Juara 1 Lomba Web Design" required>
                                    </div>
                                </div>

                                <div class="form-group row mb-4">
                                    <label for="jenisprestasi" class="col-sm-4 col-form-label label-custom">Jenis
                                        Prestasi</label>
                                    <div class="col-sm-8">
                                        <select class="form-control select2" name="jenisprestasi" required>
                                            <option value="">- Pilih Jenis -</option>
                                            <option value="Akademik" <?php echo ($r['jenisprestasi'] == 'Akademik') ? 'selected' : ''; ?>>Akademik</option>
                                            <option value="Non-Akademik" <?php echo ($r['jenisprestasi'] == 'Non-Akademik') ? 'selected' : ''; ?>>Non-Akademik</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row mb-4">
                                    <label for="tingkat" class="col-sm-4 col-form-label label-custom">Tingkat</label>
                                    <div class="col-sm-8">
                                        <select class="form-control select2" name="tingkat" required>
                                            <option value="">- Pilih Tingkat -</option>
                                            <?php
                                            $levels = ['Sekolah', 'Kecamatan', 'Kabupaten/Kota', 'Provinsi', 'Nasional', 'Internasional'];
                                            foreach ($levels as $level) {
                                                $selected = ($ting == $level) ? 'selected' : '';
                                                echo "<option value='$level' $selected>$level</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row mb-4">
                                    <label for="tgl_kegiatan" class="col-sm-4 col-form-label label-custom">Tanggal
                                        Kegiatan</label>
                                    <div class="col-sm-8">
                                        <input type="date" class="form-control" name="tgl_kegiatan"
                                            value="<?php echo $r['tgl_kegiatan']; ?>" required>
                                    </div>
                                </div>
                                <div class="form-group row mb-4">
                                    <label for="nama_kegiatan" class="col-sm-4 col-form-label label-custom">Nama
                                        Kegiatan</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" name="nama_kegiatan"
                                            value="<?php echo $r['nama_kegiatan']; ?>" placeholder="Isi nama kegiatan"
                                            required>
                                    </div>
                                </div>
                                <div class="form-group row mb-4">
                                    <label for="penyelenggara"
                                        class="col-sm-4 col-form-label label-custom">Penyelenggara</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" name="penyelenggara"
                                            value="<?php echo $r['penyelenggara']; ?>"
                                            placeholder="Contoh: Dinas Pendidikan" required>
                                    </div>
                                </div>
                                <div class="form-group row mb-4">
                                    <label for="lokasi" class="col-sm-4 col-form-label label-custom">Lokasi</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control " name="lokasi"
                                            value="<?php echo $r['lokasi']; ?>" placeholder="Isi Lokasi" required>
                                    </div>
                                </div>

                                <div class="form-group row mb-4">
                                    <label for="juara" class="col-sm-4 col-form-label label-custom">Juara Ke-</label>
                                    <div class="col-sm-8">
                                        <select class="form-control select2" id="juara" name="juara" required>
                                            <option value="">- Pilih Juara -</option>
                                            <?php
                                            $juaras = ['1' => 'Juara 1', '2' => 'Juara 2', '3' => 'Juara 3', '4' => 'Juara 4', 'Harapan 1' => 'Juara Harapan 1', 'Harapan 2' => 'Juara Harapan 2', 'Harapan 3' => 'Juara Harapan 3'];
                                            foreach ($juaras as $val => $label) {
                                                $selected = ($r['juara'] == $val) ? 'selected' : '';
                                                echo "<option value='$val' $selected>$label</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row mb-4">
                                    <label for="bulan" class="col-sm-4 col-form-label label-custom">Bulan</label>
                                    <div class="col-sm-8">
                                        <select class="form-control select2" id="bulan" name="bulan" required>
                                            <option value="">- Pilih Bulan -</option>
                                            <?php
                                            $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                            foreach ($months as $m) {
                                                $sel = ($r['bulan'] == $m) ? 'selected' : '';
                                                echo "<option value='$m' $sel>$m</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row mb-4">
                                    <label for="file" class="col-sm-4 col-form-label label-custom">Upload Lampiran
                                        (PDF/Gambar)</label>
                                    <div class="col-sm-8">
                                        <div class="file-upload-wrapper" id="dropAreaEdit_<?php echo $id; ?>">
                                            <div class="file-upload-selector border rounded">
                                                <div class="upload-area d-flex align-items-center p-2">
                                                    <div style="flex-shrink: 0;">
                                                        <button type="button" class="btn btn-sm btn-primary"
                                                            onclick="document.getElementById('fileInputEdit_<?php echo $id; ?>').click()">Pilih
                                                            File...</button>
                                                    </div>
                                                    <div class="dropzone text-center prevent-select ml-2"
                                                        style="font-size: 13px; color: #777;">
                                                        <span class="upload-text"><i
                                                                class="fa fa-cloud-upload-alt mr-1"></i> atau drag &
                                                            drop berkas disini.</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div id="existingFileList_<?php echo $id; ?>" class="mt-2">
                                                <?php
                                                $existing_files = explode(',', $r['pdf']);
                                                $total_size_bytes = 0;

                                                foreach ($existing_files as $idx => $f) {
                                                    if (!empty($f)) {
                                                        $file_path = 'file/prestasi/' . $f;
                                                        $size = file_exists($file_path) ? filesize($file_path) : 0;
                                                        $total_size_bytes += $size;

                                                        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                                                        $icon = (in_array($ext, ['jpg', 'jpeg', 'png'])) ? 'fa-file-image text-success' : 'fa-file-pdf text-info';

                                                        // Shorten display name
                                                        $display_name = basename($f);
                                                        if (strlen($display_name) > 40) {
                                                            $display_name = substr($display_name, 0, 37) . '...';
                                                        }

                                                        echo "
                                                <div class='file-item-new existing-file-item file-info-new p-2 border-bottom' id='item_ext_{$id}_{$idx}' data-size='$size' style='display: flex; align-items: center;'>
                                                    <i class='fa $icon fa-2x' style='margin-right: 12px;'></i>
                                                    <div class='file-details-new' style='flex-grow: 1; overflow: hidden;'>
                                                        <div class='file-name-new text-truncate'><a href='file/prestasi/$f' target='_blank' class='text-info text-decoration-none' title='$f'>$display_name</a></div>
                                                        <div class='file-size-new'>Tersimpan di server (" . formatBytesPHP($size) . ")</div>
                                                    </div>
                                                    <div class='delete-btn-new ml-auto' onclick='deleteExistingFile(\"{$id}\", \"{$f}\", \"{$idx}\", event)' title='Hapus Lampiran' style='cursor: pointer;'>
                                                        <i class='fa fa-trash-alt text-danger'></i>
                                                    </div>
                                                </div>";
                                                    }
                                                }
                                                ?>
                                            </div>

                                            <div id="newFileList_<?php echo $id; ?>" class="file-list-uploaded d-none">
                                                <!-- File items will be injected here -->
                                            </div>
                                            <div class="uploader-footer mt-2">
                                                <div class="fs-xs"><span class="font-600">Total : </span><span
                                                        id="totalSizeLabel_<?php echo $id; ?>"><?php echo formatBytesPHP($total_size_bytes); ?></span>
                                                </div>
                                                <div class="fs-nano text-muted" style="font-size: 11px;">
                                                    Lampirkan berkas <span class="font-600">.pdf / .jpg / .png</span>
                                                    maksimal <span class="font-600">2</span> berkas dan ukuran maksimal
                                                    <span class="font-600">2.0 MB</span>
                                                </div>
                                            </div>
                                            <input type="file" name="file[]" id="fileInputEdit_<?php echo $id; ?>"
                                                class="hidden-file-input" style="display:none;"
                                                accept=".pdf,.jpg,.jpeg,.png" multiple
                                                onchange="handleFileSelectEdit(this)">
                                        </div>
                                        <small class="text-warning mt-2 d-block" style="font-size: 11px;">*Opsional.
                                            Upload sertifikat/dokumentasi.</small>
                                    </div>
                                </div>



                                <div class="card-footer d-flex justify-content-end">
                                    <button type="button" id="btnIncomplete_<?php echo $id; ?>"
                                        class="btn bg-gradient-secondary custom" onclick="showFormErrors()">Isian Belum
                                        Lengkap</button>
                                    <button type="submit" id="btnSubmit_<?php echo $id; ?>" name="update2"
                                        class="btn bg-gradient-success custom" style="display: none;"><i
                                            class="fa fa-save mr-1"></i>
                                        Simpan Perubahan</button>
                                </div>
                            </div> <!-- /.col-md-8 -->
                        </div> <!-- /.row -->
                    </div> <!-- /.card-body -->
                </div> <!-- /.card -->
        </div>
        </div>
        </div>
        </form>
        </div> <!-- /.container-fluid -->
    </section>
    </div> <!-- /.content-wrapper -->

    <script>
        /**
         * JavaScript Module for Edit Prestasi
         */
        (function () {
            const ID = '<?php echo $id; ?>';
            const MAX_SIZE = 2 * 1024 * 1024; // 2MB
            let selectedFiles = [];

            // Initialize UI
            const form = document.getElementById('formEditPrestasi');
            const inputFiles = document.getElementById('fileInputEdit_' + ID);
            const dropArea = document.getElementById('dropAreaEdit_' + ID);
            const btnSubmit = document.getElementById('btnSubmit_' + ID);
            const btnIncomplete = document.getElementById('btnIncomplete_' + ID);

            // Prevent default drag behaviors
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                if (dropArea) dropArea.addEventListener(eventName, e => { e.preventDefault(); e.stopPropagation(); }, false);
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                if (dropArea) dropArea.addEventListener(eventName, () => dropArea.classList.add('dragover'), false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                if (dropArea) dropArea.addEventListener(eventName, () => dropArea.classList.remove('dragover'), false);
            });

            if (dropArea) {
                dropArea.addEventListener('drop', (e) => {
                    const dt = e.dataTransfer;
                    handleFiles(dt.files);
                }, false);
            }

            window.handleFileSelectEdit = function (input) {
                handleFiles(input.files);
            };

            function handleFiles(files) {
                const fileArr = Array.from(files);
                const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
                const validFiles = fileArr.filter(f => allowedTypes.includes(f.type));

                if (fileArr.length > validFiles.length) {
                    toastr.error('Hanya berkas PDF, JPG, atau PNG yang diperbolehkan!');
                }

                validFiles.forEach(file => {
                    if (selectedFiles.length < 2) {
                        if (!selectedFiles.some(f => f.name === file.name && f.size === file.size)) {
                            selectedFiles.push(file);
                        }
                    }
                });

                if (selectedFiles.length > 2) {
                    toastr.warning('Maksimal 2 berkas diperbolehkan.');
                    selectedFiles = selectedFiles.slice(0, 2);
                }

                renderFileList();
                updateInputFiles();
            }

            function renderFileList() {
                const container = document.getElementById(`newFileList_${ID}`);
                if (!container) return;

                container.innerHTML = '';
                let totalSize = 0;

                // Calculate size from existing files still in view
                $(`#existingFileList_${ID} .existing-file-item`).each(function () {
                    totalSize += parseInt($(this).attr('data-size') || 0);
                });

                if (selectedFiles.length > 0) {
                    container.classList.remove('d-none');
                } else {
                    container.classList.add('d-none');
                }

                selectedFiles.forEach((file, index) => {
                    totalSize += file.size;
                    const isImage = file.type.startsWith('image/');
                    const iconClass = isImage ? 'fa-file-image text-success' : 'fa-file-pdf text-info';

                    const div = document.createElement('div');
                    div.className = 'file-item-new p-2 border-bottom';
                    div.innerHTML = `
                <div class="file-info-new" style="display: flex; align-items: center;">
                    <i class="fa ${iconClass} fa-2x" style="margin-right: 12px;"></i>
                    <div class="file-details-new" style="flex-grow: 1;">
                        <div class="file-name-new">${file.name}</div>
                        <div class="file-size-new mt-1">Ukuran Berkas: ${formatBytes(file.size)}</div>
                    </div>
                    <div class="delete-btn-new ml-auto" onclick="removeSelectedFile(${index})" title="Hapus File" style="cursor: pointer;">
                        <i class="fa fa-trash-alt"></i>
                    </div>
                </div>
            `;
                    container.appendChild(div);
                });

                const totalSizeLabel = document.getElementById(`totalSizeLabel_${ID}`);
                if (totalSizeLabel) totalSizeLabel.innerText = formatBytes(totalSize);

                if (totalSize > MAX_SIZE) {
                    toastr.error('Total ukuran berkas melebihi 2MB!');
                    if (btnSubmit) btnSubmit.classList.add('disabled');
                    if (btnSubmit) btnSubmit.style.pointerEvents = 'none';
                } else {
                    if (btnSubmit) btnSubmit.classList.remove('disabled');
                    if (btnSubmit) btnSubmit.style.pointerEvents = 'auto';
                    checkFormValidity();
                }
            }

            window.removeSelectedFile = function (index) {
                selectedFiles.splice(index, 1);
                updateInputFiles();
                renderFileList();
            };

            function updateInputFiles() {
                if (!inputFiles) return;
                const dt = new DataTransfer();
                selectedFiles.forEach(file => dt.items.add(file));
                inputFiles.files = dt.files;
            }

            window.deleteExistingFile = function (id, fileName, idx, event) {
                event.stopPropagation();
                if (!confirm('Hapus lampiran ini dari database?')) return;

                $.ajax({
                    url: 'edit_press.php',
                    type: 'POST',
                    data: { aksi: 'hapus_file', id: id, file: fileName },
                    success: function (res) {
                        if (res.trim() === 'success') {
                            $(`#item_ext_${id}_${idx}`).fadeOut(300, function () {
                                $(this).remove();
                                renderFileList(); // Recalculate total size
                            });
                            if (typeof toastr !== 'undefined') toastr.success('Lampiran dihapus');
                        } else {
                            if (typeof toastr !== 'undefined') toastr.error('Gagal hapus: ' + res);
                        }
                    },
                    error: () => {
                        if (typeof toastr !== 'undefined') toastr.error('Server error');
                    }
                });
            };

            function checkFormValidity() {
                if (!form) return;
                let isValid = true;

                // Remove old error highlights
                $(form).find('.is-invalid').removeClass('is-invalid');
                $(form).find('.select2-selection').removeClass('border-danger');

                const requiredInputs = form.querySelectorAll('[required]');

                requiredInputs.forEach(el => {
                    const val = $(el).val();
                    if (!val || val.toString().trim() === '') {
                        isValid = false;
                    }
                });

                if (isValid) {
                    if (btnIncomplete) btnIncomplete.style.display = 'none';
                    if (btnSubmit) btnSubmit.style.display = 'inline-block';
                } else {
                    if (btnIncomplete) btnIncomplete.style.display = 'inline-block';
                    if (btnSubmit) btnSubmit.style.display = 'none';
                }
            }

            window.showFormErrors = function () {
                const requiredInputs = form.querySelectorAll('[required]');
                let firstInvalid = null;

                requiredInputs.forEach(el => {
                    const val = $(el).val();
                    if (!val || val.toString().trim() === '') {
                        $(el).addClass('is-invalid');
                        if ($(el).hasClass('select2')) {
                            $(el).next('.select2-container').find('.select2-selection').addClass('border-danger');
                        }
                        if (!firstInvalid) firstInvalid = el;
                    }
                });

                if (firstInvalid) {
                    toastr.error('Mohon lengkapi semua field yang wajib diisi!');
                    firstInvalid.focus();
                }
            };

            function formatBytes(bytes, decimals = 1) {
                if (bytes === 0) return '0 B';
                const k = 1024, dm = decimals < 0 ? 0 : decimals;
                const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
            }

            // Event listeners for form inputs
            $(form).on('input change', 'input, select, textarea', function () {
                const el = $(this);
                if (el.val() && el.val().toString().trim() !== '') {
                    el.removeClass('is-invalid');
                    if (el.hasClass('select2')) {
                        el.next('.select2-container').find('.select2-selection').removeClass('border-danger');
                    }
                }
                checkFormValidity();
            });

            // Initial check
            setTimeout(checkFormValidity, 500);

        })();
    </script>

    <?php
} ?>