<?php
include_once "cfg/konek.php";
include_once "cfg/secure.php";

if (isset($_GET['urut'])) {
    $id = mysqli_real_escape_string($sqlconn, $_GET['urut']);
    $rql = mysqli_query($sqlconn, "SELECT * FROM legalisir WHERE id = '$id'");
    $r = mysqli_fetch_array($rql);
    if ($r) {
        ?>
        <!-- CSS Styles -->
        <style>
            .card-legalisir-edit {
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                border: none;
            }

            .form-section-title {
                font-size: 14px;
                font-weight: 700;
                color: #2c3e50;
                margin-bottom: 15px;
                padding-bottom: 5px;
                border-bottom: 2px solid #eee;
            }

            .warna:valid {
                background-color: #e8f0fe !important;
                border-color: #01b2d1 !important;
            }

            .custom {
                min-width: 140px;
                border-radius: 50px !important;
                font-weight: 600;
            }
        </style>
        <div class="container-fluid">
            <div class="card card-legalisir-edit mb-4">
                <div class="card-header bg-menu-gradient">
                    <h3 class="card-title mb-0 font-weight-bold">
                        <i class="fas fa-edit mr-2"></i> Edit Dokumen Legalisir
                    </h3>
                    <div class="card-tools ml-auto">
                        <a href="print/laporanlegalisir" class="btn btn-warning btn-sm rounded-pill">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                    </div>
                </div>

                <form id="form-edit-legalisir-<?php echo $id; ?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $r['id']; ?>">

                    <div class="card-body p-4">
                        <div class="form-section-title mb-4">
                            <i class="fas fa-info-circle mr-1 text-info"></i> Informasi Dokumen
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label class="font-600 fs-xs text-muted mb-1">
                                        <i class="fa fa-file-alt mr-1"></i> No. Surat
                                    </label>
                                    <input type="text" name="no_surat" value="<?php echo htmlspecialchars($r['no_surat']); ?>"
                                        class="form-control form-control-sm warna" required placeholder="Contoh: 001/LEG/2026">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label class="font-600 fs-xs text-muted mb-1">
                                        <i class="fa fa-calendar-alt mr-1"></i> Tanggal Surat
                                    </label>
                                    <input type="date" name="tgl_dokumen" value="<?php echo $r['tgl_dokumen']; ?>"
                                        class="form-control form-control-sm warna" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label class="font-600 fs-xs text-muted mb-1">
                                        <i class="fa fa-paper-plane mr-1"></i> Ditujukan Kepada
                                    </label>
                                    <input type="text" name="ditujukan" value="<?php echo htmlspecialchars($r['ditujukan']); ?>"
                                        class="form-control form-control-sm warna" required placeholder="Instansi Penerima">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label class="font-600 fs-xs text-muted mb-1">
                                        <i class="fa fa-user-edit mr-1"></i> Pembuat
                                    </label>
                                    <input type="text" name="pembuat" value="<?php echo htmlspecialchars($r['pembuat']); ?>"
                                        class="form-control form-control-sm warna" required placeholder="Nama Pembuat">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <div class="form-group">
                                    <label class="font-600 fs-xs text-muted mb-1">
                                        <i class="fa fa-heading mr-1"></i> Perihal
                                    </label>
                                    <textarea name="perihal" rows="3" class="form-control warna" required
                                        placeholder="Deskripsi singkat..."><?php echo htmlspecialchars($r['perihal']); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="form-section-title mb-4">
                            <i class="fas fa-paperclip mr-1 text-info"></i> Lampiran Berkas
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="file-upload-wrapper" id="drop-area-edit-<?php echo $id; ?>">
                                        <div class="file-upload-selector border rounded-top bg-light">
                                            <div class="upload-area d-flex align-items-center p-2">
                                                <div style="flex-shrink: 0;">
                                                    <button type="button" class="btn btn-sm btn-info shadow-sm"
                                                        onclick="document.getElementById('fileInputEdit-<?php echo $id; ?>').click()">
                                                        <i class="fas fa-folder-open mr-1"></i> Pilih File Baru...
                                                    </button>
                                                </div>
                                                <div class="dropzone text-center prevent-select ml-3">
                                                    <span class="upload-text text-muted fs-nano">
                                                        <i class="fa fa-cloud-upload-alt mr-1"></i> Ganti lampiran dengan drag &
                                                        drop disini.
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="file-list-display-edit-<?php echo $id; ?>"
                                            class="file-list-uploaded <?php echo empty($r['pdf']) ? 'd-none' : ''; ?> p-3 border-left border-right bg-white">
                                            <?php
                                            if (!empty($r['pdf'])) {
                                                $f = $r['pdf'];
                                                $file_path = "file/legalisir/" . $f;
                                                $size_text = "Berkas Terlampir";
                                                $size_bytes = 0;
                                                if (file_exists($file_path)) {
                                                    $size_bytes = filesize($file_path);
                                                    $units = array('B', 'KB', 'MB', 'GB', 'TB');
                                                    $exp = $size_bytes ? floor(log($size_bytes, 1024)) : 0;
                                                    $size_formatted = number_format($size_bytes / pow(1024, $exp), 1) . ' ' . $units[$exp];
                                                    $size_text = "Ukuran: " . $size_formatted;
                                                }
                                                echo "
                                            <div class='file-item-new existing-file-item d-flex align-items-center' id='existing-file-{$id}' data-size='$size_bytes'>
                                                <i class='fa fa-file-pdf fa-2x text-danger mr-3'></i>
                                                <div class='file-details-new'>
                                                    <div class='file-name-new font-weight-700 text-dark mb-0' style='font-size:13px;'>$f</div>
                                                    <div class='file-size-new text-muted fs-xs'>$size_text</div>
                                                </div>
                                            </div>";
                                            }
                                            ?>
                                        </div>

                                        <div id="new-file-list-display-edit-<?php echo $id; ?>"
                                            class="file-list-uploaded d-none p-3 border-left border-right bg-white shadow-inner">
                                            <!-- New file items will be injected here -->
                                        </div>

                                        <div class="uploader-footer border rounded-bottom p-2 border-top-0 bg-light">
                                            <div class="fs-xs d-flex justify-content-between">
                                                <span><span class="font-600 text-dark">Total Berkas : </span><span
                                                        id="total-size-display-edit-<?php echo $id; ?>"><?php
                                                           if (!empty($r['pdf'])) {
                                                               $units = array('B', 'KB', 'MB', 'GB', 'TB');
                                                               $exp = $size_bytes ? floor(log($size_bytes, 1024)) : 0;
                                                               echo number_format($size_bytes / pow(1024, $exp), 1) . ' ' . $units[$exp];
                                                           } else {
                                                               echo "0 B";
                                                           }
                                                           ?></span></span>
                                                <span class="fs-nano text-muted mt-1">Abaikan jika tidak ingin mengganti.</span>
                                            </div>
                                        </div>
                                        <input type="file" name="file[]" id="fileInputEdit-<?php echo $id; ?>"
                                            class="hidden-file-input" accept=".pdf" style="display:none;"
                                            onchange="handleFileSelectEdit(this, '<?php echo $id; ?>')">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-end">
                        <button type="button" id="btnIncompleteEdit-<?php echo $id; ?>"
                            class="btn bg-gradient-secondary custom disabled" style="cursor: not-allowed;">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Isian Belum Lengkap
                        </button>
                        <button type="submit" id="btnSaveEdit-<?php echo $id; ?>" class="btn bg-gradient-primary custom"
                            name="update2" style="display: none;">
                            <i class="fas fa-save mr-1"></i> Update
                        </button>
                    </div>
            </div>
            </form>
        </div>

        <script>
            /**
             * Form completeness checker
             */
            function checkFormCompletionEdit(id) {
                const $form = $('#form-edit-legalisir-' + id);
                const $btnIncomplete = $('#btnIncompleteEdit-' + id);
                const $btnSave = $('#btnSaveEdit-' + id);

                if (!$form.length || !$btnIncomplete.length || !$btnSave.length) return;

                let isComplete = true;
                $form.find('[required]').each(function () {
                    if (!$(this).val().trim()) {
                        isComplete = false;
                    }
                });

                if (isComplete) {
                    $btnIncomplete.hide();
                    $btnSave.show();
                } else {
                    $btnIncomplete.show();
                    $btnSave.hide();
                }
            }

            /**
             * File selection handler
             */
            function handleFileSelectEdit(input, id) {
                const files = Array.from(input.files);
                const pdfFiles = files.filter(f => f.type === 'application/pdf');

                if (files.length > 0 && pdfFiles.length === 0) {
                    toastr.error('Hanya berkas PDF yang diperbolehkan!');
                    input.value = "";
                    return;
                }

                const $listContainer = $('#new-file-list-display-edit-' + id);
                const $existingContainer = $('#file-list-display-edit-' + id);
                const $totalDisplay = $('#total-size-display-edit-' + id);

                $listContainer.empty();
                let totalSize = 0;

                if (pdfFiles.length > 0) {
                    $listContainer.removeClass('d-none');
                    $existingContainer.addClass('d-none');

                    const file = pdfFiles[0];
                    totalSize = file.size;

                    if (totalSize > 2 * 1024 * 1024) {
                        toastr.warning("Ukuran berkas melebihi 2 MB!");
                        input.value = "";
                        $listContainer.addClass('d-none');
                        $existingContainer.removeClass('d-none');
                        return;
                    }

                    const itemHtml = `
                        <div class="file-item-new d-flex align-items-center">
                            <i class="fa fa-file-pdf fa-2x text-info mr-3"></i>
                            <div class="file-details-new">
                                <div class="file-name-new font-weight-700 text-dark mb-0" style="font-size:13px;">${file.name}</div>
                                <div class="file-size-new text-muted fs-xs">Baru: ${window.formatBytes(file.size)}</div>
                            </div>
                        </div>
                    `;
                    $listContainer.append(itemHtml);
                    if ($totalDisplay.length) $totalDisplay.text(window.formatBytes(totalSize));
                } else {
                    $listContainer.addClass('d-none');
                    $existingContainer.removeClass('d-none');
                    const $existingItem = $existingContainer.find('.existing-file-item');
                    if ($existingItem.length && $totalDisplay.length) {
                        const originalSize = parseInt($existingItem.attr('data-size') || 0);
                        $totalDisplay.text(window.formatBytes(originalSize));
                    }
                }
            }

            $(document).ready(function () {
                const currentId = '<?php echo $id; ?>';
                const $form = $('#form-edit-legalisir-' + currentId);

                $form.on('input change', 'input, select, textarea', function () {
                    checkFormCompletionEdit(currentId);
                });

                checkFormCompletionEdit(currentId);

                // Initial total size display logic if window.formatBytes is ready
                if (typeof window.formatBytes === 'function') {
                    const $existingItem = $('#existing-file-' + currentId);
                    if ($existingItem.length) {
                        $('#total-size-display-edit-' + currentId).text(window.formatBytes(parseInt($existingItem.attr('data-size'))));
                    }
                }
            });
        </script>

    <?php } else { ?>
        <div class="alert alert-danger mx-3 my-3 shadow-sm rounded-lg">
            <i class="fas fa-exclamation-circle mr-2"></i> Data tidak ditemukan atau id tidak valid!
        </div>
        <a href="print/laporanlegalisir" class="btn btn-primary ml-3 px-4 rounded-pill shadow">
            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Laporan
        </a>
        <?php
    }
}
?>