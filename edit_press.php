<?php
include "cfg/konek.php";
include "cfg/secure.php";

// Handle AJAX file deletion
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
                // Physical file deletion
                if (!empty($f) && file_exists("file/prestasi/" . $f)) {
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

if (isset($_REQUEST['urut'])) {
    $id = mysqli_real_escape_string($sqlconn, $_REQUEST['urut']);
    // Mengambil data berdasarkan id
    // dan menampilkan data ke dalam form modal bootstrap
    $rql = mysqli_query($sqlconn, "SELECT * FROM prestasi WHERE id = '$id'");
    $r = mysqli_fetch_array($rql);
    $ting = $r['tingkat'];

    // Ambil photo dari tabel siswa berdasarkan nama (pd) dan kelas
    $pd_name = mysqli_real_escape_string($sqlconn, $r['pd']);
    $pd_kelas = mysqli_real_escape_string($sqlconn, $r['kelas']);
    $sql_pd = mysqli_query($sqlconn, "SELECT photo FROM siswa WHERE pd = '$pd_name' AND kelas = '$pd_kelas'");
    $d_pd = mysqli_fetch_array($sql_pd);

    $photo_src = "images/male.png"; // Default fallback
    if ($d_pd && !empty($d_pd['photo']) && file_exists("file/fotopd/" . $d_pd['photo'])) {
        $photo_src = "file/fotopd/" . $d_pd['photo'];
    }
?>
<style>
    .form-control:required:invalid { border-color: #ffcccc; }
    .form-control:required:valid { border-color: #cceeff; }
    .is-invalid { border-color: #e74c3c !important; }
</style>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
        <input type="hidden" name="db_year" value="<?php echo isset($_REQUEST['db_year']) ? $_REQUEST['db_year'] : ''; ?>">

        <!-- Section 0: Student Identity with Photo -->
        <div class="row mb-3">
            <div class="col-md-12 text-center">
                <img class="profile-user-img img-fluid img-circle" src="<?php echo $photo_src; ?>" alt="User profile picture" style="width: 100px; height: 100px; object-fit: cover; border: 3px solid #007bff;">
            </div>
        </div>

        <!-- Section 1: Data Siswa -->
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label><i class="fa fa-user"></i> Nama Siswa</label>
                    <input class="form-control form-control-sm warna" name="pd" id="pd" value="<?php echo $r['pd']; ?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label><i class="fa fa-users"></i> Kelas</label>
                    <input class="form-control form-control-sm warna" name="kelas" id="kelas" value="<?php echo $r['kelas']; ?>" required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label><i class="fa fa-award"></i> Nama Prestasi</label>
                    <input class="form-control form-control-sm warna" name="prestasi" value="<?php echo $r['prestasi']; ?>" placeholder="Contoh: Juara 1 Lomba Web Design" required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-5">
                <div class="form-group">
                    <label><i class="fa fa-list-alt"></i> Jenis Prestasi</label>
                    <select class="form-control form-control-sm warna" name="jenisprestasi" required>
                        <option value="Akademik" <?php echo ($r['jenisprestasi'] == 'Akademik') ? 'selected' : ''; ?>>Akademik</option>
                        <option value="Non-Akademik" <?php echo ($r['jenisprestasi'] == 'Non-Akademik') ? 'selected' : ''; ?>>Non-Akademik</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label><i class="fa fa-trophy"></i> Juara</label>
                    <select class="form-control form-control-sm warna" name="juara" required>
                        <?php
                        $juaras = ['1', '2', '3', '4', 'Harapan 1', 'Harapan 2', 'Harapan 3'];
                        foreach ($juaras as $j) {
                            $selected = ($r['juara'] == $j) ? 'selected' : '';
                            $display = (is_numeric($j)) ? "Juara $j" : "Juara $j";
                            echo "<option value='$j' $selected>$display</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label><i class="fa fa-layer-group"></i> Tingkat</label>
                    <select class="form-control form-control-sm -edit warna" name="tingkat" style="width: 100%;" required>
                        <?php
                        $levels = ['Internasional', 'Nasional', 'Provinsi', 'Kabupaten/Kota'];
                        foreach ($levels as $level) {
                            $selected = ($ting == $level) ? 'selected' : '';
                            echo "<option value='$level' $selected>$level</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                 <div class="form-group">
                    <label><i class="fa fa-calendar-day"></i> Tanggal</label>
                    <input type="date" name="tgl_kegiatan" value="<?php echo $r['tgl_kegiatan']; ?>" class="form-control warna" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label><i class="fa fa-calendar-alt"></i> Bulan</label>
                    <select class="form-control warna" name="bulan" required>
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
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label><i class="fa fa-map-marker-alt"></i> Lokasi</label>
                    <input type="text" name="lokasi" value="<?php echo $r['lokasi']; ?>" class="form-control warna" required>
                </div>
            </div>
        </div>
        
         <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label><i class="fa fa-file"></i> Lampiran</label>
                <div class="file-upload-wrapper" id="drop-area-edit-<?php echo $id; ?>">
                    <div class="file-upload-selector border rounded-top">
                        <div class="upload-area d-flex align-items-center">
                            <div style="flex-shrink: 0;">
                                <button type="button" class="btn btn-sm btn-light-info" onclick="document.getElementById('fileInputEdit-<?php echo $id; ?>').click()">Pilih File...</button>
                            </div>
                            <div class="dropzone text-center prevent-select">
                                <span class="upload-text"><i class="fa fa-cloud-upload-alt mr-1"></i> atau drag & drop berkas disini.</span>
                            </div>
                        </div>
                    </div>
                    
                    <div id="unified-file-list-<?php echo $id; ?>" class="file-list-uploaded <?php echo (empty($r['pdf'])) ? 'd-none' : ''; ?> text-left">
                        <!-- Existing Files Container -->
                        <div id="file-list-display-edit-<?php echo $id; ?>">
                            <?php 
                            $existing_files = explode(',', $r['pdf']);
                            $initial_total_size = 0;
                            
                            if (!function_exists('formatBytesPHP')) {
                                function formatBytesPHP($bytes, $precision = 1) {
                                    if ($bytes === 0) return '0 B';
                                    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
                                    $bytes = max($bytes, 0); 
                                    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
                                    $pow = min($pow, count($units) - 1); 
                                    $bytes /= pow(1024, $pow);
                                    return round($bytes, $precision) . ' ' . $units[$pow]; 
                                }
                            }

                            foreach($existing_files as $idx => $f) {
                                if(!empty($f)) {
                                    $file_path = 'file/prestasi/' . $f;
                                    $size_bytes = file_exists($file_path) ? filesize($file_path) : 0;
                                    $initial_total_size += $size_bytes;
                                    
                                    $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                                    $icon = (in_array($ext, ['jpg', 'jpeg', 'png'])) ? 'fa-file-image text-success' : 'fa-file-pdf text-info';
                                    
                                    echo "
                                    <div class='file-item-new existing-file-item border-bottom' id='existing-file-{$id}-{$idx}' data-size='$size_bytes'>
                                        <div class='file-info-new'>
                                            <i class='fa $icon fa-2x'></i>
                                            <div class='file-details-new'>
                                                <div class='file-name-new'><a href='file/prestasi/$f' target='_blank' class='text-info text-decoration-none'>$f <i class='fa fa-external-link-alt ml-1 fs-nano'></i></a></div>
                                                <div class='file-size-new'>Ukuran: " . formatBytesPHP($size_bytes) . " (Server)</div>
                                            </div>
                                        </div>
                                        <div class='delete-btn-new ml-auto' onclick='deleteFilePrestasi(\"$id\", \"$f\", \"$idx\")' title='Hapus Lampiran'><i class='fa fa-trash-alt'></i></div>
                                    </div>";
                                }
                            }
                            ?>
                        </div>

                        <!-- New Files Container (Injected via JS) -->
                        <div id="new-file-list-display-edit-<?php echo $id; ?>" class="d-none">
                            <!-- New file items injected here -->
                        </div>
                    </div>

                    <div class="uploader-footer border rounded-bottom p-2 border-top-0">
                        <div class="fs-xs"><span class="font-600 text-blck">Total : <span id="total-size-display-edit-<?php echo $id; ?>"><?php echo formatBytesPHP($initial_total_size); ?></span></span></div>
                        <div class="fs-nano text-muted">Lampirkan berkas <span class="font-600 text-blck">.pdf / .jpg / .png</span> maksimal <span class="font-600 text-blck">2</span> berkas dan ukuran maksimal <span class="font-600 text-blck">2.0 MB</span></div>
                    </div>
                    <input type="file" name="file[]" id="fileInputEdit-<?php echo $id; ?>" class="hidden-file-input" accept=".pdf,.jpg,.jpeg,.png" multiple onchange="handleFileSelectEdit(this, '<?php echo $id; ?>')">
                </div>
                <small class="text-warning">*Opsional. Upload sertifikat/dokumentasi baru.</small>
                </div>
            </div>
        </div>
        <script>
        if (typeof window.formatBytes === 'undefined') {
            window.formatBytes = function(bytes, decimals = 1) {
                if (bytes === 0) return '0 B';
                const k = 1024;
                const dm = decimals < 0 ? 0 : decimals;
                const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
            }
        }

        window.deleteFilePrestasi = function(id, fileName, idx) {
            if (confirm('Apakah Anda yakin ingin menghapus lampiran ini?')) {
                $.ajax({
                    url: 'edit_press.php', // Changed from prestasifix.php to be self-contained
                    type: 'POST',
                    data: {
                        aksi: 'hapus_file',
                        id: id,
                        file: fileName
                    },
                    success: function(response) {
                        if (response.trim() === 'success') {
                            $('#existing-file-' + id + '-' + idx).fadeOut(300, function() {
                                $(this).remove();
                                // Update total if no new file is selected
                                if (!(window.selectedFilesEdit[id] && window.selectedFilesEdit[id].length > 0)) {
                                    updateInitialTotalDisplay(id);
                                }
                            });
                            toastr.success('Lampiran berhasil dihapus');
                        } else {
                            toastr.error('Gagal menghapus lampiran: ' + response);
                        }
                    },
                    error: function() {
                        toastr.error('Terjadi kesalahan server saat menghapus lampiran');
                    }
                });
            }
        };

        function updateInitialTotalDisplay(id) {
            let total = 0;
            $('#file-list-display-edit-' + id + ' .existing-file-item').each(function() {
                total += parseInt($(this).attr('data-size') || 0);
            });
            
            if (!(window.selectedFilesEdit[id] && window.selectedFilesEdit[id].length > 0)) {
                $('#total-size-display-edit-' + id).text(window.formatBytes(total));
            }
        }

        (function() {
            var dropArea = document.getElementById('drop-area-edit-<?php echo $id; ?>');
            var fileInput = document.getElementById('fileInputEdit-<?php echo $id; ?>');

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
                  if(files.length > 0) {
                      const dataTransfer = new DataTransfer();
                      Array.from(fileInput.files).forEach(file => dataTransfer.items.add(file));
                      Array.from(files).forEach(file => dataTransfer.items.add(file));
                      fileInput.files = dataTransfer.files;
                      handleFileSelectEdit(fileInput, '<?php echo $id; ?>');
                  }
                }
            }
        })();

        if (typeof window.selectedFilesEdit === 'undefined') { window.selectedFilesEdit = {}; }

        function handleFileSelectEdit(input, id) {
            const newFiles = Array.from(input.files);
            const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
            const validFiles = newFiles.filter(f => allowedTypes.includes(f.type));
            
            if (newFiles.length > 0 && validFiles.length === 0) {
                toastr.error('Hanya berkas PDF atau Gambar (JPG/PNG) yang diperbolehkan!');
            }
            
            if (!window.selectedFilesEdit[id]) window.selectedFilesEdit[id] = [];
            
            // Add new valid files to existing selection (additive)
            validFiles.forEach(file => {
                if (window.selectedFilesEdit[id].length < 2) {
                    if (!window.selectedFilesEdit[id].some(f => f.name === file.name && f.size === file.size)) {
                        window.selectedFilesEdit[id].push(file);
                    }
                }
            });

            if (window.selectedFilesEdit[id].length > 2) {
                toastr.warning('Maksimal 2 berkas diperbolehkan.');
            }

            updateInputFilesEdit(id);
            renderFileListEdit(id);
        }

        function renderFileListEdit(id) {
            const listContainer = document.getElementById('new-file-list-display-edit-' + id);
            const parentContainer = document.getElementById('unified-file-list-' + id);
            const totalDisplay = document.getElementById('total-size-display-edit-' + id);
            if (!listContainer) return;
            
            let files = window.selectedFilesEdit[id] || [];
            const maxSizeTotal = 2 * 1024 * 1024; // 2MB

            listContainer.innerHTML = '';
            let totalSize = 0;
            
            // Re-calculate total size including existing files
            let existingSize = 0;
            $('#file-list-display-edit-' + id + ' .existing-file-item').each(function() {
                existingSize += parseInt($(this).attr('data-size') || 0);
            });

            if (files.length > 0) { 
                listContainer.classList.remove('d-none');
                if (parentContainer) parentContainer.classList.remove('d-none');
            } else { 
                listContainer.classList.add('d-none');
                // Hide container only if NO existing files and NO new files
                if (parentContainer && $('#file-list-display-edit-' + id + ' .existing-file-item').length === 0) {
                    parentContainer.classList.add('d-none');
                }
            }

            files.forEach((file, index) => {
                totalSize += file.size;
                const isImage = file.type.startsWith('image/');
                const iconClass = isImage ? 'fa-file-image text-success' : 'fa-file-pdf text-info';
                
                const item = document.createElement('div');
                item.className = 'file-item-new';
                item.innerHTML = `
                    <div class="file-info-new">
                        <i class="fa ${iconClass} fa-2x"></i>
                        <div class="file-details-new">
                            <div class="file-name-new">${file.name}</div>
                            <div class="file-size-new">Ukuran Berkas: ${window.formatBytes(file.size)}</div>
                            <div class="upload-status" id="status-edit-${id}-${index}">
                                <div class="fs-nano mt-1" style="color: #27ae60;"><i class="fa fa-circle-notch fa-spin mr-1"></i> Sedang disiapkan...</div>
                            </div>
                        </div>
                    </div>
                    <div class="delete-btn-new ml-auto" onclick="removeFileEdit(${index}, '${id}')">
                        <i class="fa fa-trash-alt"></i>
                    </div>
                `;
                listContainer.appendChild(item);

                setTimeout(() => {
                    const statusEl = document.getElementById(`status-edit-${id}-${index}`);
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
                window.selectedFilesEdit[id] = [];
                updateInputFilesEdit(id);
                renderFileListEdit(id);
                return;
            }

            if (totalSize > 0) {
                if (totalDisplay) totalDisplay.textContent = window.formatBytes(totalSize);
            } else {
                updateInitialTotalDisplay(id);
            }
        }

        window.removeFileEdit = function(index, id) {
            window.selectedFilesEdit[id].splice(index, 1);
            updateInputFilesEdit(id);
            renderFileListEdit(id);
        };

        function updateInputFilesEdit(id) {
            const input = document.getElementById('fileInputEdit-' + id);
            if (!input) return;
            const dataTransfer = new DataTransfer();
            (window.selectedFilesEdit[id] || []).forEach(file => dataTransfer.items.add(file));
            input.files = dataTransfer.files;
        }
        </script>

        <div class="modal-footer d-flex justify-content-between">
            <button type="button" class="btn bg-gradient-danger custom" data-dismiss="modal">Batal</button>
            <button type="button" id="btnIncompleteEdit" class="btn bg-gradient-secondary custom" style="cursor: not-allowed;">Belum Lengkap</button>
            <button type="submit" id="btnSaveEdit" name="update2" class="btn bg-gradient-primary custom" style="display: none;">Update</button>
        </div>
        
        <script>
        function checkFormCompletionEdit() {
            const form = document.querySelector('#myEdit form');
            const btnIncomplete = document.getElementById('btnIncompleteEdit');
            const btnSave = document.getElementById('btnSaveEdit');
            
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

        $(document).ready(function() {
            $('#myEdit form').on('input change', 'input, select, textarea', function() {
                checkFormCompletionEdit();
            });
            checkFormCompletionEdit();
        });
        </script>
    </form>
<?php } ?>