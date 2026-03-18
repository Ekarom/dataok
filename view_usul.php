<?php
include "cfg/konek.php";
include "cfg/secure.php";

if (isset($_REQUEST['urut'])) {
    // Sanitasi input untuk mencegah SQL injection
    $id = mysqli_real_escape_string($sqlconn, $_POST['urut']);

    // Mengambil data berdasarkan id
    $sql = mysqli_query($sqlconn, "SELECT * FROM usulan WHERE id = '$id'");
    $r = mysqli_fetch_array($sql);

    if ($r) {
?>
        <style>
            .modal-view-body {
                background-color: #f8f9fa;
                padding: 20px;
                border-radius: 0 0 10px 10px;
            }
            .view-section {
                background: #fff;
                padding: 15px;
                border-radius: 12px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.05);
                margin-bottom: 20px;
                border: 1px solid #eef0f2;
            }
            .view-section-title {
                font-size: 0.9rem;
                font-weight: 700;
                color: #495057;
                margin-bottom: 15px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                border-bottom: 2px solid #007bff;
                display: inline-block;
                padding-bottom: 3px;
            }
            .view-label {
                font-size: 0.75rem;
                font-weight: 600;
                color: #6c757d;
                margin-bottom: 4px;
                display: block;
            }
            .view-value {
                font-size: 0.9rem;
                color: #212529;
                font-weight: 500;
                padding: 8px 12px;
                background: #f1f3f5;
                border-radius: 8px;
                min-height: 38px;
                display: flex;
                align-items: center;
                border: 1px solid #e9ecef;
            }
            .view-icon {
                width: 20px;
                margin-right: 8px;
                color: #007bff;
                text-align: center;
            }
            .pdf-container {
                border-radius: 12px;
                overflow: hidden;
                border: 1px solid #dee2e6;
                background: #fff;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            }
        </style>

        <div class="modal-view-body">
            <!-- PDF Viewer -->
            <div class="view-section">
                <center><span class="view-section-title"><i class="fa fa-file-pdf mr-1"></i> Pratinjau Dokumen</span></center>
                <div class="pdf-container mt-2">
                    <iframe id="pdfViewer" src="file/usulan/<?php echo htmlspecialchars($r['pdf']); ?>" frameborder="0" width="100%" height="600px"></iframe>
                </div>
            </div>

            <!-- Detail Information -->
            <div class="view-section">
                <center><span class="view-section-title"><i class="fas fa-info-circle mr-1"></i> Detail Usulan</span></center>
                
                <div class="row mt-2">
                    <div class="col-md-12 mb-3">
                        <label class="view-label">No. Surat</label>
                        <div class="view-value"><i class="fas fa-file-contract view-icon"></i> <?php echo htmlspecialchars($r['no_surat']); ?></div>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="view-label">Judul</label>
                        <div class="view-value" style="align-items: flex-start; padding-top: 10px; height: auto;">
                            <i class="fas fa-heading view-icon" style="margin-top: 3px;"></i> 
                            <?php echo nl2br(htmlspecialchars($r['judul'])); ?>
                        </div>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="view-label">Tujuan</label>
                        <div class="view-value"><i class="fas fa-paper-plane view-icon"></i> <?php echo htmlspecialchars($r['tujuan']); ?></div>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="view-label">Tanggal Dikirim</label>
                        <div class="view-value"><i class="fas fa-calendar-alt view-icon"></i> <?php echo date('d-m-Y', strtotime($r['tgl_dokumen'])); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer px-0 pb-0" style="border-top: none; background: #f8f9fa;">
            <button type="button" class="btn btn-secondary custom" data-dismiss="modal">Tutup</button>
        </div>
<?php
    } else {
        echo '<div class="alert alert-danger mx-3 my-3">Data tidak ditemukan!</div>';
    }
}
?>
