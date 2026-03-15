<?php
include_once "cfg/konek.php";
include_once "cfg/secure.php";

if (isset($_GET['urut'])) {
    // Sanitasi input untuk mencegah SQL injection
    $id = mysqli_real_escape_string($sqlconn, $_GET['urut']);

    // Mengambil data berdasarkan id
    $rql = mysqli_query($sqlconn, "SELECT * FROM legalisir WHERE id = '$id'");
    $r = mysqli_fetch_array($rql);

    if ($r) {
?>
        <style>
            .view-container {
                padding: 10px 0;
            }
            .view-section {
                background: #fff;
                padding: 20px;
                border-radius: 12px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.05);
                margin-bottom: 20px;
                border: 1px solid #eef0f2;
            }
            .view-section-title {
                font-size: 1rem;
                font-weight: 700;
                color: #495057;
                margin-bottom: 20px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                border-bottom: 3px solid #007bff;
                display: inline-block;
                padding-bottom: 5px;
            }
            .view-label {
                font-size: 0.8rem;
                font-weight: 600;
                color: #6c757d;
                margin-bottom: 6px;
                display: block;
            }
            .view-value {
                font-size: 1rem;
                color: #212529;
                font-weight: 500;
                padding: 10px 15px;
                background: #f8f9fa;
                border-radius: 8px;
                min-height: 45px;
                display: flex;
                align-items: center;
                border: 1px solid #e9ecef;
            }
            .view-icon {
                width: 25px;
                margin-right: 12px;
                color: #007bff;
                text-align: center;
                font-size: 1.1rem;
            }
            .pdf-container {
                border-radius: 12px;
                overflow: hidden;
                border: 1px solid #dee2e6;
                background: #fff;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }
        </style>

        <div class="view-container">
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-eye"></i> Detail Dokumen Legalisir</h3>
                    <div class="card-tools">
                        <a href="print/laporanlegalisir" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Detail Information -->
                        <div class="col-lg-4">
                            <div class="view-section">
                                <h5 class="view-section-title"><i class="fas fa-info-circle mr-1"></i> Data Dokumen</h5>
                                
                                <div class="mb-3">
                                    <label class="view-label">No. Surat</label>
                                    <div class="view-value"><i class="fas fa-file-contract view-icon"></i> <?php echo htmlspecialchars($r['no_surat']); ?></div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="view-label">Tanggal Surat</label>
                                    <div class="view-value"><i class="fas fa-calendar-alt view-icon"></i> <?php echo date('d-m-Y', strtotime($r['tgl_dokumen'])); ?></div>
                                </div>

                                <div class="mb-3">
                                    <label class="view-label">Ditujukan Kepada</label>
                                    <div class="view-value"><i class="fas fa-paper-plane view-icon"></i> <?php echo htmlspecialchars($r['ditujukan']); ?></div>
                                </div>

                                <div class="mb-3">
                                    <label class="view-label">Perihal</label>
                                    <div class="view-value" style="align-items: flex-start; padding-top: 12px; height: auto;">
                                        <i class="fas fa-align-left view-icon" style="margin-top: 4px;"></i> 
                                        <?php echo nl2br(htmlspecialchars($r['perihal'])); ?>
                                    </div>
                                </div>

                                <div class="mb-0">
                                    <label class="view-label">Pembuat</label>
                                    <div class="view-value"><i class="fas fa-user-edit view-icon"></i> <?php echo htmlspecialchars($r['pembuat']); ?></div>
                                </div>
                            </div>
                            
                            <div class="text-center mt-3">
                                <a href="print/laporanlegalisir" class="btn btn-lg btn-danger btn-block"><i class="fas fa-arrow-left mr-2"></i> Kembali ke List</a>
                            </div>
                        </div>

                        <!-- PDF Viewer -->
                        <div class="col-lg-8">
                            <div class="view-section">
                                <h5 class="view-section-title text-center d-block"><i class="fa fa-file-pdf mr-1"></i> Pratinjau Lampiran</h5>
                                <div class="pdf-container mt-2">
                                    <?php if(!empty($r['pdf'])): ?>
                                        <embed type="application/pdf" src="file/legalisir/<?php echo htmlspecialchars($r['pdf']); ?>" frameborder="0" width="100%" height="750px">
                                    <?php else: ?>
                                        <div class="alert alert-warning text-center py-5">
                                            <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                                            <h5>Lampiran tidak tersedia!</h5>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<?php
    } else {
        echo '<div class="alert alert-danger mx-3 my-3">Data tidak ditemukan!</div>';
        echo '<a href="print/laporanlegalisir" class="btn btn-primary ml-3">Kembali</a>';
    }
}
?>
