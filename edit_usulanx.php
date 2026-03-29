<?php

include "cfg/konek.php";

include "cfg/secure.php";



if($_REQUEST['urut']) {

    $id = $_POST['urut'];

    // mengambil data berdasarkan id

    // dan menampilkan data ke dalam form modal bootstrap

    $rql = mysqli_query($sqlconn,"SELECT * FROM usulan WHERE id = '$id'");

    $r = mysqli_fetch_array($rql);

?>



<form method="post" enctype="multipart/form-data">

    <input type="hidden" name="id" value="<?php echo $r['id']; ?>">

    

    <!-- Modal body -->

    <div class="modal-body">
        <!-- Section 1: No Surat & Tanggal -->
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label><i class="fa fa-file-alt"></i> No. Surat</label>
                    <input type="text" name="no_surat" value="<?php echo $r['no_surat'];?>" class="form-control form-control-sm warna" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label><i class="fa fa-calendar-alt"></i> Tanggal Dikirim</label>
                    <input type="date" name="tgl_dokumen" value="<?php echo $r['tgl_dokumen'];?>" class="form-control form-control-sm warna" required>
                </div>
            </div>
        </div>

        <!-- Section 2: Judul -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label><i class="fa fa-heading"></i> Judul Usulan</label>
                    <input type="text" name="judul" value="<?php echo $r['judul'];?>" class="form-control warna" required>
                </div>
            </div>
        </div>

        <!-- Section 3: Tujuan -->
        <div class="row">
            <div class="col-md-12">
                 <div class="form-group">
                    <label><i class="fa fa-paper-plane"></i> Tujuan / Instansi</label>
                    <textarea name="tujuan" rows="3" class="form-control warna" required><?php echo $r['tujuan'];?></textarea>
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
                $initial_total_size = 0;
                foreach($existing_files as $idx => $f) {
                    if(!empty($f)) {
                        $file_path = "file/usulan/" . $f;
                        $size_text = "Berkas Terlampir (Server)";
                        $size_bytes = 0;
                        if (file_exists($file_path)) {
                            $size_bytes = filesize($file_path);
                            $initial_total_size += $size_bytes;
                            // Helper to format bytes for PHP output
                            $units = array('B', 'KB', 'MB', 'GB', 'TB');
                            $i = $size_bytes ? floor(log($size_bytes, 1024)) : 0;
                            $size_formatted = number_format($size_bytes / pow(1024, $i), 1) . ' ' . $units[$i];
                            $size_text = "Ukuran: " . $size_formatted;
                        }
                        echo "
                        <div class='file-item-new existing-file-item' id='existing-file-{$id}-{$idx}' data-size='$size_bytes'>
                            <div class='file-info-new'>
                                <i class='fa fa-file-pdf fa-2x text-info'></i>
                                <div class='file-details-new'>
                                    <div class='file-name-new'>$f</div>
                                    <div class='file-size-new'>$size_text</div>
                                </div>
                            </div>
                            <div class='delete-btn-new ml-auto' onclick='deleteFileUsulan(\"$id\", \"$f\", \"$idx\")' title='Hapus Lampiran'><i class='fa fa-trash-alt'></i></div>
                        </div>";
                    }
                }
                ?>
            </div>

            <div id="new-file-list-display-edit-<?php echo $id; ?>" class="file-list-uploaded d-none text-left">
                <!-- New file items will be injected here -->
            </div>

            <div class="uploader-footer border rounded-bottom p-2 border-top-0">
                <div class="fs-xs"><span class="font-600 text-blck">Total : <span id="total-size-display-edit-<?php echo $id; ?>"><?php 
                    $units = array('B', 'KB', 'MB', 'GB', 'TB');
                    $i = $initial_total_size ? floor(log($initial_total_size, 1024)) : 0;
                    echo number_format($initial_total_size / pow(1024, $i), 1) . ' ' . $units[$i];
                ?></span></span></div>
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

            ['dragenter', 'dragover'].forEach(eventName => { 
                dropArea.addEventListener(eventName, () => dropArea.classList.add('dragover'), false); 
            });
            ['dragleave', 'drop'].forEach(eventName => { 
                dropArea.addEventListener(eventName, () => dropArea.classList.remove('dragover'), false); 
            });

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
    
    // JS Helper to display bytes
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

    window.deleteFileUsulan = function(id, fileName, idx) {
        if (confirm('Apakah Anda yakin ingin menghapus lampiran ini?')) {
            $.ajax({
                url: 'usulan.php',
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
        
        // If there are new files, they will override the display via renderFileListEdit
        if (!(window.selectedFilesEdit[id] && window.selectedFilesEdit[id].length > 0)) {
            $('#total-size-display-edit-' + id).text(window.formatBytes(total));
        }
    }

    function handleFileSelectEdit(input, id) {
        const files = Array.from(input.files);
        const pdfFiles = files.filter(f => f.type === 'application/pdf');
        
        if (files.length !== pdfFiles.length) {
            toastr.error('Hanya berkas PDF yang diperbolehkan!');
        }
        
        window.selectedFilesEdit[id] = pdfFiles;
        updateInputFilesEdit(id);
        renderFileListEdit(id);
    }

    function renderFileListEdit(id) {
        const listContainer = document.getElementById('new-file-list-display-edit-' + id);
        const existingContainer = document.getElementById('file-list-display-edit-' + id);
        const totalDisplay = document.getElementById('total-size-display-edit-' + id);
        if (!listContainer) return;
        
        let files = window.selectedFilesEdit[id] || [];
        const maxFiles = 1;
        const maxSizeTotal = 2 * 1024 * 1024; // 2MB

        if (files.length > maxFiles) {
            alert(`Maksimal ${maxFiles} berkas yang dapat diunggah!`);
            files = files.slice(0, maxFiles);
            window.selectedFilesEdit[id] = files;
            updateInputFilesEdit(id);
        }

        listContainer.innerHTML = '';
        let totalSize = 0;
        
        if (files.length > 0) { 
            listContainer.classList.remove('d-none');
            // Hide existing files if new one is selected (since only 1 is allowed)
            if (existingContainer) existingContainer.classList.add('d-none');
        } else { 
            listContainer.classList.add('d-none');
            if (existingContainer) {
                // Check if there are still existing files
                if (existingContainer.querySelectorAll('.existing-file-item').length > 0) {
                    existingContainer.classList.remove('d-none');
                }
            }
        }

        files.forEach((file, index) => {
            totalSize += file.size;
            const item = document.createElement('div');
            item.className = 'file-item-new';
            item.innerHTML = `
                <div class="file-info-new">
                    <i class="fa fa-file-pdf fa-2x text-info"></i>
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
                if (statusEl) { statusEl.remove(); }
            }, 1000);
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
            // Revert to initial total if no new files
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



    <div class="modal-footer">

        <button type="button" class="btn btn-secondary btn-flat" data-dismiss="modal">Tutup</button>

        <button type="submit" class="btn btn-primary btn-flat" name="update">Update</button>

    </div>

</form>



<?php } ?>
