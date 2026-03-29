<?php
include_once "cfg/konek.php";
include_once "cfg/secure.php";

// Hanya Admin (level 1) yang bisa akses
if (!isset($level) || $level != "1") {
    header("Location: management/usermanagement");
    exit();
}

// Proses tambah jika form disubmit
if (isset($_POST['tambah'])) {
    $userid = mysqli_real_escape_string($sqlconn, $_POST['userid']);
    $nama = mysqli_real_escape_string($sqlconn, $_POST['nama']);
    $nik = !empty($_POST['nik']) ? mysqli_real_escape_string($sqlconn, $_POST['nik']) : NULL;
    $lv_user = mysqli_real_escape_string($sqlconn, $_POST['level']);
    $status = mysqli_real_escape_string($sqlconn, $_POST['status']);
    $raw_pass = !empty($_POST['password']) ? $_POST['password'] : 'smpn171**';
    $idu_user = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyz'), 0, 10);
    $ip_user = mysqli_real_escape_string($sqlconn, $_SERVER['REMOTE_ADDR'] ?? '');

    // Cek username ganda
    $cek = mysqli_query($sqlconn, "SELECT id FROM usera WHERE userid = '$userid'");
    if (mysqli_num_rows($cek) > 0) {
        $_SESSION['toast_type'] = 'error';
        $_SESSION['toast_msg'] = 'User ID sudah ada, silakan gunakan yang lain.';
    } elseif (empty($userid)) {
        $_SESSION['toast_type'] = 'error';
        $_SESSION['toast_msg'] = 'User ID harus diisi!';
    } else {
        $password = password_hash($raw_pass, PASSWORD_DEFAULT);
        $nik_value = is_null($nik) ? 'NULL' : "'$nik'";
        $sql = "INSERT INTO usera (userid, password, nama, nik, level, status, ip, idu)
                VALUES ('$userid', '$password', '$nama', $nik_value, '$lv_user', '$status', '$ip_user', '$idu_user')";

        if (mysqli_query($sqlconn, $sql)) {
            write_log("ADD", "Menambah User: $nama");
            $_SESSION['toast_type'] = 'success';
            $_SESSION['toast_msg'] = 'User berhasil ditambahkan.';
            header("Location: management/usermanagement");
            exit();
        } else {
            $_SESSION['toast_type'] = 'error';
            $_SESSION['toast_msg'] = 'Gagal: ' . mysqli_error($sqlconn);
        }
    }
}
?>

<!-- Konten Utama -->
<section class="content">
    <div class="row">
        <!-- Form Tambah -->
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header box-shadow-0 bg-gradient-x-info">
                    <h5 class="card-title text-white">Form Tambah Data User</h5>
                    <br>
                    <br>
                    <a href="management/usermanagement" class="btn btn-warning btn-sm rounded-pill">
                        Kembali
                    </a>
                </div>

                <div class="card-body">
                    <form action="tambah_usera" method="post">
                        <input type="hidden" name="tambah" value="yes">

                        <!-- Level User -->
                        <div class="form-group row">
                            <!-- Nama Lengkap -->
                            <label class="col-sm-4 col-form-label">
                                <i class="fas fa-id-card"></i> Nama Lengkap
                            </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="nama" required>
                            </div>
                        </div>
                        <!-- User ID -->
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">
                                <i class="fas fa-user"></i> User ID
                            </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="userid" required>
                            </div>
                        </div>
                        <!-- NRK/NIKI/- -->
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">
                                <i class="fas fa-address-card"></i> NRK/NIKI/-
                            </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="nik">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">
                                <i class="fas fa-user-shield"></i> Level User
                            </label>
                            <div class="col-sm-8">
                                <select class="form-control" name="level" id="leveluser" style="width: 100%;">
                                    <option value="">Pilih Level</option>
                                    <option value="1">Admin</option>
                                    <option value="2">Staff</option>
                                    <option value="3">User</option>
                                </select>
                            </div>
                        </div>
                        <!-- Status -->
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">
                                <i class="fas fa-toggle-on"></i> Status
                            </label>
                            <div class="col-sm-8 d-flex align-items-center">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="status" id="status1" value="1"
                                        checked style="transform: scale(1.5); margin-right: 10px;">
                                    <label class="form-check-label" for="status1">Aktif</label>
                                </div>
                                <div class="form-check form-check-inline ml-3">
                                    <input class="form-check-input" type="radio" name="status" id="status0" value="0"
                                        style="transform: scale(1.5); margin-right: 10px;">
                                    <label class="form-check-label" for="status0">Non Aktif</label>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <button class="btn btn-outline-primary rounded-pill" type="submit">
                                Simpan
                            </button>
                        </div>
                </div>
                </form>
            </div>
        </div>
    </div>

    </div>
</section>

<script>
    $(function () {
        if (typeof $.fn.select2 !== 'undefined') {
            $("#leveluser").select2({
                placeholder: "Pilih Level User",
                allowClear: false,
                width: '100%',
                minimumResultsForSearch: 0
            });
        }
    });
</script>
