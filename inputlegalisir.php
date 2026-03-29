<section class="content">
    <div class="container-fluid">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header box-shadow-0 bg-gradient-x-info">
                <h5 class="card-title mb-0 text-white font-weight-bold">Form Input Data Legalisir</h5>
                <br>
                <br>
                <div class="card-action">
                    <a href="print/laporanlegalisir" class="btn btn-warning btn-sm rounded-pill shadow-sm">
                        Kembali
                    </a>
                </div>
            </div>

            <form id="form-tambah-legalisir" method="post" enctype="multipart/form-data">
                <div class="card-body">
                    <!-- Section 1: No Surat & Tanggal -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-600 fs-xs text-muted mb-1">
                                    <i class="fa fa-file-alt mr-1 text-primary"></i> No. Surat
                                </label>
                                <input type="text" name="no_surat" class="form-control form-control-sm" required placeholder="Contoh: 001/LEG/2026">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-600 fs-xs text-muted mb-1">
                                    <i class="fa fa-calendar-alt mr-1 text-primary"></i> Tanggal Dikirim
                                </label>
                                <input type="date" name="tgl_dokumen" class="form-control form-control-sm" required>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Ditujukan Kepada & Pembuat -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-600 fs-xs text-muted mb-1">
                                    <i class="fa fa-paper-plane mr-1 text-primary"></i> Tujuan / Instansi
                                </label>
                                <input type="text" name="ditujukan" class="form-control form-control-sm" required placeholder="Instansi atau Pihak Penerima">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-600 fs-xs text-muted mb-1">
                                    <i class="fa fa-user mr-1 text-primary"></i> Pembuat
                                </label>
                                <input type="text" name="pembuat" class="form-control form-control-sm" required placeholder="Nama Pembuat/Pihak Terkait">
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Perihal -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="font-600 fs-xs text-muted mb-1">
                                    <i class="fa fa-heading mr-1 text-primary"></i> Perihal
                                </label>
                                <textarea name="perihal" rows="3" class="form-control" placeholder="Deskripsi singkat perihal legalisir..." required></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Section 4: Lampiran -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="font-600 fs-xs text-muted mb-1">
                                    <i class="fa fa-paperclip mr-1 text-primary"></i> Lampiran Berkas
                                </label>
                                <input type="file" name="file[]" id="fileInput" class="form-control form-control-sm" accept=".pdf" onchange="handleFileSelect(this)">
                                <div id="new-file-info" class="d-none mt-2">
                                    <!-- Placeholder for file select feedback -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-end align-items-center bg-white border-top">
                    <button type="button" id="btnIncomplete" class="btn bg-gradient-secondary text-white disabled" style="cursor: not-allowed; border-radius: 4px;">
                        Isian Belum Lengkap
                    </button>
                    <button type="submit" id="btnSave" class="btn bg-gradient-primary text-white shadow-sm" name="add" style="display: none; border-radius: 4px;">
                        <i class="fas fa-save mr-1"></i> Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
    function checkFormCompletion()
    {
        const $form = $('#form-tambah-legalisir');
        const $btnIncomplete = $('#btnIncomplete');
        const $btnSave = $('#btnSave');

        let isComplete = true;
        $form.find('[required]').each(function () {
            if (!$(this).val().trim()) isComplete = false;
        });

        if (isComplete) { $btnIncomplete.hide(); $btnSave.show(); } 
        else { $btnIncomplete.show(); $btnSave.hide(); }
    }

    function handleFileSelect(input)
    {
        const files = Array.from(input.files);
        const pdfFiles = files.filter(f => f.type === 'application/pdf');

        if (files.length > 0 && pdfFiles.length === 0)
        {
            alert('Hanya berkas PDF yang diperbolehkan!');
            input.value = "";
            return;
        }

        const $info = $('#new-file-info');
        $info.empty();

        if (pdfFiles.length > 0)
        {
            const file = pdfFiles[0];
            if (file.size > 2 * 1024 * 1024)
            {
                alert('Ukuran berkas melebihi 2 MB!');
                input.value = "";
                return;
            }
            $info.removeClass('d-none').append('<div class="small text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Berkas siap: ' + file.name + '</div>');
        }
        else
        {
            $info.addClass('d-none');
        }
    }

    $(document).ready(function () {
        $('#form-tambah-legalisir').on('input change', 'input, select, textarea', function () {
            checkFormCompletion();
        });
        checkFormCompletion();
    });
</script>