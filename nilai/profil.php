<!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<style>
    /* ==========================================
       PROFILE CARD STYLES
       ========================================== */
    .profile-card {
        background-color: #ffffff;
        transition: all 0.3s ease-in-out;
    }
    
    .profile-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 247, 255, 1);
    }
    
    /* ==========================================
       CUSTOM ELEMENTS
       ========================================== */
    .hr-custom {
        border-top: 1px solid #495057;
    }
    
    .btn-custom {
        border-radius: 0.5rem;
        transition: background-color 0.2s;
    }
    
    /* ==========================================
       PROGRESS BAR
       ========================================== */
    .progress {
        background-color: #FF2900;
    }
    
    /* ==========================================
       TOASTR NOTIFICATION
       ========================================== */
    .toast-top-center {
        top: 20px;
    }
    
    /* ==========================================
       PASSWORD STRENGTH INDICATOR
       ========================================== */
    .password-strength-meter {
        height: 5px;
        background-color: #e9ecef;
        border-radius: 3px;
        margin-top: 8px;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .password-strength-bar {
        height: 100%;
        width: 0%;
        transition: all 0.3s ease;
        border-radius: 3px;
    }
    
    .strength-weak {
        background-color: #dc3545;
        width: 33%;
    }
    
    .strength-medium {
        background-color: #ffc107;
        width: 66%;
    }
    
    .strength-strong {
        background-color: #28a745;
        width: 100%;
    }
    
    .password-strength-text {
        font-size: 0.85rem;
        margin-top: 5px;
        font-weight: 500;
    }
    
    .text-weak {
        color: #dc3545;
    }
    
    .text-medium {
        color: #ffc107;
    }
    
    .text-strong {
        color: #28a745;
    }

    /* Circular Upload Container */
    .profile-img-container {
        position: relative;
        width: 130px;
        height: 130px;
        margin: 0 auto 15px;
        cursor: pointer;
        overflow: hidden;
        border-radius: 50%;
        border: 4px solid #fff;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    
    .profile-img-container:hover {
        border-color: #007bff;
        transform: scale(1.02);
        box-shadow: 0 6px 20px rgba(0,123,255,0.2);
    }
    
    .profile-img-container .overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        color: #fff;
    }
    
    .profile-img-container:hover .overlay {
        opacity: 1;
    }
    
    .profile-img-container .overlay i {
        font-size: 32px;
        margin-bottom: 5px;
    }
    
    .profile-img-container .overlay span {
        font-size: 12px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .profile-img-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>


    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Left Column: Profile Card -->
                <div class="col-md-3">
                    <!-- Profile Image Card -->
                    <div class="card shadow-sm border-0">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <div class="profile-img-container" onclick="document.getElementById('photo1').click();" title="Klik untuk ganti foto">
                                    <div id='exisImage'>
                                        <?php
                                        if (!empty($p_siswa)) {
                                            // Student Photo logic
                                            $s_photo = $p_siswa['photo'] ?? '';
                                            if (!empty($s_photo) && file_exists("../file/fotopd/$s_photo")) {
                                                echo "<img src='file/fotopd/$s_photo' alt='Student Photo'>";
                                            } else {
                                                echo "<img src='images/default.png' alt='Default Photo'>";
                                            }
                                        } else {
                                            // Admin/Staff Photo logic
                                            if (!empty($poto) && file_exists("images/$poto")) {
                                                echo "<img src='images/$poto' alt='User Photo'>";
                                            } else {
                                                echo "<img src='images/default.png' alt='Default Photo'>";
                                            }
                                        }
                                        ?>
                                    </div>
                                    <div class="overlay">
                                        <i class="fas fa-camera"></i>
                                        <span>Ganti Foto</span>
                                    </div>
                                </div>
                                <h3 id="status" class="mt-1"></h3>
                            </div>
                            
                            <?php
                            if (!empty($nama)) {
                                echo "<h3 class='profile-username text-center'>".$nama."</h3>";
                            } elseif (!empty($nis)) {
                                echo "<h3 class='profile-username text-center'>".$nis."</h3>";
                            }
                            ?>
                            
                            <p class="text-center">
                                <?php
                                if (!empty($p_siswa)) {
                                    echo "<span class='badge bg-info'>Siswa (Active)</span>";
                                } else if (isset($lv)) {
                                    if ($lv == "1") {
                                        echo "<span class='badge bg-menu-gradient'>Administrator</span>";
                                    } elseif ($lv == "2") {
                                        echo "<span class='badge bg-menu-gradient'>Staff</span>";
                                    } elseif ($lv == "3") {
                                        echo "<span class='badge bg-menu-gradient'>User</span>";
                                    }
                                }
                                ?>
                            </p>
                            
                            <!-- Photo Upload Form (Hidden) -->
                            <form id="upload_form" enctype="multipart/form-data" method="post" style="display:none;">
                                <input type="file" name="photo1" id="photo1" onchange="uploadFile()" accept="image/*">
                            </form>

                            <!-- Progress Display -->
                            <div class="upload-feedback mb-3 text-center">
                                <div class="progress mb-2" style="height: 6px; display: none; border-radius: 10px;" id="progress_wrapper">
                                    <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%"></div>
                                </div>
                                <small id="loaded_n_total" class="text-muted small"></small>
                            </div>
                            
                            <?php if (empty($p_siswa)): ?>
                                <button type="button" 
                                        class="btn btn-primary btn-block" 
                                        data-toggle="modal" 
                                        data-target="#gantiPassModal">
                                    Ganti Password
                                </button>
                            <?php else: ?>
                                <div class="alert alert-info py-2 mb-0">
                                    <small><i class="fas fa-info-circle"></i> Password Anda adalah <b>NISN</b> atau <b>NIS</b> Anda.</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- About Me Card -->
                    <div class="card shadow-sm border-0">
                        <div class="card-header box-shadow-0 bg-gradient-x-info">
                            <h5 class="card-title text-white"><?php echo !empty($p_siswa) ? 'Identitas Siswa' : 'About Me'; ?></h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($p_siswa)): ?>
                                <!-- Student View -->
                                <strong><i class="fas fa-id-card mr-1"></i> NIS / NISN</strong>
                                <p class="text-muted"><?php echo $p_siswa['nis'] . ' / ' . ($p_siswa['nisn'] ?? '-'); ?></p>
                                <hr>
                                
                                <strong><i class="fas fa-user mr-1"></i> Jenis Kelamin</strong>
                                <p class="text-muted"><?php echo ($p_siswa['jk'] == 'L' ? 'Laki-laki' : ($p_siswa['jk'] == 'P' ? 'Perempuan' : '-')); ?></p>
                                <hr>
                                
                                <strong><i class="fas fa-graduation-cap mr-1"></i> Kelas</strong>
                                <p class="text-muted"><?php echo $p_siswa['kelas'] ?? '-'; ?></p>
                                <hr>
                                
                                <strong><i class="fas fa-clock mr-1"></i> Terakhir Login</strong>
                                <p class="text-muted"><?php echo $log ?? '-'; ?></p>
                            <?php else: ?>
                                <!-- Admin View -->
                                <strong><i class="fas fa-book mr-1"></i> Nama</strong>
                                <p class="text-muted"><?php echo !empty($nama) ? $nama : '-'; ?></p>
                                <hr>
                                
                                <strong><i class="fas fa-file-alt mr-1"></i> User Id</strong>
                                <p class="text-muted"><?php echo !empty($nuser) ? $nuser : '-'; ?></p>
                                <hr>
                                
                                <strong><i class="fas fa-pencil-alt mr-1"></i> Level</strong>
                                <p class="text-muted">
                                    <?php
                                    if (isset($lv)) {
                                        if ($lv == "1") echo "<label>Admin</label>";
                                        elseif ($lv == "2") echo "<label>User</label>";
                                        elseif ($lv == "3") echo "<label>Staff</label>";
                                    }
                                    ?>
                                </p>
                                <hr>
                                
                                <strong><i class="far fa-file-alt mr-1"></i> Log</strong>
                                <p class="text-muted"><?php echo !empty($log) ? $log : '-'; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column -->
                <div class="col-md-9">
                    <?php if (!empty($p_siswa)): ?>
                        <!-- Student Specific: Academic Summary -->
                        <div class="card shadow-sm border-0">
                            <div class="card-header box-shadow-0 bg-gradient-x-info">
                                <h5 class="card-title text-white"><i class="fas fa-chart-line mr-2"></i> Ringkasan Akademik</h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center mt-3">
                                    <div class="col-sm-4 border-right">
                                        <?php
                                        $s_nama = mysqli_real_escape_string($sqlconn, $nama);
                                        $s_kelas = mysqli_real_escape_string($sqlconn, $p_siswa['kelas'] ?? '');
                                        $qp = mysqli_query($sqlconn, "SELECT COUNT(*) as total FROM prestasi WHERE pd = '$s_nama' AND kelas = '$s_kelas'");
                                        $rp = mysqli_fetch_assoc($qp);
                                        $total_prestasi = $rp['total'] ?? 0;
                                        ?>
                                        <div class="description-block">
                                            <h5 class="description-header text-primary" style="font-size: 2rem;"><?php echo $total_prestasi; ?></h5>
                                            <span class="description-text">TOTAL PRESTASI</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 border-right">
                                        <?php
                                        $ql = mysqli_query($sqlconn, "SELECT COUNT(*) as total FROM legalisir WHERE pembuat = '$s_nama'");
                                        $rl = mysqli_fetch_assoc($ql);
                                        $total_legalisir = $rl['total'] ?? 0;
                                        ?>
                                        <div class="description-block">
                                            <h5 class="description-header text-success" style="font-size: 2rem;"><?php echo $total_legalisir; ?></h5>
                                            <span class="description-text">TOTAL LEGALISIR</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="description-block">
                                            <h5 class="description-header text-info" style="font-size: 2rem;"><?php echo $p_siswa['kelas'] ?? '-'; ?></h5>
                                            <span class="description-text">KELAS AKTIF</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-5">
                                    <h6 class="font-weight-bold mb-3"><i class="fas fa-info-circle mr-1"></i> Informasi Akun</h6>
                                    <div class="alert alert-light border">
                                        <p class="mb-1"><strong>Username:</strong> <?php echo $p_siswa['nis']; ?></p>
                                        <p class="mb-0"><strong>Status Akun:</strong> <span class="badge badge-success">Terverifikasi</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Admin/Staff Level Info -->
                        <div class="card shadow-sm border-0">
                            <div class="card-header box-shadow-0 bg-gradient-x-info p-2">
                                <ul class="nav nav-pills">
                                    <li class="nav-item">
                                        <a class="nav-link active" href="#activity" data-toggle="tab">Staff LV</a>
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="card-body">
                                <div class="tab-content">
                                    <div class="tab-pane active" id="activity">
                                        <div class="post">
                                            <div class="user-block">
                                                <?php if (isset($lv)) {
                                                    if ($lv == "1") { ?>
                                                        <span class="username">Administrator</span><br>
                                                        <span class="description">Super Admin</span>
                                                    <?php } else if ($lv == "2") { ?>
                                                        <span class="username">Staff</span><br>
                                                        <span class="description">Staff User</span>
                                                    <?php } 
                                                } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal: Change Password -->
<div class="modal fade" id="gantiPassModal" tabindex="-1" aria-labelledby="gantiPassModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0">
            <div class="modal-header box-shadow-0 bg-gradient-x-info">
                <h5 class="modal-title text-white">Ganti Password Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted text-center small mb-4">Password baru harus minimal 6 karakter.</p>
                <form id="formGantiPass">
                    <div class="mb-3">
                        <label for="passL" class="form-label">Password Lama</label>
                        <input type="password" class="form-control" id="passL" name="passL" required>
                    </div>
                    <div class="mb-3">
                        <label for="passB" class="form-label">Password Baru</label>
                        <input type="password" class="form-control" id="passB" name="passB" required>
                        <!-- Password Strength Indicator -->
                        <div class="password-strength-meter">
                            <div class="password-strength-bar" id="strengthBar"></div>
                        </div>
                        <div class="password-strength-text" id="strengthText"></div>
                    </div>
                    <div class="mb-3">
                        <label for="passK" class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control" id="passK" name="passK" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-flat" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary btn-flat" id="btnSimpanPass">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Notification -->
<div class="modal fade" id="notifModal" tabindex="-1" aria-labelledby="notifModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" id="notifModalHeader">
                <h6 class="modal-title">Notifikasi</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="notifMessage">
                <!-- Message will be inserted here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-flat" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
$(document).ready(function() {
    // ==========================================
    // NOTIFICATION MODAL FUNCTION
    // ==========================================
    function showNotif(type, message) {
        const modal = $('#notifModal');
        const header = $('#notifModalHeader');
        const messageEl = $('#notifMessage');
        
        // Reset classes
        header.removeClass('bg-success bg-danger bg-warning bg-info text-white');
        
        // Set style based on type
        if (type === 'success') {
            header.addClass('bg-success text-white');
        } else if (type === 'error') {
            header.addClass('bg-danger text-white');
        } else if (type === 'warning') {
            header.addClass('bg-warning text-white');
        } else {
            header.addClass('bg-info text-white');
        }
        
        messageEl.html(message);
        modal.modal('show');
    }

    // ==========================================
    // PASSWORD STRENGTH CALCULATOR
    // ==========================================
    function calculatePasswordStrength(password) {
        let strength = 0;
        
        if (password.length === 0) {
            return { score: 0, text: '', className: '' };
        }
        
        // Length checks
        if (password.length >= 6) strength += 1;
        if (password.length >= 8) strength += 1;
        if (password.length >= 12) strength += 1;
        
        // Character variety checks
        if (/[a-z]/.test(password)) strength += 1; // lowercase
        if (/[A-Z]/.test(password)) strength += 1; // uppercase
        if (/[0-9]/.test(password)) strength += 1; // numbers
        if (/[^a-zA-Z0-9]/.test(password)) strength += 1; // special chars
        
        // Determine strength level
        if (strength <= 2) {
            return { score: 1, text: 'Lemah', className: 'strength-weak text-weak' };
        } else if (strength <= 4) {
            return { score: 2, text: 'Sedang', className: 'strength-medium text-medium' };
        } else {
            return { score: 3, text: 'Kuat', className: 'strength-strong text-strong' };
        }
    }

    // ==========================================
    // PASSWORD STRENGTH INDICATOR
    // ==========================================
    $('#passB').on('input', function() {
        const password = $(this).val();
        const strength = calculatePasswordStrength(password);
        const strengthBar = $('#strengthBar');
        const strengthText = $('#strengthText');
        
        // Reset classes
        strengthBar.removeClass('strength-weak strength-medium strength-strong');
        strengthText.removeClass('text-weak text-medium text-strong');
        
        if (password.length === 0) {
            strengthBar.css('width', '0%');
            strengthText.text('');
        } else {
            strengthBar.addClass(strength.className.split(' ')[0]);
            strengthText.addClass(strength.className.split(' ')[1]);
            strengthText.html('<i class="fas fa-shield-alt"></i> Kekuatan: ' + strength.text);
        }
    });

    // ==========================================
    // CHANGE PASSWORD HANDLER
    // ==========================================
    $('#btnSimpanPass').click(function() {  
        const passL = $('#passL').val();
        const passB = $('#passB').val();
        const passK = $('#passK').val();

        // Initialize Toastr Options
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": false,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };
        
        // Validation with Toastr
        if (passL === '' || passB === '' || passK === '') {
            toastr.error('Harap isi semua kolom password!');
            return;
        }

        if (passB.length < 6) {
            toastr.warning('Password baru minimal 6 karakter');
            return;
        }

        if (passB !== passK) {
            toastr.error('Konfirmasi password tidak cocok');
            return;
        }

        // Send AJAX request
        $.ajax({
            url: 'post/gantipass.php',
            type: 'POST',
            data: {
                passL: passL,
                passB: passB,
                passK: passK
            },
            success: function(response) {
                response = response.trim();
                
                if (response === 'SUCCESS') {
                    toastr.success('Password berhasil diubah!');
                    $('#gantiPassModal').modal('hide');
                    $('#formGantiPass')[0].reset();
                } else if (response === '1') {
                    toastr.warning('Password lama harus diisi');
                } else if (response === '2') {
                    toastr.warning('Password baru harus diisi');
                } else if (response === '3') {
                    toastr.warning('Password konfirmasi harus diisi');
                } else if (response === '4') {
                    toastr.error('Konfirmasi password tidak cocok');
                } else if (response === '5') {
                    toastr.warning('Password minimal 6 karakter');
                } else if (response === '6') {
                    toastr.error('Password lama salah!');
                } else if (response.indexOf('ERROR') !== -1) {
                    toastr.error(response);
                } else {
                    toastr.error('Terjadi kesalahan: ' + response);
                }
            },
            error: function() {
                toastr.error('Terjadi kesalahan koneksi server');
            }
        });
    });

    // ==========================================
    // RESET PASSWORD STRENGTH ON MODAL CLOSE
    // ==========================================
    $('#gantiPassModal').on('hidden.bs.modal', function() {
        $('#formGantiPass')[0].reset();
        $('#strengthBar').removeClass('strength-weak strength-medium strength-strong').css('width', '0%');
        $('#strengthText').removeClass('text-weak text-medium text-strong').text('');
    });

    // ==========================================
    // PHOTO UPLOAD HANDLER
    // ==========================================
    window.uploadFile = function() {
        const fileInput = document.getElementById("photo1");
        const file = fileInput.files[0];
        if (!file) return;

        // Visual feedback: Start
        $("#progress_wrapper").show();
        $("#status").html("<span class='text-muted small'>Processing...</span>");

        const formdata = new FormData();
        formdata.append("photo1", file);

        const ajax = new XMLHttpRequest();
        
        ajax.upload.addEventListener("progress", function(e) {
            if (e.lengthComputable) {
                const percent = (e.loaded / e.total) * 100;
                $("#progressBar").css("width", Math.round(percent) + "%");
                $("#status").html("<span class='text-primary small'>Uploading: " + Math.round(percent) + "%</span>");
                $("#loaded_n_total").html((e.loaded / 1024).toFixed(1) + " / " + (e.total / 1024).toFixed(1) + " KB");
            }
        }, false);

        ajax.addEventListener("load", function(e) {
            const response = e.target.responseText.trim();
            console.log("Upload Response:", response);
            if (response.startsWith("SUCCESS|")) {
                const newImgUrl = response.split("|")[1];
                
                // Update UI
                $("#exisImage").html("<img src='" + newImgUrl + "' alt='Photo'>");
                $("#status").html("<span class='text-success small'>Berhasil Dikirim!</span>");
                toastr.success("Profil foto berhasil diperbarui.");
                
                // Hide progress after success
                setTimeout(() => {
                    $("#progress_wrapper").fadeOut();
                    $("#loaded_n_total").html("");
                    $("#status").html("");
                    $(".custom-file-label").text("Ganti Foto...");
                }, 2000);
            } else {
                const errorMsg = response.includes("ERROR:") ? response.split("ERROR:")[1] : "Gagal mengupload.";
                $("#status").html("<span class='text-danger small'>" + errorMsg + "</span>");
                toastr.error(errorMsg);
                $("#progressBar").addClass("bg-danger");
            }
        }, false);

        ajax.addEventListener("error", function() {
            $("#status").html("<span class='text-danger small'>Server Error</span>");
            toastr.error("Gagal terhubung ke server.");
        }, false);

        ajax.open("POST", "post/photo.php");
        ajax.send(formdata);
    };

});
</script>
