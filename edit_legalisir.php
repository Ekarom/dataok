<?php
include_once "cfg/konek.php";
include_once "cfg/secure.php";

if (isset($_GET['urut'])) 
{
    $id = mysqli_real_escape_string($sqlconn, $_GET['urut']);
    $rql = mysqli_query($sqlconn, "SELECT * FROM legalisir WHERE id = '$id'");
    $r = mysqli_fetch_array($rql);

    if ($r) {
?>
        <div class="container-fluid">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header box-shadow-0 bg-gradient-x-info">
                    <h5 class="card-title mb-0 text-white font-weight-bold">Form Edit Data Legalisir</h5>
                    <br>
                    <br>
                    <div class="card-action">
                        <a href="print/laporanlegalisir" class="btn btn-warning btn-sm rounded-pill shadow-sm">
                            Kembali
                        </a>
                    </div>
                </div>

                <form id="form-edit-legalisir-<?php echo $id; ?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $r['id']; ?>">

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-600 fs-xs text-muted mb-1">
                                        <i class="fa fa-file-alt mr-1 text-primary"></i> No. Surat
                                    </label>
                                    <input type="text" name="no_surat" value="<?php echo htmlspecialchars($r['no_surat']); ?>" class="form-control form-control-sm" required placeholder="Contoh: 001/LEG/2026">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-600 fs-xs text-muted mb-1">
                                        <i class="fa fa-calendar-alt mr-1 text-primary"></i> Tanggal Surat
                                    </label>
                                    <input type="date" name="tgl_dokumen" value="<?php echo $r['tgl_dokumen']; ?>" class="form-control form-control-sm" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-600 fs-xs text-muted mb-1">
                                        <i class="fa fa-paper-plane mr-1 text-primary"></i> Tujuan / Instansi
                                    </label>
                                    <input type="text" name="ditujukan" value="<?php echo htmlspecialchars($r['ditujukan']); ?>" class="form-control form-control-sm" required placeholder="Instansi atau Pihak Penerima">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-600 fs-xs text-muted mb-1">
                                        <i class="fa fa-user mr-1 text-primary"></i> Pembuat
                                    </label>
                                    <input type="text" name="pembuat" value="<?php echo htmlspecialchars($r['pembuat']); ?>" class="form-control form-control-sm" required placeholder="Nama Pembuat/Pihak Terkait">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="font-600 fs-xs text-muted mb-1">
                                        <i class="fa fa-heading mr-1 text-primary"></i> Perihal
                                    </label>
                                    <textarea name="perihal" rows="3" class="form-control" required placeholder="Deskripsi singkat perihal legalisir..."><?php echo htmlspecialchars($r['perihal']); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="font-600 fs-xs text-muted mb-1">
                                        <i class="fa fa-paperclip mr-1 text-primary"></i> Lampiran Berkas
                                    </label>
                                    <input type="file" name="file[]" id="fileInput" class="form-control form-control-sm" accept=".pdf" multiple onchange="handleFileSelectEdit(this, '<?php echo $id; ?>')">
                                    <div id="file-helper-text" class="small text-muted mt-1 <?php echo empty($r['pdf']) ? 'd-none' : ''; ?>">
                                        <i class="fas fa-info-circle mr-1"></i> Berkas lama: <span class="font-weight-bold text-dark"><?php echo $r['pdf']; ?></span> (Abaikan jika tidak ingin mengganti)
                                    </div>
                                    <div id="new-file-list-display-edit-<?php echo $id; ?>" class="d-none mt-2">
                                        <!-- Selection placeholder -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-end align-items-center">
                        <button type="button" id="btnIncompleteEdit-<?php echo $id; ?>" class="btn bg-gradient-secondary disabled text-white" style="cursor: not-allowed; border-radius: 4px;">
                            Isian Belum Lengkap
                        </button>
                        <button type="submit" id="btnSaveEdit-<?php echo $id; ?>" class="btn bg-gradient-primary text-white" name="update2" style="display: none; border-radius: 4px;">
                            Update Data
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function checkFormCompletionEdit(id)
            {
                const $form = $('#form-edit-legalisir-' + id);
                const $btnIncomplete = $('#btnIncompleteEdit-' + id);
                const $btnSave = $('#btnSaveEdit-' + id);

                if (!$form.length) return;

                let isComplete = true;
                $form.find('[required]').each(function () {
                    if (!$(this).val().trim()) isComplete = false;
                });

                if (isComplete) { $btnIncomplete.hide(); $btnSave.show(); } 
                else { $btnIncomplete.show(); $btnSave.hide(); }
            }

            function handleFileSelectEdit(input, id)
            {
                const files = Array.from(input.files);
                const pdfFiles = files.filter(f => f.type === 'application/pdf');

                if (files.length > 0 && pdfFiles.length === 0)
                {
                    alert('Hanya berkas PDF yang diperbolehkan!');
                    input.value = "";
                    return;
                }

                const $newDisplay = $('#new-file-list-display-edit-' + id);
                $newDisplay.empty();

                if (pdfFiles.length > 0)
                {
                    const file = pdfFiles[0];
                    if (file.size > 2 * 1024 * 1024)
                    {
                        alert('Ukuran berkas melebihi 2 MB!');
                        input.value = "";
                        return;
                    }
                    $newDisplay.removeClass('d-none').append('<div class="small text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Berkas siap: ' + file.name + '</div>');
                }
                else
                {
                    $newDisplay.addClass('d-none');
                }
            }

            $(document).ready(function () {
                const currentId = '<?php echo $id; ?>';
                $('#form-edit-legalisir-' + currentId).on('input change', 'input, select, textarea', function () {
                    checkFormCompletionEdit(currentId);
                });
                checkFormCompletionEdit(currentId);
            });
        </script>
<?php
    }
    else {
?>
        <div class="alert alert-danger mx-3 my-3 shadow-sm rounded-lg border-0">
            <h6 class="font-weight-bold"><i class="fas fa-exclamation-circle mr-2"></i> Data tidak ditemukan</h6>
            <p class="mb-0 small">ID tidak valid atau telah dihapus.</p>
        </div>
        <div class="px-3">
            <a href="print/laporanlegalisir" class="btn btn-primary px-4 rounded-pill shadow-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>
<?php
    }
}
?>
