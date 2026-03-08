<?php
include "cfg/konek.php";

$s = []; // Initialize to prevent undefined variable errors
if (isset($_POST['db_year']) && !empty($_POST['db_year'])) {
    $target_db = mysqli_real_escape_string($sqlconn, $_POST['db_year']);
    mysqli_select_db($sqlconn, $target_db);
}

if(isset($_POST['nis'])){
    $nis = $_POST['nis'];
    $sql = mysqli_query($sqlconn, "SELECT * FROM siswa WHERE id='$nis'");
    if ($sql && mysqli_num_rows($sql) > 0) {
        $s = mysqli_fetch_array($sql);
    }
}

// Photo path handling
$foto_path = "images/default.png"; // Default
if(isset($s['photo']) && $s['photo'] != "" && file_exists("file/fotopd/".$s['photo'])){
    $foto_path = "file/fotopd/".$s['photo'];
}
?>
<style>
    .form-control:required:invalid { border-color: #ffcccc; }
    .form-control:required:valid { border-color: #cceeff; }
    .is-invalid { border-color: #e74c3c !important; }
</style>

<form action="?press" method="POST" enctype="multipart/form-data" id="inputpresForm">
    <div class="row">
        <!-- Student Info Column -->
        <div class="col-md-4 text-center">
            <div class="card card-navy card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <img class="profile-user-img img-fluid img-circle" src="<?php echo $foto_path; ?>" alt="User profile picture" style="width: 150px; height: 150px; object-fit: cover;">
                    </div>
                    <h3 class="profile-username text-center mt-3"><?php echo isset($s['pd']) ? $s['pd'] : '-'; ?></h3>
                    <center><b>KELAS</b></center>
                    <p class="text-muted text-center badge bg-menu-gradient"><?php echo isset($s['kelas']) ? $s['kelas'] : '-'; ?></p>
                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <center><b>NIS</b></center>
                            <center><b class="badge bg-menu-gradient"><?php echo isset($s['nis']) ? $s['nis'] : '-'; ?></b></center>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Input Form Column -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <input type="hidden" name="pd" value="<?php echo isset($s['pd']) ? $s['pd'] : ''; ?>">
                    <input type="hidden" name="kelas" value="<?php echo isset($s['kelas']) ? $s['kelas'] : ''; ?>">
                    <input type="hidden" name="db_year" value="<?php echo isset($_POST['db_year']) ? $_POST['db_year'] : (isset($_GET['db_year']) ? $_GET['db_year'] : ''); ?>">
                    
                    <div class="form-group row">
                        <label for="prestasi" class="col-sm-4 col-form-label">Prestasi</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control warna" name="prestasi" placeholder="Contoh: Juara 1 Lomba Web Design" required>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="jenisprestasi" class="col-sm-4 col-form-label">Jenis Prestasi</label>
                        <div class="col-sm-8">
                            <select class="form-control warna" name="jenisprestasi" required>
                                <option value="">- Pilih Jenis -</option>
                                <option value="Akademik">Akademik</option>
                                <option value="Non-Akademik">Non-Akademik</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="tingkat" class="col-sm-4 col-form-label">Tingkat</label>
                        <div class="col-sm-8">
                            <select class="form-control warna" name="tingkat" required>
                                <option value="">- Pilih Tingkat -</option>
                                <option value="Sekolah">Sekolah</option>
                                <option value="Kecamatan">Kecamatan</option>
                                <option value="Kabupaten/Kota">Kabupaten/Kota</option>
                                <option value="Provinsi">Provinsi</option>
                                <option value="Nasional">Nasional</option>
                                <option value="Internasional">Internasional</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="tgl_kegiatan" class="col-sm-4 col-form-label">Tanggal Kegiatan</label>
                        <div class="col-sm-8">
                            <input type="date" class="form-control warna" name="tgl_kegiatan" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="nama_kegiatan" class="col-sm-4 col-form-label">Nama Kegiatan</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control warna" name="nama_kegiatan" placeholder="Isi nama kegiatan" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="penyelenggara" class="col-sm-4 col-form-label">Penyelenggara</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control warna" name="penyelenggara" placeholder="Contoh: Dinas Pendidikan" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="lokasi" class="col-sm-4 col-form-label">Lokasi</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control warna" name="lokasi" placeholder="Isi Lokasi" required>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="juara" class="col-sm-4 col-form-label">Juara Ke-</label>
                        <div class="col-sm-8">
                            <select class="form-control warna" id="juara" name="juara" required>
                                <option value="">- Pilih Juara -</option>
                                <option value="1">Juara 1</option>
                                <option value="2">Juara 2</option>
                                <option value="3">Juara 3</option>
                                <option value="4">Juara 4</option>
                                <option value="Harapan 1">Juara Harapan 1</option>
                                <option value="Harapan 2">Juara Harapan 2</option>
                                <option value="Harapan 3">Juara Harapan 3</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="bulan" class="col-sm-4 col-form-label">Bulan</label>
                        <div class="col-sm-8">
                            <select class="form-control warna" id="bulan" name="bulan" required>
                                <option value="">- Pilih Bulan -</option>
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
                    </div>

                    <div class="form-group row">
                        <label for="file" class="col-sm-4 col-form-label">Upload Lampiran (PDF/Gambar)</label>
                        <div class="col-sm-8">
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
                                        Lampirkan berkas <span class="font-600 text-blck">.pdf / .jpg / .png</span> maksimal <span class="font-600 text-blck">2</span> berkas dan ukuran maksimal <span class="font-600 text-blck">2.0 MB</span>
                                    </div>
                                </div>
                                <input type="file" name="file[]" id="fileInput" class="hidden-file-input" accept=".pdf,.jpg,.jpeg,.png" multiple onchange="handleFileSelect(this)">
                            </div>
                            <small class="text-warning">*Opsional. Upload sertifikat/dokumentasi.</small>
                        </div>
                    </div>

                    <div class="form-group row mt-2" id="preview-area" style="display:none;">
                        <div class="col-sm-3"></div>
                        <div class="col-sm-9">
                            <img id="img-preview" src="" style="max-width: 100%; max-height: 200px; display:none; border: 1px solid #ddd; padding: 5px;">
                            <p id="pdf-preview" style="display:none;"><i class="fa fa-file-pdf"></i> PDF Selected</p>
                        </div>
                    </div>
                </div>

                <div class="card-footer text-right">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" id="btnIncomplete" class="btn btn-secondary disabled" style="cursor: not-allowed;">Lengkapi</button>
                    <button type="submit" id="btnSave" name="save" class="btn btn-success" style="display: none;"><i class="fa fa-save"></i> Simpan</button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
// Completion check function
function validateInputForm() {
    const form = document.getElementById('inputpresForm');
    if (!form) return;
    
    const btnIncomplete = document.getElementById('btnIncomplete');
    const btnSave = document.getElementById('btnSave');
    
    // Get all required inputs
    const requiredInputs = form.querySelectorAll('[required]');
    let isComplete = true;
    
    requiredInputs.forEach(input => {
        if (!input.value.trim()) {
            isComplete = false;
        }
    });

    if (isComplete) {
        if (btnIncomplete) btnIncomplete.style.display = 'none';
        if (btnSave) btnSave.style.display = 'inline-block';
    } else {
        if (btnIncomplete) btnIncomplete.style.display = 'inline-block';
        if (btnSave) btnSave.style.display = 'none';
    }
}

$(document).ready(function() {
    // Listen for changes in all inputs/selects
    $('#inputpresForm').on('input change', 'input, select, textarea', function() {
        validateInputForm();
    });
    
    // Initial check
    validateInputForm();
});
// Helper for file sizes
function formatBytes(bytes, decimals = 1) {
    if (bytes === 0) return '0 B';
    const k = 1024, dm = decimals < 0 ? 0 : decimals, units = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + units[i];
}

// Global variable for selected files
window.selectedFiles = [];

function handleFileSelect(input) {
    const newFiles = Array.from(input.files);
    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
    const validFiles = newFiles.filter(f => allowedTypes.includes(f.type));
    
    if (newFiles.length > 0 && validFiles.length === 0) {
        if (typeof toastr !== 'undefined') toastr.error('Hanya berkas PDF atau Gambar (JPG/PNG) yang diperbolehkan!');
    }

    // Add new valid files to the existing selection (additive)
    validFiles.forEach(file => {
        if (window.selectedFiles.length < 2) {
            // Avoid duplicates
            if (!window.selectedFiles.some(f => f.name === file.name && f.size === file.size)) {
                window.selectedFiles.push(file);
            }
        }
    });
    
    if (window.selectedFiles.length > 2) {
        if (typeof toastr !== 'undefined') toastr.warning('Maksimal 2 berkas diperbolehkan.');
    }
    
    updateInputFiles();
    renderFileList();
}

function renderFileList() {
    const listContainer = document.getElementById('file-list-display');
    const totalDisplay = document.getElementById('total-size-display');
    if (!listContainer) return;
    
    listContainer.innerHTML = '';
    let totalSize = 0;
    
    if (window.selectedFiles.length > 0) {
        listContainer.classList.remove('d-none');
    } else {
        listContainer.classList.add('d-none');
    }

    window.selectedFiles.forEach((file, index) => {
        totalSize += file.size;
        
        if (file.size > 2 * 1024 * 1024) {
            if (typeof toastr !== 'undefined') toastr.error('Ukuran berkas ' + file.name + ' melebihi 2MB!');
            window.selectedFiles.splice(index, 1);
            updateInputFiles();
            renderFileList();
            return;
        }

        const isImage = file.type.startsWith('image/');
        const iconClass = isImage ? 'fa-file-image text-success' : 'fa-file-pdf text-info';
        
        const item = document.createElement('div');
        item.className = 'file-item-new';
        item.id = `file-item-${index}`;
        item.innerHTML = `
            <div class="file-info-new">
                <i class="fa ${iconClass} fa-2x"></i>
                <div class="file-details-new">
                    <div class="file-name-new">${file.name}</div>
                    <div class="file-size-new">Ukuran Berkas: ${formatBytes(file.size)}</div>
                    <div class="upload-status" id="status-${index}">
                        <div class="fs-nano mt-1" style="color: #27ae60;"><i class="fa fa-circle-notch fa-spin mr-1"></i> Sedang disiapkan...</div>
                    </div>
                </div>
            </div>
            <div class="delete-btn-new ml-auto" onclick="removeFile(${index})" title="Hapus File">
                <i class="fa fa-trash-alt"></i>
            </div>
        `;
        listContainer.appendChild(item);

        // Simulate upload delay for UI polish
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

    if (totalDisplay) totalDisplay.textContent = formatBytes(totalSize);
}

function removeFile(index) {
    window.selectedFiles.splice(index, 1);
    updateInputFiles();
    renderFileList();
}

function updateInputFiles() {
    const input = document.getElementById('fileInput');
    if (!input) return;
    const dataTransfer = new DataTransfer();
    window.selectedFiles.forEach(file => dataTransfer.items.add(file));
    input.files = dataTransfer.files;
}

// Drag & Drop Handling
$(document).ready(function() {
    var dropArea = $('#drop-area');
    if (dropArea.length) {
        dropArea.on('dragenter dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropArea.addClass('dragover');
        });

        dropArea.on('dragleave drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropArea.removeClass('dragover');
        });

        dropArea.on('drop', function(e) {
            var files = e.originalEvent.dataTransfer.files;
            if(files.length > 0) {
                handleFileSelect({ files: files });
            }
        });
    }
});
</script>
