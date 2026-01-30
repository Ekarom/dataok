<?php

include "cfg/konek.php";

include "cfg/secure.php";



    if($_REQUEST['urut']) {

        $id = $_POST['urut'];

        // mengambil data berdasarkan id

        // dan menampilkan data ke dalam form modal bootstrap

        $rql = mysqli_query($sqlconn,"SELECT * FROM legalisir WHERE id = '$id'");

        $r = mysqli_fetch_array($rql);

?>



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
            
            <div id="file-list-display-edit-<?php echo $id; ?>" class="file-list-uploaded <?php echo empty($r['pdf']) ? 'd-none' : ''; ?> text-left">
                <?php 
                $existing_files = explode(',', $r['pdf']);
                foreach($existing_files as $idx => $f) {
                    if(!empty($f)) {
                        echo "
                        <div class='file-item-new existing-file' id='existing-file-{$id}-{$idx}'>
                            <div class='file-info-new'>
                                <i class='fa fa-file-pdf fa-2x text-info'></i>
                                <div class='file-details-new'>
                                    <div class='file-name-new'>$f</div>
                                    <div class='file-size-new'>Berkas Terlampir (Server)</div>
                                </div>
                            </div>
                            <div class='delete-btn-new ml-auto' onclick='deleteFileLegalisir(\"$id\", \"$f\", \"$idx\")' title='Hapus Lampiran'><i class='fa fa-trash-alt'></i></div>
                        </div>";
                    }
                }
                ?>
            </div>

            <script>
            function deleteFileLegalisir(id, file, idx) {
                if (!confirm('Hapus lampiran ini?')) return;
                $.ajax({
                    type: 'POST',
                    url: 'legalisir.php',
                    data: { aksi: 'hapus_file', id: id, file: file },
                    success: function(response) {
                        if (response.trim() == 'success') {
                            $('#existing-file-' + id + '-' + idx).fadeOut(300, function() {
                                $(this).remove();
                                if ($('#file-list-display-edit-' + id + ' .file-item-new').length == 0) {
                                    $('#file-list-display-edit-' + id).addClass('d-none');
                                }
                            });
                        } else {
                            alert('Gagal menghapus: ' + response);
                        }
                    },
                    error: function() { alert('Terjadi kesalahan koneksi'); }
                });
            }
            </script>

            <div id="new-file-list-display-edit-<?php echo $id; ?>" class="file-list-uploaded d-none text-left">
                <!-- New file items will be injected here -->
            </div>

            <div class="uploader-footer border rounded-bottom p-2 border-top-0">
                <div class="fs-xs"><span class="font-600 text-blck">Total : <span id="total-size-display-edit-<?php echo $id; ?>">0 B</span></span></div>
                <div class="fs-nano text-muted">Lampirkan berkas <span class="font-600 text-blck">.pdf</span> maksimal <span class="font-600 text-blck">1</span> berkas dan ukuran maksimal <span class="font-600 text-blck">2.0 MB</span></div>
            </div>
            <input type="file" name="file[]" id="fileInputEdit-<?php echo $id; ?>" class="hidden-file-input" accept=".pdf" multiple onchange="handleFileSelectEdit(this, '<?php echo $id; ?>')">
        </div>
    </div>

    <script>
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
        const files = Array.from(input.files);
        const pdfFiles = files.filter(f => f.type === 'application/pdf');
        window.selectedFilesEdit[id] = pdfFiles;
        updateInputFilesEdit(id);
        renderFileListEdit(id);
    }

    function renderFileListEdit(id) {
        const listContainer = document.getElementById('new-file-list-display-edit-' + id);
        const totalDisplay = document.getElementById('total-size-display-edit-' + id);
        if (!listContainer) return;
        
        let files = window.selectedFilesEdit[id] || [];
        const maxFiles = 1;
        const maxSizeTotal = 2 * 1024 * 1024; // 2MB

        if (files.length > maxFiles) {
            alert(`Maksimal ${maxFiles} berkas!`);
            files = files.slice(0, maxFiles);
            window.selectedFilesEdit[id] = files;
            updateInputFilesEdit(id);
        }

        listContainer.innerHTML = '';
        let totalSize = 0;
        if (files.length > 0) { listContainer.classList.remove('d-none'); } else { listContainer.classList.add('d-none'); }

        files.forEach((file, index) => {
            totalSize += file.size;
            const item = document.createElement('div');
            item.className = 'file-item';
            item.innerHTML = `
                <div class="file-icon" style="flex-shrink: 0; line-height: 1;"><i class="fa fa-file-pdf"></i></div>
                <div class="file-details">
                    <div class="upload-status-text"><i class="fa fa-circle-notch fa-spin mr-1"></i> Sedang mengunggah...</div>
                    <div class="file-name-text text-truncate" title="${file.name}">${file.name}</div>
                    <div class="file-size-text">Ukuran: ${formatBytes(file.size)}</div>
                    <div class="progress-container">
                        <div class="progress-bar-fill animated-bar" style="width: 100%"></div>
                    </div>
                </div>
                <div class="file-remove" style="flex-shrink: 0;" onclick="removeFileEdit(${index}, '${id}')"><i class="fa fa-times"></i></div>
            `;
            listContainer.appendChild(item);
        });

        if (totalSize > maxSizeTotal) { 
            alert(`Ukuran berkas melebihi 2 MB!`);
            window.selectedFilesEdit[id] = [];
            updateInputFilesEdit(id);
            renderFileListEdit(id);
            return;
        }

        if (totalDisplay) totalDisplay.textContent = formatBytes(totalSize);
    }

    window.removeFileEdit = function(index, id) {
        window.selectedFilesEdit[id].splice(index, 1);
        updateInputFilesEdit(id);
        renderFileListEdit(id);
    };

    function updateInputFilesEdit(id) {
        const input = document.getElementById('fileInputEdit-' + id);
        const dataTransfer = new DataTransfer();
        window.selectedFilesEdit[id].forEach(file => dataTransfer.items.add(file));
        input.files = dataTransfer.files;
    }
    </script>
                                                                                                                                                                                                                     

<div class="modal-footer">

<button type="button" class="btn btn-secondary btn-flat" data-dismiss="modal">Batal</button>



<button type="submit" class="btn btn-primary btn-flat" name="update2">Update</button>

                                                </form>

                                       

										   <!------------------------------------------------------------------------ End Edit Modal ------------------------------------------------------------------------------------------------------------------------------>

<?php } ?>