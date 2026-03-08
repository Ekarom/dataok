<?php
include "cfg/konek.php";
include "cfg/secure.php";

if(isset($_REQUEST['urut'])) {
    $id = mysqli_real_escape_string($sqlconn, $_REQUEST['urut']);
    $rql = mysqli_query($sqlconn,"SELECT * FROM legalisir WHERE id = '$id'");
    $r = mysqli_fetch_array($rql);
?>

<style>
    /* File Uploader Premium UI Styles */
    .file-upload-wrapper { width: 100%; margin-bottom: 20px; position: relative; }
    .file-upload-selector { background: #f8f9fa; display: flex; align-items: center; border: 1px solid #ced4da; border-radius: 4px 4px 0 0; cursor: pointer; transition: all 0.3s ease; }
    .file-upload-selector:hover { background-color: #f0f4f8; }
    .upload-area { display: flex; align-items: center; width: 100%; padding: 8px 12px; }
    .btn-light-info { background-color: #e3f2fd; color: #0288d1; border: 1px solid #b3e5fc; font-weight: 600; font-size: 13px; padding: 5px 12px; white-space: nowrap; border-radius: 4px; transition: all 0.2s; }
    .btn-light-info:hover { background-color: #b3e5fc; color: #01579b; }
    .dropzone { flex-grow: 1; text-align: center; color: #6c757d; font-size: 13px; border-left: 1px solid #dee2e6; margin-left: 10px; padding-left: 10px; cursor: pointer; }
    
    .file-list-uploaded { border: 1px solid #ced4da; border-top: none; background: #fff; max-height: 250px; overflow-y: auto; }
    .file-item-new { display: flex; align-items: center; padding: 10px 15px; border-bottom: 1px solid #f1f1f1; transition: background 0.2s; }
    .file-item-new:hover { background-color: #fcfcfc; }
    .file-info-new { display: flex; align-items: center; flex-grow: 1; min-width: 0; }
    .file-details-new { margin-left: 12px; overflow: hidden; text-align: left; }
    .file-name-new { font-size: 13px; font-weight: 600; color: #333; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block; }
    .file-size-new { font-size: 11px; color: #888; display: block; margin-top: 2px; }
    
    .delete-btn-new { color: #dc3545; cursor: pointer; font-size: 16px; padding: 5px; margin-left: 10px; transition: all 0.2s; }
    .delete-btn-new:hover { color: #bd2130; transform: scale(1.1); }
    
    .uploader-footer { padding: 8px 12px; background: #f8f9fa; border: 1px solid #ced4da; border-top: none; border-radius: 0 0 4px 4px; font-size: 12px; }
    .font-600 { font-weight: 600; }
    .text-blck { color: #333; }
    .hidden-file-input { display: none !important; }
    .file-upload-selector.dragover { background-color: #e8f0fe; border-color: #4285f4; }
    .fs-nano { font-size: 11px; }
    
    /* Animation for upload status */
    .upload-status-active { color: #27ae60; font-size: 11px; font-weight: 500; margin-top: 2px; }
</style>

<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo $r['id']; ?>">

    <div class="modal-body">
        <!-- Section 1: No Surat & Tanggal -->
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label><i class="fa fa-file-alt"></i> No. Surat</label>
                    <input type="text" name="no_surat" value="<?php echo $r['no_surat']; ?>" class="form-control form-control-sm warna" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label><i class="fa fa-calendar-alt"></i> Tanggal Surat</label>
                    <input type="date" name="tgl_dokumen" value="<?php echo $r['tgl_dokumen']; ?>" class="form-control form-control-sm warna" required>
                </div>
            </div>
        </div>

        <!-- Section 2: Ditujukan Kepada & Pembuat -->
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label><i class="fa fa-paper-plane"></i> Ditujukan Kepada</label>
                    <input type="text" name="ditujukan" value="<?php echo $r['ditujukan']; ?>" class="form-control form-control-sm warna" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label><i class="fa fa-user-edit"></i> Pembuat</label>
                    <input type="text" name="pembuat" value="<?php echo $r['pembuat']; ?>" class="form-control form-control-sm warna" required>
                </div>
            </div>
        </div>

        <!-- Section 3: Perihal -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label><i class="fa fa-heading"></i> Perihal</label>
                    <textarea name="perihal" rows="3" class="form-control warna" required><?php echo $r['perihal']; ?></textarea>
                </div>
            </div>
        </div>

        <!-- Section 4: Lampiran -->
        <div class="file-upload-wrapper" id="drop-area-edit-<?php echo $id; ?>">
            <label><i class="fa fa-paperclip"></i> Lampiran Berkas (PDF)</label>
            <div class="file-upload-selector border rounded-top">
                <div class="upload-area d-flex align-items-center">
                    <div style="flex-shrink: 0;">
                        <button type="button" class="btn btn-sm btn-light-info" onclick="document.getElementById('fileInputEdit-<?php echo $id; ?>').click()">Pilih File...</button>
                    </div>
                    <div class="dropzone text-center prevent-select" onclick="document.getElementById('fileInputEdit-<?php echo $id; ?>').click()">
                        <span class="upload-text"><i class="fa fa-cloud-upload-alt mr-1"></i> atau drag & drop berkas disini.</span>
                    </div>
                </div>
            </div>
            
            <div id="file-list-display-edit-<?php echo $id; ?>" class="file-list-uploaded <?php echo empty($r['pdf']) ? 'd-none' : ''; ?>">
                <?php 
                $existing_files = explode(',', $r['pdf']);
                foreach($existing_files as $idx => $f) {
                    if(!empty($f)) {
                        $f_display = basename($f);
                        echo "
                        <div class='file-item-new existing-file' id='existing-file-{$id}-{$idx}'>
                            <div class='file-info-new'>
                                <i class='fa fa-file-pdf fa-2x text-danger'></i>
                                <div class='file-details-new'>
                                    <a href='file/legalisir/{$f}' target='_blank' class='file-name-new text-info text-decoration-none'>{$f_display} <i class='fa fa-external-link-alt fs-nano ml-1'></i></a>
                                    <span class='file-size-new italic'>Berkas Terlampir (Server)</span>
                                </div>
                            </div>
                            <div class='delete-btn-new ml-auto' onclick='deleteFileLegalisir(\"$id\", \"$f\", \"$idx\")' title='Hapus Lampiran'><i class='fa fa-trash-alt'></i></div>
                        </div>";
                    }
                }
                ?>
            </div>

            <div id="new-file-list-display-edit-<?php echo $id; ?>" class="file-list-uploaded d-none">
                <!-- New file items will be injected here -->
            </div>

            <div class="uploader-footer border rounded-bottom p-2 border-top-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div>Total Size: <span id="total-size-display-edit-<?php echo $id; ?>" class="font-600">0 B</span></div>
                    <div class="text-muted fs-nano">Max: 1 File (2.0 MB)</div>
                </div>
            </div>
            <input type="file" name="file[]" id="fileInputEdit-<?php echo $id; ?>" class="hidden-file-input" accept=".pdf" onchange="handleFileSelectEdit(this, '<?php echo $id; ?>')">
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-flat" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary btn-flat" name="update2"><i class="fa fa-save mr-1"></i> Update Data</button>
    </div>
</form>

<script>
(function() {
    const id = '<?php echo $id; ?>';
    const dropArea = document.getElementById('drop-area-edit-' + id);
    const fileInput = document.getElementById('fileInputEdit-' + id);
    const existingList = document.getElementById('file-list-display-edit-' + id);
    const newList = document.getElementById('new-file-list-display-edit-' + id);
    const totalDisplay = document.getElementById('total-size-display-edit-' + id);

    if (dropArea) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(ev => dropArea.addEventListener(ev, e => { e.preventDefault(); e.stopPropagation(); }, false));
        ['dragenter', 'dragover'].forEach(ev => dropArea.addEventListener(ev, () => dropArea.classList.add('dragover'), false));
        ['dragleave', 'drop'].forEach(ev => dropArea.addEventListener(ev, () => dropArea.classList.remove('dragover'), false));
        dropArea.addEventListener('drop', e => {
            if(e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                handleFileSelectEdit(fileInput, id);
            }
        }, false);
    }

    if (typeof window.selectedFilesEdit === 'undefined') { window.selectedFilesEdit = {}; }

    window.handleFileSelectEdit = function(input, currentId) {
        const files = Array.from(input.files);
        const pdfFiles = files.filter(f => f.type === 'application/pdf' || f.name.toLowerCase().endsWith('.pdf'));

        if (files.length > 0 && pdfFiles.length === 0) {
            alert("Mohon pilih berkas dengan format .pdf!");
            input.value = '';
            return;
        }

        if (pdfFiles.length > 1) {
            alert("Maksimal 1 berkas!");
            window.selectedFilesEdit[currentId] = [pdfFiles[0]];
        } else {
            window.selectedFilesEdit[currentId] = pdfFiles;
        }
        
        updateInputAndRender(currentId);
    };

    function updateInputAndRender(currentId) {
        const dataTransfer = new DataTransfer();
        const files = window.selectedFilesEdit[currentId] || [];
        files.forEach(f => dataTransfer.items.add(f));
        fileInput.files = dataTransfer.files;

        // Render NEW files
        newList.innerHTML = '';
        let totalSize = 0;
        
        if (files.length > 0) {
            newList.classList.remove('d-none');
            files.forEach((file, index) => {
                totalSize += file.size;
                if (file.size > 2 * 1024 * 1024) {
                    alert("Ukuran file " + file.name + " melebihi 2MB!");
                    removeFileEdit(index, currentId);
                    return;
                }
                
                const item = document.createElement('div');
                item.className = 'file-item-new';
                item.innerHTML = `
                    <div class="file-info-new">
                        <i class="fa fa-file-pdf fa-2x text-danger"></i>
                        <div class="file-details-new">
                            <div class="file-name-new">${file.name}</div>
                            <div class="file-size-new">Ukuran: ${formatBytes(file.size)}</div>
                            <div class="upload-status-active"><i class="fa fa-circle-notch fa-spin mr-1"></i> Siap diunggah...</div>
                        </div>
                    </div>
                    <div class="delete-btn-new ml-auto" onclick="removeFileEdit(${index}, '${currentId}')"><i class="fa fa-times"></i></div>
                `;
                newList.appendChild(item);

                // Add preview link after small delay
                const fileUrl = URL.createObjectURL(file);
                setTimeout(() => {
                    const nameEl = item.querySelector('.file-name-new');
                    const statusEl = item.querySelector('.upload-status-active');
                    if (nameEl) nameEl.innerHTML = `<a href="${fileUrl}" target="_blank" class="text-info text-decoration-none">${file.name} <i class="fa fa-external-link-alt fs-nano ml-1"></i></a>`;
                    if (statusEl) statusEl.innerHTML = '<i class="fa fa-check-circle mr-1"></i> Berkas siap';
                }, 800);
            });
        } else {
            newList.classList.add('d-none');
        }

        totalDisplay.textContent = formatBytes(totalSize);
    }

    window.removeFileEdit = function(index, currentId) {
        window.selectedFilesEdit[currentId].splice(index, 1);
        updateInputAndRender(currentId);
    };

    window.deleteFileLegalisir = function(currentId, file, idx) {
        // No confirmation as requested: "ini di hilangkan agar tidak ribet"
        $.ajax({
            type: 'POST',
            url: 'legalisir.php',
            data: { aksi: 'hapus_file', id: currentId, file: file },
            success: function(response) {
                if (response.trim().indexOf('success') !== -1) {
                    $('#existing-file-' + currentId + '-' + idx).fadeOut(300, function() {
                        $(this).remove();
                        if (existingList.querySelectorAll('.file-item-new').length === 0) {
                            existingList.classList.add('d-none');
                        }
                    });
                } else {
                    alert('Gagal: ' + response);
                }
            },
            error: function() { alert('Koneksi terputus'); }
        });
    };

    function formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }
})();
</script>

<?php 
} 
?>
