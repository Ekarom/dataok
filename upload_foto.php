<?php
//===============================================//
//            Image Upload V 1.1 (Premium)
//                  By
//               Antigravity
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
        --glass-bg: rgba(255, 255, 255, 0.95);
        --glass-border: rgba(255, 255, 255, 0.2);
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
        color: var(--upload-primary);
        margin-bottom: 15px;
        transition: transform 0.3s ease;
    }

    .upload-zone:hover .upload-icon {
        transform: scale(1.1);
    }

    #file1 {
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
        background: linear-gradient(90deg, #4e73df 0%, #224abe 100%);
        box-shadow: 0 3px 10px rgba(78, 115, 223, 0.3);
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
</style>


    <section class="content px-4">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">
                        <div class="card-header bg-menu-gradient border-0 py-3 d-flex align-items-center justify-content-between">
                            <h5 class="mb-0 fw-bold"><i class="fas fa-cloud-upload-alt me-2"></i> Upload ZIP Archive</h5>
                        </div>
                        <div class="card-body p-4">
                            <p class="text-muted small mb-4">
                                <i class="fas fa-info-circle me-1"></i> 
                                Pastikan semua foto berada dalam format <strong>.jpg</strong> atau <strong>.png</strong> dan dikompresi ke dalam satu file <strong>.zip</strong>.
                            </p>

                            <form action="uphoto-go.php" method="POST" enctype="multipart/form-data" id="upload_form">
                                <input type="hidden" name="<?php echo ini_get("session.upload_progress.name"); ?>" value="upload_progress"/>
                                
                                <div class="upload-zone mb-4" id="drop-zone">
                                    <i class="fas fa-file-archive upload-icon"></i>
                                    <h5 class="fw-bold mb-1">Drag & Drop file ZIP di sini</h5>
                                    <p class="text-muted small mb-0">atau klik untuk menelusuri file</p>
                                    <input type="file" name="file1" id="file1" accept=".zip" required />
                                </div>

                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span id="file-name-display" class="text-primary fw-bold small"></span>
                                    <span id="size" class="badge bg-soft-success text-success fw-bold"></span>
                                </div>

                                <div class="progress-wrapper">
                                    <div id="progress-bar" class="progress-bar-premium h-100 d-flex align-items-center justify-content-center text-white small" style="width: 0%">0%</div>
                                </div>

                                <div class="text-center">
                                    <button type="submit" id="submit-btn" class="btn btn-primary btn-premium px-5 shadow-sm">
                                        <i class="fas fa-paper-plane me-2"></i>Upload
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card border-0 shadow-lg rounded-4 mb-4">
                        <div class="card-header bg-menu-gradient border-0 py-3">
                            <h5 class="mb-0 fw-bold"><i class="fas fa-terminal me-2"></i> Processing Log</h5>
                        </div>
                        <div class="card-body p-4">
                            <textarea class="form-control" id="info" rows="12" readonly placeholder="Waiting for processing..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="stat-card stat-all border-0 shadow-sm rounded-4">
                                <div class="stat-label text-info">Total Files</div>
                                <div class="stat-count" id="count">0</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="stat-card stat-ok border-0 shadow-sm rounded-4">
                                <div class="stat-label text-success">Terupload</div>
                                <div class="stat-count" id="ok">0</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="stat-card stat-err border-0 shadow-sm rounded-4">
                                <div class="stat-label text-danger">Gagal / Error</div>
                                <div class="stat-count" id="err">0</div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 mt-4 bg-menu-gradient p-4">
                        <h6 class="fw-bold mb-3">Statistik Terkini</h6>
                        <div class="d-flex align-items-center justify-content-between small opacity-75">
                            <span>Waktu Proses</span>
                            <span id="time-elapsed">0s</span>
                        </div>
                        <hr class="my-2 border-white opacity-25">
                        <div class="d-flex align-items-center justify-content-between small opacity-75">
                            <span>Status Server</span>
                            <span class="badge bg-success bullet">Ready</span>
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
    const fileInput = $('#file1');
    const fileNameDisplay = $('#file-name-display');

    // Drag and Drop Visuals
    dropZone.on('dragover', function(e) { e.preventDefault(); $(this).addClass('dragover'); });
    dropZone.on('dragleave', function() { $(this).removeClass('dragover'); });
    dropZone.on('drop', function() { $(this).removeClass('dragover'); });

    fileInput.on('change', function() { 
        if(this.files.length > 0) {
            let file = this.files[0];
            let iSize = (file.size / 1024);
            let rd = Math.ceil(iSize);
            $('#size').text(rd + " KB").fadeIn();
            fileNameDisplay.text(file.name);
        }
    });

    $("#upload_form").on('submit', function (event) {
        event.preventDefault();
        
        let file = fileInput[0].files[0];
        if(!file) return alert('Silakan pilih file ZIP terlebih dahulu!');

        let formData = new FormData();
        formData.append('file1', file);
        
        // Reset and start UI
        $("#info").val("Upload in progress, please wait...\n").css("color", "#f1f1f1");
        $("#progress-bar").css("width", "0%").text("0%").removeClass('bg-success bg-danger bg-warning');
        $("#submit-btn").prop("disabled", true).addClass('opacity-50');
        
        // Reset and show loading state on cards
        $("#count, #ok, #err").text("...");
        
        let startTime = Date.now();
        let timer = setInterval(() => {
            let seconds = Math.floor((Date.now() - startTime) / 1000);
            $('#time-elapsed').text(seconds + "s");
        }, 1000);

        $.ajax({
            type: 'POST',
            url: 'uphoto-go.php',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = Math.round((evt.loaded / evt.total) * 100);
                        $("#progress-bar").css("width", percentComplete + "%").text(percentComplete + "%");
                    }
                }, false);
                return xhr;
            },
            success: function (info) {
                clearInterval(timer);
                $("#submit-btn").prop("disabled", false).removeClass('opacity-50');
                
                try {
                    // Check if info is already an array (due to dataType: 'json')
                    if (typeof info === 'string') info = JSON.parse(info);
                    if (!Array.isArray(info)) throw new Error("Response is not an array");
                    
                    let text = "";
                    let total = 0;
                    let successCount = 0;
                    let errorCount = 0;
                    
                    // The first element is the status code (e.g., "3")
                    let info_copy = [...info];
                    let status = info_copy.shift();
                    
                    // Iterate through processed items
                    for (let entry of info_copy) {
                        text += entry + "\n";
                        let upEntry = entry.toUpperCase();
                        
                        // Increment total for all file-related entries
                        if (upEntry.startsWith("SUCCESS:") || upEntry.startsWith("ERROR:") || upEntry.startsWith("SKIPPED:")) {
                            total++;
                        }
                        
                        // Specifically count successes and errors
                        if (upEntry.startsWith("SUCCESS:")) {
                            successCount++;
                        } else if (upEntry.startsWith("ERROR:")) {
                            errorCount++;
                        }
                    }

                    $("#info").val(text);
                    $("#count").text(total);
                    $("#ok").text(successCount);
                    $("#err").text(errorCount);
                    
                    if (status == "3") {
                        $("#info").css("color", "#a5d6a7");
                        $("#progress-bar").addClass('bg-success').css("width", "100%").text("COMPLETE");
                    } else if (status == "5") {
                        $("#info").css("color", "#f6c23e");
                        $("#progress-bar").addClass('bg-warning').text("DONE WITH WARNINGS");
                    } else {
                        $("#info").css("color", "#ef9a9a");
                        $("#progress-bar").addClass('bg-danger').text("ERROR");
                    }
                    
                } catch (e) {
                    console.error("Parse Error:", e);
                    $("#info").val("Error processing response: " + e.message + "\nData: " + JSON.stringify(info));
                    $("#info").css("color", "#ef9a9a");
                    $("#count, #ok, #err").text("0");
                    $("#progress-bar").addClass('bg-danger').text("DATA ERROR");
                }
            },
            error: function (err) {
                clearInterval(timer);
                $("#submit-btn").prop("disabled", false).removeClass('opacity-50');
                $("#info").val("Server Error: " + err.statusText).css("color", "#ef9a9a");
                // Ensure counters don't stay at "..."
                $("#count, #ok, #err").text("Error");
                $("#progress-bar").addClass('bg-danger').text("SERVER ERROR");
            }
        });
    });
});
</script>
</body>
</html>
