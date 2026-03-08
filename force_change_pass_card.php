<style>
    /* Animated gradient border for security alert */
    .security-gradient-alert {
        position: relative;
        background-color: transparent;
        padding: 17px;
        border-radius: 10px;
        margin-bottom: 20px;
        color: #ff0000;
        font-size: 14px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        z-index: 1;
    }

    .security-gradient-alert h5 {
        color: #ffc107;
        font-weight: bold;
        margin-bottom: 10px;
    }

    .security-gradient-alert strong {
        color: #ff0000;
        font-weight: bold;
    }

    .security-gradient-alert::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, #ff0000, #ff6600, #000000, #ff0000, #ff6600);
        background-size: 400% 400%;
        border-radius: 10px;
        z-index: -2;
        animation: gradientMove 5s ease infinite;
    }

    .security-gradient-alert::after {
        content: '';
        position: absolute;
        top: 2px;
        left: 2px;
        right: 2px;
        bottom: 2px;
        background-color: rgba(0, 0, 0, 0.9);
        border-radius: 8px;
        z-index: -1;
    }

    @keyframes gradientMove {
        0% {
            background-position: 0% 50%;
        }
        50% {
            background-position: 100% 50%;
        }
        100% {
            background-position: 0% 50%;
        }
    }
</style>

<div class="content-wrapper d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card card-danger card-outline w-50 shadow-lg">
        <div class="card-header text-center">
            <h1 class="card-title" style="float: none; font-size: 1.5rem;"><i class="fas fa-exclamation-triangle mr-2"></i><b>Change Password</b></h1>
        </div>
        <div class="card-body">
            <div class="security-gradient-alert">
                <h5><i class="icon fas fa-lock"></i> Keamanan Akun</h5>
                Anda terdeteksi menggunakan password default atau password yang tidak aman (sama dengan username). 
                <br>Demi keamanan data, Anda <strong>wajib</strong> mengganti password Anda sekarang sebelum dapat mengakses dashboard.
            </div>
            
            <form id="formForceChangePassCard">
                <!-- Hidden field for Old Password logic -->
                <?php 
                $valPassL = 'smpn171**';
                $default_pass = 'smpn171**';
                
                // Deterministic check for the old password to match backend verification
                if (isset($passworddb)) {
                    if (password_verify($default_pass, $passworddb) || $passworddb === md5($default_pass)) {
                        $valPassL = $default_pass;
                    } elseif (isset($user) && (password_verify($user, $passworddb) || $passworddb === md5($user))) {
                        $valPassL = $user;
                    }
                }
                ?>
                <input type="hidden" id="cardPassL" name="passL" value="<?php echo htmlspecialchars($valPassL); ?>">
                
                <div class="form-group">
                    <label for="cardPassB">Password Baru</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                        </div>
                        <input type="password" class="form-control" id="cardPassB" name="passB" required placeholder="Minimal 6 karakter">
                    </div>
                    <!-- Password Strength Indicator -->
                    <div class="password-strength-meter mt-2" style="height: 5px; background-color: #e9ecef; border-radius: 3px; overflow: hidden;">
                        <div class="password-strength-bar" id="cardStrengthBar" style="height: 100%; width: 0%; transition: all 0.3s ease;"></div>
                    </div>
                    <small class="password-strength-text" id="cardStrengthText"></small>
                </div>
                
                <div class="form-group">
                    <label for="cardPassK">Konfirmasi Password Baru</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                        </div>
                        <input type="password" class="form-control" id="cardPassK" name="passK" required placeholder="Ulangi password baru">
                    </div>
                    <small id="cardMatchText" class="mt-1 d-block" style="font-weight: bold;"></small>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <button type="button" class="btn btn-danger btn-block btn-lg" id="btnCardSimpanPass" disabled>
                            <i class="fas fa-save mr-2"></i> Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Toggle button state based on input
    function toggleCardSubmitButton() {
        const passB = $('#cardPassB').val().trim();
        const passK = $('#cardPassK').val().trim();
        const btn = $('#btnCardSimpanPass');
        
        if (passB !== '' && passK !== '') {
            btn.prop('disabled', false);
        } else {
            btn.prop('disabled', true);
        }
    }

    // Strength Meter Logic
    function calculateCardPasswordStrength(password) {
        let strength = 0;
        if (password.length === 0) return { score: 0, percentage: 0, text: '', color: '' };
        
        // 7-point scoring system
        if (password.length >= 6) strength += 1;
        if (password.length >= 8) strength += 1;
        if (password.length >= 12) strength += 1;
        if (/[a-z]/.test(password)) strength += 1;
        if (/[A-Z]/.test(password)) strength += 1;
        if (/[0-9]/.test(password)) strength += 1;
        if (/[^a-zA-Z0-9]/.test(password)) strength += 1;
        
        let color = '#dc3545'; // red
        let text = 'Lemah';
        let percentage = (strength / 7) * 100;

        if (strength === 7) {
            color = '#28a745'; // dark green
            text = 'Sangat Kuat';
            percentage = 100;
        } else if (strength >= 5) {
            color = '#94d82d'; // light green
            text = 'Kuat';
            percentage = 75;
        } else if (strength >= 3) {
            color = '#ffc107'; // yellow
            text = 'Sedang';
            percentage = 50;
        } else {
            color = '#dc3545'; // red
            text = 'Lemah';
            percentage = 25;
        }
        
        return { score: strength, percentage: percentage, text: text, color: color };
    }

    $('#cardPassB').on('input', function() {
        const password = $(this).val();
        const strength = calculateCardPasswordStrength(password);
        
        $('#cardStrengthBar').css('width', strength.percentage + '%').css('background-color', strength.color);
        $('#cardStrengthText').html((strength.text ? '<i class="fas fa-shield-alt mr-1"></i>' : '') + strength.text).css('color', strength.color).css('font-weight', 'bold');
        checkPasswordMatch();
        toggleCardSubmitButton();
    });

    function checkPasswordMatch() {
        const passB = $('#cardPassB').val();
        const passK = $('#cardPassK').val();
        const matchText = $('#cardMatchText');

        if (passK.length === 0) {
            matchText.text('').removeClass('text-success text-danger');
            return;
        }

        if (passB === passK) {
            matchText.html('<i class="fas fa-check-circle mr-1"></i> Password cocok').css('color', '#28a745').removeClass('text-danger').addClass('text-success');
        } else {
            matchText.html('<i class="fas fa-times-circle mr-1"></i> Password tidak cocok').css('color', '#dc3545').removeClass('text-success').addClass('text-danger');
        }
    }

    $('#cardPassK').on('input', function() {
        checkPasswordMatch();
        toggleCardSubmitButton();
    });

    // Save Handler
    $('#btnCardSimpanPass').click(function() {
        const passL = $('#cardPassL').val();
        const passB = $('#cardPassB').val();
        const passK = $('#cardPassK').val();
        
        // Client-side validation
        if (passB === passL) {
            toastr.error('Password baru tidak boleh sama dengan password lama/username!');
            return;
        }

        if (passB === 'smpn171**') {
             toastr.error('Password baru tidak boleh sama dengan default!');
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
                console.log(response); // Debug
                if (response === 'SUCCESS') {
                    // Success!
                    toastr.success('Password berhasil diubah. Mengalihkan ke halaman login...');
                    setTimeout(function() {
                        window.location.href = 'ceklogout.php';
                    }, 3000);
                } else if (response === '6') { 
                    toastr.error('Password lama salah/tidak valid. Silakan hubungi admin.');
                } else if (response.indexOf('ERROR') !== -1) {
                    toastr.error(response);
                } else {
                     const msgs = {
                        '1': 'Password lama kosong',
                        '2': 'Password baru kosong',
                        '3': 'Konfirmasi kosong',
                        '4': 'Konfirmasi tidak cocok',
                        '5': 'Password terlalu pendek'
                     };
                     toastr.error(msgs[response] || 'Terjadi kesalahan: ' + response);
                }
            },
            error: function() {
                toastr.error('Terjadi kesalahan koneksi server');
            }
        });
    });
});
</script>
