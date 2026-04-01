<?php
//===============================================//
//          Grade Data Import V 1.1 (Premium)
//                  By
//                  ME
//            Copyright © 2023
//===============================================//

include "cfg/konek.php";
include "cfg/secure.php";
?>

<style>
    :root {
        --upload-primary: #4e73df;
        --upload-success: #1cc88a;
        --upload-info: #36b9cc;
        --upload-warning: #f6c23e;
        --upload-danger: #e74a3b;
        --upload-dark: #5a5c69;
    }

    .upload-zone {
        border: 2px dashed #d1d3e2;
        border-radius: 12px;
        padding: 40px 20px;
        text-align: center;
        transition: all 0.3s ease;
        background: #f8f9fc;
        cursor: pointer;
        position: relative;
    }

    .upload-zone:hover, .upload-zone.dragover {
        border-color: var(--upload-primary);
        background: #eef2ff;
        transform: translateY(-2px);
    }

    .upload-icon {
        font-size: 48px;
        color: var(--upload-success);
        margin-bottom: 15px;
        transition: transform 0.3s ease;
    }

    .upload-zone:hover .upload-icon {
        transform: scale(1.1);
    }

    #userfile {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        opacity: 0;
        cursor: pointer;
    }

    .progress-wrapper {
        background: #eaecf4;
        border-radius: 50px;
        overflow: hidden;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
        height: 25px;
        margin: 20px 0;
    }

    .progress-bar-premium {
        background: linear-gradient(90deg, #1cc88a 0%, #17a673 100%);
        box-shadow: 0 3px 10px rgba(28, 200, 138, 0.3);
        transition: width 0.4s ease;
        font-weight: bold;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
    }

    #info {
        background: #2d2d2d !important;
        color: #f1f1f1 !important;
        border: none;
        border-radius: 8px;
        font-family: 'Fira Code', 'Courier New', monospace;
        padding: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        line-height: 1.6;
        resize: none;
    }

    .stat-card {
        border-left: 4px solid;
        border-radius: 8px;
        padding: 15px;
        background: #fff;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        transition: transform 0.2s;
    }

    .stat-card:hover { transform: scale(1.02); }
    .stat-count { font-size: 1.5rem; font-weight: 700; color: #5a5c69; }
    .stat-label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; margin-bottom: 5px; }
    
    .stat-all { border-left-color: var(--upload-info); }
    .stat-ok { border-left-color: var(--upload-success); }
    .stat-err { border-left-color: var(--upload-danger); }
    
    .btn-premium {
        border-radius: 50px;
        padding: 10px 25px;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .btn-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0,0,0,0.15);
    }

    .template-box {
        background: #e3f2fd;
        border-radius: 12px;
        padding: 20px;
        border: 1px solid #bbdefb;
    }
</style>


    <section class="content px-4">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-8">
                    <!-- Setup Card -->
                    <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">
                        <div class="card-header box-shadow-0 bg-gradient-x-info border-0 py-3 d-flex align-items-center justify-content-between">
                            <h5 class="mb-0 fw-bold text-white"><i class="fas fa-file-excel me-2"></i> Import Excel Nilai</h5>
                        </div>
                        
                        <div class="card-body p-4">
                            <div>
                                <a href="file/data_nilai_siswa.xls" class="btn btn-sm btn-primary">
                                <i class="fas fa-download me-1"></i> Template Nilai
                            </a>
                            </div>

                            <form id="upload_form">
                                <div class="upload-zone mb-4" id="drop-zone">
                                    <i class="fas fa-cloud-upload-alt upload-icon"></i>
                                    <h5 class="fw-bold mb-1">Drag & Drop file Excel (XLS/XLSX)</h5>
                                    <p class="text-muted small mb-0">atau klik untuk menelusuri file</p>
                                    <input type="file" name="userfile" id="userfile" accept=".xls,.xlsx" required />
                                </div>

                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span id="file-name-display" class="text-primary fw-bold small"></span>
                                    <span id="size" class="badge bg-soft-success text-success fw-bold"></span>
                                </div>

                                <div class="progress-wrapper">
                                    <div id="progress-bar" class="progress-bar-premium h-100 d-flex align-items-center justify-content-center text-white small" style="width: 0%">0%</div>
                                </div>

                                <div class="text-center">
                                    <button type="submit" id="submit-btn" class="btn btn-success btn-premium px-5 shadow-sm">
                                        <i class="fas fa-upload me-2"></i> Import
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Log Card -->
                    <div class="card border-0 shadow-lg rounded-4 mb-4">
                        <div class="card-header box-shadow-0 bg-gradient-x-info border-0 py-3">
                            <h5 class="mb-0 fw-bold text-white"><i class="fas fa-terminal me-2"></i> Processing Log</h5>
                        </div>
                        <div class="card-body p-4">
                            <textarea class="form-control" id="info" rows="12" readonly placeholder="Awaiting file upload..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="stat-card stat-all border-0 shadow-sm rounded-4">
                                <div class="stat-label text-info">Total Baris</div>
                                <div class="stat-count" id="count">0</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="stat-card stat-ok border-0 shadow-sm rounded-4">
                                <div class="stat-label text-success">Import Sukses</div>
                                <div class="stat-count" id="ok">0</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="stat-card stat-err border-0 shadow-sm rounded-4">
                                <div class="stat-label text-danger">Gagal / Lewati</div>
                                <div class="stat-count" id="err">0</div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 mt-4 box-shadow-0 bg-gradient-x-info text-white p-4">
                        <h6 class="fw-bold mb-3">Statistik Proses</h6>
                        <div class="d-flex align-items-center justify-content-between small opacity-75">
                            <span>Durasi</span>
                            <span id="time-elapsed">0s</span>
                        </div>
                        <hr class="my-2 border-white opacity-25">
                        <div class="d-flex align-items-center justify-content-between small opacity-75">
                            <span>Server Status</span>
                            <span class="badge bg-white text-success bullet">Active</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function () {
    const dropZone = $('#drop-zone');
    const fileInput = $('#userfile');
    const fileNameDisplay = $('#file-name-display');

    dropZone.on('dragover', function(e) { e.preventDefault(); $(this).addClass('dragover'); });
    dropZone.on('dragleave', function() { $(this).removeClass('dragover'); });
    dropZone.on('drop', function() { $(this).removeClass('dragover'); });

    fileInput.on('change', function() { 
        if(this.files.length > 0) {
            let file = this.files[0];
            $('#size').text(Math.ceil(file.size / 1024) + " KB").fadeIn();
            fileNameDisplay.text(file.name);
        }
    });

    $("#upload_form").on('submit', function (e) {
        e.preventDefault();
        
        let file = fileInput[0].files[0];
        if(!file) return alert('Silakan pilih file Excel terlebih dahulu!');

        let formData = new FormData();
        formData.append('userfile', file);
        
        $("#info").val("Initializing data import...\n").css("color", "#f1f1f1");
        $("#progress-bar").css("width", "0%").text("0%").removeClass('bg-success bg-danger bg-warning');
        $("#submit-btn").prop("disabled", true).addClass('opacity-50');
        $("#count, #ok, #err").text("...");
        
        let startTime = Date.now();
        let timer = setInterval(() => {
            $('#time-elapsed').text(Math.floor((Date.now() - startTime) / 1000) + "s");
        }, 1000);

        $.ajax({
            type: 'POST',
            url: 'unilai-go.php',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        var percent = Math.round((evt.loaded / evt.total) * 100);
                        $("#progress-bar").css("width", percent + "%").text(percent + "%");
                    }
                }, false);
                return xhr;
            },
            success: function (info) {
                clearInterval(timer);
                $("#submit-btn").prop("disabled", false).removeClass('opacity-50');
                
                try {
                    if (!Array.isArray(info)) throw new Error("Format respon server tidak valid (Bukan Array).");
                    
                    let text = "";
                    let total = 0;
                    let successCount = 0;
                    let errorCount = 0;
                    
                    // Flags to prefer server-side stats if available
                    let distinctTotal = null;
                    let distinctSuccess = null;
                    let distinctFailed = null;

                    let info_copy = [...info];
                    let status = info_copy.shift();
                    
                    for (let entry of info_copy) {
                        text += entry + "\n";
                        let upEntry = entry.trim().toUpperCase();
                        
                        // Parse Server-Side Stats
                        if (upEntry.startsWith("SUMMARY:")) {
                            let parts = upEntry.replace("SUMMARY:", "").split(",");
                            parts.forEach(p => {
                                let kv = p.split("=");
                                if (kv.length === 2) {
                                    let k = kv[0].trim();
                                    let v = parseInt(kv[1]);
                                    if (k === "SUCCESS") distinctSuccess = v;
                                    if (k === "FAILED") distinctFailed = v;
                                }
                            });
                        }
                        if (upEntry.startsWith("DONE:")) {
                            // "DONE: Processed 123 rows."
                            let match = upEntry.match(/PROCESSED (\d+) ROWS/);
                            if (match && match[1]) {
                                distinctTotal = parseInt(match[1]);
                            }
                        }

                        // Fallback counting (only if server stats missing)
                        if (distinctTotal === null) {
                            if (upEntry.startsWith("SUCCESS:") || upEntry.startsWith("ERROR:") || upEntry.startsWith("SKIPPED:")) {
                                total++;
                            }
                            if (upEntry.startsWith("SUCCESS:")) {
                                successCount++;
                            } else if (upEntry.startsWith("ERROR:") || upEntry.startsWith("SKIPPED:")) {
                                errorCount++;
                            }
                        }
                    }

                    $("#info").val(text);
                    
                    // Use Server Stats if available, otherwise Fallback
                    $("#count").text(distinctTotal !== null ? distinctTotal : total);
                    $("#ok").text(distinctSuccess !== null ? distinctSuccess : successCount);
                    $("#err").text(distinctFailed !== null ? distinctFailed : errorCount);
                    
                    if (status == "3") {
                        $("#info").css("color", "#a5d6a7");
                        $("#progress-bar").addClass('bg-success').css("width", "100%").text("COMPLETE");
                    } else if (status == "5") {
                        $("#info").css("color", "#f6c23e");
                        $("#progress-bar").addClass('bg-warning').text("DONE WITH WARNINGS");
                    } else {
                        $("#info").css("color", "#ef9a9a");
                        $("#progress-bar").addClass('bg-danger').text("PROCESS ERROR");
                    }
                    
                } catch (e) {
                    console.error(e);
                    $("#info").val("Client Error: " + e.message + "\n\nRaw Response:\n" + JSON.stringify(info));
                    $("#info").css("color", "#ef9a9a");
                    $("#count, #ok, #err").text("Error");
                    $("#progress-bar").addClass('bg-danger').text("CLIENT ERROR");
                }
            },
            error: function (xhr, status, error) {
                clearInterval(timer);
                $("#submit-btn").prop("disabled", false).removeClass('opacity-50');
                
                let errorMsg = "Server Error: " + status + " " + error;
                if(xhr.responseText) {
                   // Clean up response text for display (truncate if too long)
                   let responsePreview = xhr.responseText.substring(0, 500);
                   errorMsg += "\n\nDetails:\n" + responsePreview;
                }
                
                $("#info").val(errorMsg).css("color", "#ef9a9a");
                $("#count, #ok, #err").text("Error");
                $("#progress-bar").addClass('bg-danger').text("SERVER ERROR");
            }
        });
    });
});
</script>
</body>
</html>
