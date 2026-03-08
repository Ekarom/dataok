<?php
include "cfg/konek.php";
include "cfg/secure.php";

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
    
    if (!$r) {
        echo "<div class='alert alert-danger'>Data tidak ditemukan.</div>";
        exit;
    }

    $ting = $r['tingkat'];
    $pd_name = mysqli_real_escape_string($sqlconn, $r['pd']);
    $pd_kelas = mysqli_real_escape_string($sqlconn, $r['kelas']);
    
    $sql_pd = mysqli_query($sqlconn, "SELECT photo FROM siswa WHERE pd = '$pd_name' AND kelas = '$pd_kelas'");
    $d_pd = mysqli_fetch_array($sql_pd);

    $photo_src = "images/male.png"; 
    if ($d_pd && !empty($d_pd['photo']) && file_exists("file/fotopd/" . $d_pd['photo'])) {
        $photo_src = "file/fotopd/" . $d_pd['photo'];
    }

    // Helper function for formatting bytes
    if (!function_exists('formatBytesPHP')) {
        function formatBytesPHP($bytes, $precision = 1) {
            if ($bytes <= 0) return '0 B';
            $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
            $pow = floor(log($bytes, 1024)); 
            $pow = min($pow, count($units) - 1); 
            $bytes /= pow(1024, $pow);
            return round($bytes, $precision) . ' ' . $units[$pow]; 
        }
    }
?>

<!-- Modern CSS Styles -->
<style>
    :root {
        --primary: #4e73df;
        --secondary: #858796;
        --success: #1cc88a;
        --info: #36b9cc;
        --warning: #f6c23e;
        --danger: #e74a3b;
        --light: #f8f9fc;
        --dark: #5a5c69;
    }

    .edit-form-container {
        padding: 10px;
    }

    .form-section-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 15px;
        padding-bottom: 5px;
        border-bottom: 2px solid #eaecf4;
        display: flex;
        align-items: center;
    }

    .form-section-title i {
        margin-right: 8px;
    }

    .form-group label {
        font-weight: 600;
        font-size: 0.8rem;
        color: #4e5e6a;
        margin-bottom: 5px;
    }

    .form-control-modern {
        border-radius: 8px;
        border: 1px solid #d1d3e2;
        padding: 0.5rem 0.75rem;
        font-size: 0.85rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-control-modern:focus {
        border-color: #bac8f3;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }

    .form-control-modern:required:invalid { border-left: 3px solid var(--danger); }
    .form-control-modern:required:valid { border-left: 3px solid var(--success); }

    /* File Upload Styling */
    .file-upload-wrapper-modern {
        border: 2px dashed #d1d3e2;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        background: #fbfbfc;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .file-upload-wrapper-modern.dragover {
        background: #f0f3ff;
        border-color: var(--primary);
    }

    .upload-icon-modern {
        font-size: 2rem;
        color: var(--primary);
        margin-bottom: 10px;
        display: block;
    }

    .file-item-modern {
        display: flex;
        align-items: center;
        padding: 10px;
        background: #fff;
        border: 1px solid #eaecf4;
        border-radius: 8px;
        margin-top: 10px;
    }

    .file-icon-modern {
        font-size: 1.5rem;
        margin-right: 12px;
    }

    .file-details-modern {
        flex-grow: 1;
        overflow: hidden;
    }

    .file-name-modern {
        font-weight: 600;
        font-size: 0.85rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 2px;
    }

    .file-size-modern {
        font-size: 0.75rem;
        color: var(--secondary);
    }

    .btn-delete-file {
        color: var(--danger);
        padding: 5px 10px;
        cursor: pointer;
        opacity: 0.7;
        transition: opacity 0.2s;
    }

    .btn-delete-file:hover {
        opacity: 1;
    }

    .modal-footer-custom {
        border-top: 1px solid #eaecf4;
        padding-top: 15px;
        margin-top: 20px;
    }
</style>

<div class="edit-form-container">
    <form method="post" enctype="multipart/form-data" id="formEditPrestasi">
        <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
        <input type="hidden" name="db_year" value="<?php echo isset($_REQUEST['db_year']) ? $_REQUEST['db_year'] : ''; ?>">

        <!-- Section 1: Identitas Siswa -->
        <div class="form-section-title">
            <i class="fa fa-id-card"></i> Identitas Siswa
        </div>
        <div class="row">
            <div class="col-md-7">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input class="form-control form-control-modern" name="pd" id="pd" value="<?php echo $r['pd']; ?>" required readonly>
                </div>
                <div class="form-group">
                    <label>Kelas</label>
                    <input class="form-control form-control-modern" name="kelas" id="kelas" value="<?php echo $r['kelas']; ?>" required readonly>
                </div>
            </div>
            <div class="col-md-5 text-center">
                <div class="p-2 border rounded bg-light" style="display:inline-block;">
                    <img src="<?php echo $photo_src; ?>" class="img-fluid rounded" style="max-height: 120px; border: 2px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1);" alt="Photo">
                </div>
            </div>
        </div>

        <!-- Section 2: Data Prestasi -->
        <div class="form-section-title mt-4">
            <i class="fa fa-trophy"></i> Detail Prestasi
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>Nama Prestasi / Judul Kegiatan</label>
                    <input class="form-control form-control-modern" name="prestasi" value="<?php echo $r['prestasi']; ?>" placeholder="Contoh: Juara 1 Lomba Web Design" required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Jenis Prestasi</label>
                    <select class="form-control form-control-modern" name="jenisprestasi" required>
                        <option value="Akademik" <?php echo ($r['jenisprestasi'] == 'Akademik') ? 'selected' : ''; ?>>Akademik</option>
                        <option value="Non-Akademik" <?php echo ($r['jenisprestasi'] == 'Non-Akademik') ? 'selected' : ''; ?>>Non-Akademik</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Juara</label>
                    <select class="form-control form-control-modern" name="juara" required>
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
            <div class="col-md-3">
                <div class="form-group">
                    <label>Tingkat</label>
                    <select class="form-control form-control-modern" name="tingkat" required>
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
            <div class="col-md-12">
                <div class="form-group">
                    <label>Nama Kegiatan</label>
                    <input type="text" name="nama_kegiatan" value="<?php echo $r['nama_kegiatan']; ?>" class="form-control form-control-modern" placeholder="Nama instansi atau event..." required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                 <div class="form-group">
                    <label>Tanggal Pelaksanaan</label>
                    <input type="date" name="tgl_kegiatan" value="<?php echo $r['tgl_kegiatan']; ?>" class="form-control form-control-modern" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Bulan (Rekap)</label>
                    <select class="form-control form-control-modern" name="bulan" required>
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
            <div class="col-md-6">
                <div class="form-group">
                    <label>Penyelenggara</label>
                    <input type="text" name="penyelenggara" value="<?php echo $r['penyelenggara']; ?>" class="form-control form-control-modern" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Lokasi</label>
                    <input type="text" name="lokasi" value="<?php echo $r['lokasi']; ?>" class="form-control form-control-modern" required>
                </div>
            </div>
        </div>
        
        <!-- Section 3: Lampiran -->
        <div class="form-section-title mt-4">
            <i class="fa fa-paperclip"></i> Lampiran Berkas
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="file-upload-wrapper-modern" id="dropAreaEdit_<?php echo $id; ?>" onclick="document.getElementById('fileInputEdit_<?php echo $id; ?>').click()">
                    <i class="fa fa-cloud-upload-alt upload-icon-modern"></i>
                    <div class="font-weight-bold">Klik atau seret file ke sini</div>
                    <div class="small text-muted mb-2">Hanya berkas PDF, JPG, atau PNG (Maks 2MB)</div>
                    <input type="file" name="file[]" id="fileInputEdit_<?php echo $id; ?>" class="d-none" accept=".pdf,.jpg,.jpeg,.png" multiple onchange="handleFileSelectEdit(this, '<?php echo $id; ?>')">
                </div>

                <div id="fileListContainer_<?php echo $id; ?>" class="mt-2">
                    <!-- Existing Files -->
                    <div id="existingFileList_<?php echo $id; ?>">
                        <?php 
                        $existing_files = explode(',', $r['pdf']);
                        $total_size_bytes = 0;

                        foreach($existing_files as $idx => $f) {
                            if(!empty($f)) {
                                $file_path = 'file/prestasi/' . $f;
                                $size = file_exists($file_path) ? filesize($file_path) : 0;
                                $total_size_bytes += $size;
                                
                                $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                                $icon = (in_array($ext, ['jpg', 'jpeg', 'png'])) ? 'fa-file-image text-success' : 'fa-file-pdf text-info';
                                
                                echo "
                                <div class='file-item-modern existing-file-item' id='item_ext_{$id}_{$idx}' data-size='$size'>
                                    <div class='file-icon-modern'><i class='fa $icon'></i></div>
                                    <div class='file-details-modern'>
                                        <div class='file-name-modern'><a href='file/prestasi/$f' target='_blank'>$f</a></div>
                                        <div class='file-size-modern'>Tersimpan di server (" . formatBytesPHP($size) . ")</div>
                                    </div>
                                    <div class='btn-delete-file' onclick='deleteExistingFile(\"{$id}\", \"{$f}\", \"{$idx}\", event)' title='Hapus Lampiran'><i class='fa fa-trash-alt'></i></div>
                                </div>";
                            }
                        }
                        ?>
                    </div>

                    <!-- New Selected Files -->
                    <div id="newFileList_<?php echo $id; ?>"></div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mt-2 px-1">
                    <span class="small font-weight-bold">Total Ukuran: <span id="totalSizeLabel_<?php echo $id; ?>"><?php echo formatBytesPHP($total_size_bytes); ?></span></span>
                    <span class="small text-warning italic">*Opsional jika ingin mengganti lampiran</span>
                </div>
            </div>
        </div>

        <div class="row modal-footer-custom px-2">
            <div class="col-6">
                <button type="button" class="btn btn-secondary btn-block rounded-pill" data-dismiss="modal">Batal</button>
            </div>
            <div class="col-6">
                <button type="button" id="btnWait_<?php echo $id; ?>" class="btn btn-light btn-block rounded-pill disabled" style="cursor:not-allowed;">Lengkapi Form</button>
                <button type="submit" name="update2" id="btnSubmit_<?php echo $id; ?>" class="btn btn-primary btn-block rounded-pill" style="display:none;">Simpan Perubahan</button>
            </div>
        </div>
    </form>
</div>

<script>
/**
 * JavaScript Module for Edit Prestasi
 */
(function() {
    const ID = '<?php echo $id; ?>';
    const MAX_SIZE = 2 * 1024 * 1024; // 2MB
    let selectedFiles = [];

    // Initialize UI
    const form = document.getElementById('formEditPrestasi');
    const inputFiles = document.getElementById('fileInputEdit_<?php echo $id; ?>');
    const dropArea = document.getElementById('dropAreaEdit_<?php echo $id; ?>');
    const btnSubmit = document.getElementById('btnSubmit_<?php echo $id; ?>');
    const btnWait = document.getElementById('btnWait_<?php echo $id; ?>');

    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, e => { e.preventDefault(); e.stopPropagation(); }, false);
    });

    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, () => dropArea.classList.add('dragover'), false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, () => dropArea.classList.remove('dragover'), false);
    });

    dropArea.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        handleFiles(dt.files);
    }, false);

    window.handleFileSelectEdit = function(input) {
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
        container.innerHTML = '';
        let totalSize = 0;

        // Calculate size from existing files still in view
        $(`#existingFileList_${ID} .existing-file-item`).each(function() {
            totalSize += parseInt($(this).attr('data-size') || 0);
        });

        selectedFiles.forEach((file, index) => {
            totalSize += file.size;
            const isImage = file.type.startsWith('image/');
            const icon = isImage ? 'fa-file-image text-success' : 'fa-file-pdf text-info';
            
            const div = document.createElement('div');
            div.className = 'file-item-modern';
            div.innerHTML = `
                <div class="file-icon-modern"><i class="fa ${icon}"></i></div>
                <div class="file-details-modern">
                    <div class="file-name-modern">${file.name}</div>
                    <div class="file-size-modern">Baru • ${formatBytes(file.size)}</div>
                </div>
                <div class="btn-delete-file" onclick="removeSelectedFile(${index})"><i class="fa fa-trash-alt"></i></div>
            `;
            container.appendChild(div);
        });

        const totalSizeLabel = document.getElementById(`totalSizeLabel_${ID}`);
        if (totalSizeLabel) totalSizeLabel.innerText = formatBytes(totalSize);

        if (totalSize > MAX_SIZE) {
            toastr.error('Total ukuran berkas melebihi 2MB!');
            btnSubmit.classList.add('disabled');
            btnSubmit.style.pointerEvents = 'none';
        } else {
            btnSubmit.classList.remove('disabled');
            btnSubmit.style.pointerEvents = 'auto';
            checkFormValidity();
        }
    }

    window.removeSelectedFile = function(index) {
        selectedFiles.splice(index, 1);
        updateInputFiles();
        renderFileList();
    };

    function updateInputFiles() {
        const dt = new DataTransfer();
        selectedFiles.forEach(file => dt.items.add(file));
        inputFiles.files = dt.files;
    }

    window.deleteExistingFile = function(id, fileName, idx, event) {
        event.stopPropagation();
        if (!confirm('Hapus lampiran ini dari database?')) return;

        $.ajax({
            url: 'edit_press.php',
            type: 'POST',
            data: { aksi: 'hapus_file', id: id, file: fileName },
            success: function(res) {
                if (res.trim() === 'success') {
                    $(`#item_ext_${id}_${idx}`).fadeOut(300, function() {
                        $(this).remove();
                        renderFileList(); // Recalculate total size
                    });
                    toastr.success('Lampiran dihapus');
                } else {
                    toastr.error('Gagal hapus: ' + res);
                }
            },
            error: () => toastr.error('Server error')
        });
    };

    function checkFormValidity() {
        let isValid = true;
        const required = form.querySelectorAll('[required]');
        required.forEach(el => {
            if (!el.value.trim()) isValid = false;
        });

        if (isValid) {
            btnWait.style.display = 'none';
            btnSubmit.style.display = 'block';
        } else {
            btnWait.style.display = 'block';
            btnSubmit.style.display = 'none';
        }
    }

    function formatBytes(bytes, decimals = 1) {
        if (bytes === 0) return '0 B';
        const k = 1024, dm = decimals < 0 ? 0 : decimals;
        const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }

    // Event listeners for form inputs
    form.addEventListener('input', checkFormValidity);
    form.addEventListener('change', checkFormValidity);
    
    // Initial check
    setTimeout(checkFormValidity, 500);

})();
</script>

<?php } ?>
