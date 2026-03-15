<style>
    /* Consolidated Premium UI Styles */
    .file-upload-wrapper {
        width: 100%;
        margin-bottom: 20px;
    }

    .file-upload-selector {
        background: #f8f9fa;
        display: flex;
        align-items: center;
        border: 1px solid #ced4da;
        border-radius: 4px 4px 0 0;
    }

    .upload-area {
        display: flex;
        align-items: center;
        width: 100%;
        padding: 8px 12px;
    }

    .btn-light-info {
        background-color: #e3f2fd;
        color: #0288d1;
        border: 1px solid #b3e5fc;
        font-weight: 600;
        font-size: 13px;
        padding: 5px 12px;
        white-space: nowrap;
        border-radius: 4px;
        transition: all 0.2s;
    }

    .btn-light-info:hover {
        background-color: #b3e5fc;
        color: #01579b;
    }

    .dropzone {
        flex-grow: 1;
        text-align: center;
        color: #6c757d;
        font-size: 13px;
        border-left: 1px solid #dee2e6;
        margin-left: 10px;
        padding-left: 10px;
        cursor: pointer;
    }

    .file-list-uploaded {
        border: 1px solid #ced4da;
        border-top: none;
        background: #fff;
        max-height: 200px;
        overflow-y: auto;
    }

    .file-item-new {
        display: flex;
        align-items: center;
        padding: 10px 15px;
        border-bottom: 1px solid #f1f1f1;
        transition: background 0.2s;
    }

    .file-item-new:hover {
        background-color: #fcfcfc;
    }

    .file-info-new {
        display: flex;
        align-items: center;
        flex-grow: 1;
        min-width: 0;
    }

    .file-details-new {
        margin-left: 12px;
        overflow: hidden;
    }

    .file-name-new {
        font-size: 13px;
        font-weight: 600;
        color: #333;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
    }

    .file-size-new {
        font-size: 11px;
        color: #888;
        display: block;
    }

    .delete-btn-new {
        color: #dc3545;
        cursor: pointer;
        font-size: 16px;
        padding: 5px;
        margin-left: 10px;
        transition: color 0.2s;
    }

    .delete-btn-new:hover {
        color: #bd2130;
    }

    .uploader-footer {
        padding: 8px 12px;
        background: #f8f9fa;
        border: 1px solid #ced4da;
        border-top: none;
        border-radius: 0 0 4px 4px;
        font-size: 12px;
        line-height: 1.5;
    }

    .font-600 {
        font-weight: 600;
    }

    .text-blck {
        color: #333;
    }

    .hidden-file-input {
        display: none;
    }

    .file-upload-selector.dragover {
        background-color: #e8f0fe;
        border-color: #4285f4;
    }

    .fs-xs {
        font-size: 13px;
    }

    .fs-nano {
        font-size: 11px;
    }

    .mt-1 {
        margin-top: 0.25rem !important;
    }
</style>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-menu-gradient">
                <h3 class="card-title"><i class="fa fa-plus-circle mr-2"></i> Tambah Dokumen Legalisir</h3>
                <div class="card-tools">
                    <a href="print/laporanlegalisir" class="btn btn-sm btn-secondary shadow-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                </div>
            </div>

            <form id="form-tambah-legalisir" method="post" enctype="multipart/form-data">
                <div class="card-body">
                    <!-- Section 1: No Surat & Tanggal -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fa fa-file-alt mr-1"></i> No. Surat</label>
                                <input type="text" name="no_surat" class="form-control form-control-sm warna" required
                                    placeholder="Contoh: 001/LEG/2026">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fa fa-calendar-alt mr-1"></i> Tanggal Dikirim</label>
                                <input type="date" name="tgl_dokumen" class="form-control form-control-sm warna"
                                    required>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Ditujukan Kepada & Pembuat -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fa fa-paper-plane mr-1"></i> Tujuan / Instansi</label>
                                <input type="text" name="ditujukan" class="form-control form-control-sm warna" required
                                    placeholder="Instansi atau Pihak Penerima">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fa fa-user-edit mr-1"></i> Pembuat</label>
                                <input type="text" name="pembuat" class="form-control form-control-sm warna" required
                                    placeholder="Nama Pembuat/Pihak Terkait">
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Perihal -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label><i class="fa fa-heading mr-1"></i> Perihal</label>
                                <textarea name="perihal" rows="3" class="form-control warna"
                                    placeholder="Deskripsi singkat perihal legalisir..." required></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Section 4: Lampiran -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label><i class="fa fa-paperclip mr-1"></i> Lampiran Berkas</label>
                                <div class="file-upload-wrapper" id="drop-area">
                                    <div class="file-upload-selector border rounded-top">
                                        <div class="upload-area d-flex align-items-center">
                                            <div style="flex-shrink: 0;">
                                                <button type="button" class="btn btn-sm btn-light-info"
                                                    onclick="document.getElementById('fileInput').click()">Pilih
                                                    File...</button>
                                            </div>
                                            <div class="dropzone text-center prevent-select">
                                                <span class="upload-text"><i class="fa fa-cloud-upload-alt mr-1"></i>
                                                    atau drag & drop
                                                    berkas disini.</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="file-list-display" class="file-list-uploaded d-none text-left">
                                        <!-- File items will be injected here -->
                                    </div>
                                    <div class="uploader-footer border rounded-bottom p-2 border-top-0">
                                        <div class="fs-xs"><span class="font-600 text-blck">Total : </span><span
                                                id="total-size-display">0 B</span></div>
                                        <div class="fs-nano text-muted">
                                            Lampirkan berkas <span class="font-600 text-blck">.pdf</span> maksimal
                                            <span class="font-600 text-blck">1</span> berkas dan ukuran maksimal
                                            <span class="font-600 text-blck">2.0 MB</span>
                                        </div>
                                    </div>
                                    <input type="file" name="file[]" id="fileInput" class="hidden-file-input"
                                        accept=".pdf" multiple onchange="handleFileSelect(this)">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-end">
                    <button type="button" id="btnIncomplete" class="btn bg-gradient-secondary custom disabled"
                        style="cursor: not-allowed;">Isian Belum Lengkap</button>
                    <button type="submit" id="btnSave" class="btn bg-gradient-primary custom" name="add"
                        style="display: none;">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</section>
</div>