<?php
// Catatan: Asumsikan cfg/konek.php dan cfg/secure.php sudah terinclude.
include "cfg/konek.php";
include "cfg/secure.php";

// --- VALIDASI KONEKSI DATABASE ---
// Jika koneksi (asumsi variabel $sqlconn dari konek.php) gagal, hentikan eksekusi
if (!isset($sqlconn) || $sqlconn === false) {
    echo '<div class="alert alert-danger"><strong>Error:</strong> Koneksi database ($sqlconn) belum diinisialisasi atau gagal. Pastikan file cfg/konek.php berfungsi dengan benar.</div>';
    exit();
}

// --- FUNGSI BANTUAN ---
// Fungsi generate string random (dipindahkan ke atas untuk akses global)
function random_strings($length_of_string) {
    $str_result = '0123456789abcdefghijklmnopqrstuvwxyz';
    return substr(str_shuffle($str_result), 0, $length_of_string);
}

// Pastikan $lv terisi (dari secure.php)
$lv = isset($level) ? $level : '';

// --- FUNGSI MENAMPILKAN MODAL PESAN KESALAHAN/SUKSES (PENGGANTI ALERT) ---
function display_notification($type, $message) {
    // Escape message for JS
    $safe_message = addslashes($message);
    
    // Determine redirection URL
    $redirect = "?modul=user";
    
    // Check if we are already on the correct module (to avoid loops or unnecessary redirects)
    $current_modul = isset($_REQUEST['modul']) ? $_REQUEST['modul'] : '';
    
    // Output JS alert and redirect
    if ($current_modul !== 'user') {
        echo "<script>alert('$safe_message'); location.href = '$redirect';</script>";
    } else {
        echo "<script>alert('$safe_message'); location.href = '$redirect';</script>";
    }
}

// --- AKSI: UPDATE STATUS (Aktif/Non-Aktif) ---
if (isset($_REQUEST['aksi1'])) {
    $id_req = mysqli_real_escape_string($sqlconn, $_REQUEST['id']);
    
    // Menggunakan $id_req alih-alih $id
    $sqlcek = mysqli_query($sqlconn, "SELECT status FROM usera WHERE id = '$id_req'");
    if ($sta = mysqli_fetch_array($sqlcek)) {
        $status = $sta['status'];
        $ubah = ($status == "1") ? "0" : "1";
        mysqli_query($sqlconn, "UPDATE usera SET status = '$ubah' WHERE id = '$id_req'");
        $st_text = ($ubah == "1") ? "Aktif" : "Non-Aktif";
        write_log("EDIT", "Mengubah status user ID: $id_req menjadi $st_text");
        display_notification('success', 'Status user berhasil diubah.');
    } else {
        display_notification('danger', 'Gagal menemukan user.');
    }
}

// --- AKSI: RESET PASSWORD ---
if (isset($_REQUEST['resetp'])) {
    $id_req = mysqli_real_escape_string($sqlconn, $_REQUEST['id']);
    $default_pass = 'smpn171**';
    
    // Hash password default
    $pass = password_hash($default_pass, PASSWORD_DEFAULT);
    
    // Menggunakan $id_req alih-alih $id
    if(mysqli_query($sqlconn, "UPDATE usera SET password = '$pass' WHERE id = '$id_req'")){
        write_log("RESET", "Reset password user ID: $id_req");
        display_notification('success', 'Password berhasil direset menjadi: ' . $default_pass); 
    } else {
        display_notification('danger', 'Gagal mereset password: ' . mysqli_error($sqlconn));
    }
}

// --- AKSI: TAMBAH user ---
if (isset($_POST['tambah'])) {
    $userid = mysqli_real_escape_string($sqlconn, $_POST['userid']);
    $nama     = mysqli_real_escape_string($sqlconn, $_POST['nama']);
    // NIK diizinkan kosong karena di DB adalah DEFAULT NULL
    $nik      = !empty($_POST['nik']) ? mysqli_real_escape_string($sqlconn, $_POST['nik']) : NULL;
    
    $lv_user  = mysqli_real_escape_string($sqlconn, $_POST['level']);
    $status   = mysqli_real_escape_string($sqlconn, $_POST['status']);
    
    // Ambil password dari form, jika kosong gunakan default
    $raw_pass = !empty($_POST['password']) ? $_POST['password'] : 'smpn171**';

    // Data yang wajib diisi (idu dan ip)
    $idu_user = random_strings(10); 
    $ip_user = isset($_SERVER['REMOTE_ADDR']) ? mysqli_real_escape_string($sqlconn, $_SERVER['REMOTE_ADDR']) : '';

    // Cek username ganda
    $cek_user = mysqli_query($sqlconn, "SELECT id FROM usera WHERE userid = '$userid'");
    $sqlcek = mysqli_num_rows($cek_user);
    
    if ($sqlcek > 0) {
        display_notification('danger', 'user ID SUDAH ADA, silakan ganti yang lain.');
    } elseif (empty($userid) || empty($raw_pass)) {
        display_notification('danger', 'user ID dan Password Harus diisi!');
    } else {
        // Enkripsi Password
        $password = password_hash($raw_pass, PASSWORD_DEFAULT);
        
        // Perbaiki penanganan nilai NULL untuk NIK
        $nik_value = is_null($nik) ? 'NULL' : "'$nik'";

        // QUERY: Menambahkan kolom 'ip' dan 'idu' untuk memenuhi batasan NOT NULL
        $sql = "INSERT INTO usera (userid, password, nama, nik, level, status, ip, idu) 
                VALUES ('$userid', '$password', '$nama', $nik_value, '$lv_user', '$status', '$ip_user', '$idu_user')";
                
        if (mysqli_query($sqlconn, $sql)) {
            write_log("ADD", "Menambah user baru: $userid ($nama)");
            display_notification('success', 'user Berhasil Ditambahkan');
        } else {
            // Tampilkan error database yang sebenarnya
            $db_error = mysqli_error($sqlconn);
            $error_message = empty($db_error) 
                ? "Gagal menjalankan query INSERT. (Kemungkinan nilai kosong/panjang tidak sesuai/hak akses)." 
                : "Error Database: " . $db_error . ". Query Gagal: " . $sql; // Ditampilkan Query lengkap
                
            display_notification('danger', $error_message);
        }
    }
}

// --- AKSI: UPDATE user (Simpan Edit) ---
if (isset($_POST['simpan'])) {
    $id_req   = mysqli_real_escape_string($sqlconn, $_POST['id']);
    $userid = mysqli_real_escape_string($sqlconn, $_POST['userid']);
    $nama     = mysqli_real_escape_string($sqlconn, $_POST['nama']);
    // NIK diizinkan kosong
    $nik      = !empty($_POST['nik']) ? mysqli_real_escape_string($sqlconn, $_POST['nik']) : NULL;
    
    $level_in = mysqli_real_escape_string($sqlconn, $_POST['level']);
    $status   = mysqli_real_escape_string($sqlconn, $_POST['status']);
    
    // Cek apakah password diisi (jika kosong, jangan update password)
    $raw_pass = isset($_POST['password']) ? $_POST['password'] : '';
    $update_pass_query = "";
    
    if (!empty($raw_pass)) {
        $password = password_hash($raw_pass, PASSWORD_DEFAULT);
        $update_pass_query = ", password = '$password'";
    }

    $nik_value = is_null($nik) ? 'NULL' : "'$nik'";

    $sql = "UPDATE usera SET 
            userid = '$userid', 
            nama = '$nama', 
            nik = $nik_value, 
            level = '$level_in', 
            status = '$status' 
            $update_pass_query
            WHERE id = '$id_req'";

    if (mysqli_query($sqlconn, $sql)) {
         write_log("EDIT", "Update data user ID: $id_req ($userid)");
         display_notification('success', 'Data user Berhasil Diupdate');
    } else {
         display_notification('danger', 'Error Update: ' . mysqli_error($sqlconn) . " (Query: " . $sql . ")");
    }
}

// --- AKSI: HAPUS user ---
if (isset($_REQUEST['aksi']) && $_REQUEST['aksi'] == 'hapus') {
    $id_req = mysqli_real_escape_string($sqlconn, $_REQUEST['urut']);

    // Hapus file foto jika ada
    $cek = mysqli_query($sqlconn, "SELECT poto FROM usera WHERE id = '$id_req'");
    if ($cek1 = mysqli_fetch_array($cek)) {
        // Periksa apakah ada nama file poto dan apakah file tersebut ada
        if (!empty($cek1['poto']) && file_exists("up/profil/" . $cek1['poto'])) {
            unlink("up/profil/" . $cek1['poto']);
        }
    }

    if(mysqli_query($sqlconn, "DELETE FROM usera WHERE id = '$id_req'")) {
        write_log("DELETE", "Menghapus user ID: $id_req");
        display_notification('success', 'Data user berhasil dihapus!');
    } else {
        display_notification('danger', 'Gagal menghapus data: ' . mysqli_error($sqlconn));
    }
}
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Header Konten -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Management user</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active">Management user</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Style DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/responsive.bootstrap4.css">
    <!-- Konten Utama -->
    <section class="content">
        <div class="row">
            <div class="col-12">
                <div class="card table-responsive">
                    <div class="card-header bg-menu-gradient">
                        <?php if ($lv == "1") { ?>
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary btn-flat btn-sm" data-toggle="modal" data-target="#gTam">
                                    <i class="fa fa-plus-circle"></i> &nbsp; Tambah user
                                </button>
                                <a href="file/down_excel_user.php" target="_blank" class="btn btn-primary btn-flat btn-sm">
                                    <i class="fas fa-download"></i> &nbsp;Download Data
                                </a>
                                <a href="?modul=Upload_user" class="btn btn-primary btn-flat btn-sm">
                                    <i class="fas fa-upload"></i> &nbsp;Upload Data user
                                </a>
                            </div>
                        <?php } ?>
                    </div>
                    
                    <div class="card-body">
                        <table width="100%" id="example2" class="table table-striped table-hover table-sm">
                            <thead>
                                <tr align="center">
                                    <th width="2%">No</th>
                                    <th width="3%">user ID</th>
                                    <th width="5%">Nama</th>
                                    <th width="5%">NRK/NIKKI</th>
                                    <th width="3%">Level</th>
                                    <th width="10%">Last Login</th>
                                    <th width="5%">IP</th>
                                    <th width="3%">Status</th>
                                    <th width="5%">Rst Pass</th>
                                    <th width="3%">Edit</th>
                                    <th width="3%">Hapus</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = mysqli_query($sqlconn, "SELECT * FROM usera ORDER BY id ASC"); 
                                $no = 0;
                                while ($s = mysqli_fetch_array($sql)) {
                                    $stts = $s['status'];
                                    $login = $s['level'];
                                    $no++;
                                ?>

                                    <tr>
                                        <td align="center"><?php echo $no; ?></td>
                                        <td><?php echo htmlspecialchars($s['userid']); ?></td>
                                        <td><?php echo htmlspecialchars($s['nama']); ?></td>
                                        <td><?php echo htmlspecialchars($s['nik']); ?></td>
                                        <td align="center">
                                            <?php
                                            if ($login == "1") {
                                                echo "<span class='badge bg-menu-gradient'>Admin</span>";
                                            } elseif ($login == "2") {
                                                echo "<span class='badge bg-menu-gradient'>Staff</span>";
                                            } else {
                                                echo "<span class='badge bg-menu-gradient'>user</span>";
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo $s['lastlogin']; ?></td>
                                        <td><?php echo $s['ip']; ?></td>
                                        
                                        <!-- Tombol Status -->
                                        <td align="center">
                                            <?php if ($stts == "1") { ?>
                                                <button type="button" class="btn btn-sm btn-success" onclick="toggleStatus(<?php echo $s['id']; ?>, 1)" title="Click to deactivate">
                                                    <i class="fas fa-toggle-on"></i> Active
                                                </button>
                                            <?php } else { ?>
                                                <button type="button" class="btn btn-sm btn-danger" onclick="toggleStatus(<?php echo $s['id']; ?>, 0)" title="Click to activate">
                                                    <i class="fas fa-toggle-off"></i> Inactive
                                                </button>
                                            <?php } ?>
                                        </td>
                                        
                                        <!-- Tombol Reset Password -->
                                        <td align="center">
                                            <a href="?modul=user&resetp=reset&id=<?php echo $s['id']; ?>" onclick="if (confirm('Reset password menjadi = smpn171**?')) { window.location.href='?modul=user&resetp=reset&id=<?php echo $s['id']; ?>'; } return false;">
                                                <button type="button" class="btn btn-danger btn-sm btn-flat" title="Default Password = smpn171**"><i class="fa fa-key"></i></button>
                                            </a>
                                        </td>

                                        <!-- Tombol Edit -->
                                        <td align="center">
                                            <a href='#myEdit' data-toggle='modal' data-id='<?php echo $s['id']; ?>'>
                                                <button type="button" class="btn btn-info btn-sm btn-flat"><i class="fa fa-edit"></i></button>
                                            </a>
                                        </td>
                                        
                                        <!-- Tombol Hapus -->
                                        <td align="center">
                                            <?php if ($lv != '1') { ?>
                                                <button type="button" class="btn btn-danger btn-sm btn-flat" onclick="alert('Akses Ditolak. Hubungi Admin.');"><i class="fa fa-trash"></i></button>
                                            <?php } else { ?>
                                                <a href="?modul=user&aksi=hapus&urut=<?php echo $s['id']; ?>" onclick="if (confirm('Yakin ingin menghapus user ini?')) { window.location.href='?modul=user&aksi=hapus&urut=<?php echo $s['id']; ?>'; } return false;">
                                                    <button type="button" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-trash"></i></button>
                                                </a>
                                            <?php } ?>
                                        </td>
                                    </tr>

                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- MODAL TAMBAH user -->
<div class="modal fade" id="gTam" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-menu-gradient">
                <h6 class="modal-title w-100 text-center"><i class="fa fa-user-plus"></i> Tambah Data user</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <!-- Form Start -->
            <form action="?modul=user" method="post">
                <div class="modal-body">
                    <input type="hidden" name="tambah" value="yes">
                    
                    <!-- Row 1: Level user & username -->
                    <div class="row">
                        <!-- Level user -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-user-shield"></i> Level user</label>
                                <select class="form-control select2 class-level" name="level" required style="width: 100%;">
                                    <option value="">Pilih Level</option>
                                    <option value="1">Admin</option>
                                    <option value="2">Staf</option>
                                    <option value="3">user</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- username -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-user"></i> username</label>
                                <input type="text" class="form-control" name="userid" placeholder="username Login" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Row 2: Nama Lengkap & NIP/NIKI -->
                    <div class="row">
                        <!-- Nama Lengkap -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-id-card"></i> Nama Lengkap</label>
                                <input type="text" class="form-control" name="nama" placeholder="Nama Lengkap" required>
                            </div>
                        </div>
                        
                        <!-- NIP/NIKI/- -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-address-card"></i> NIP/NIKI/-</label>
                                <input type="text" class="form-control" name="nik" placeholder="NIP/NIKI/Strip">
                            </div>
                        </div>
                    </div>

                    <!-- Row 3: Status -->
                    <div class="row">
                        <!-- Status -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-toggle-on"></i> Status</label>
                                <div class="mt-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="status" id="status1" value="1" checked style="transform: scale(1.5); margin-right: 10px;">
                                        <label class="form-check-label" for="status1">Aktif</label>
                                    </div>
                                    <div class="form-check form-check-inline ml-3">
                                        <input class="form-check-input" type="radio" name="status" id="status0" value="0" style="transform: scale(1.5); margin-right: 10px;">
                                        <label class="form-check-label" for="status0">Non Aktif</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-flat" data-dismiss="modal"><i class="fa fa-close"></i> Tutup</button>
                    <button type="submit" class="btn btn-primary btn-flat"><i class="fa fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL EDIT user -->
<div class="modal fade" id="myEdit" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="fetched-data"></div>
            </div>
        </div>
    </div>
</div>

<!-- Script Handling -->
<script type="text/javascript">
    $(document).ready(function() {
        // Modal Edit Listener
        $('#myEdit').on('show.bs.modal', function(e) {
            var rowid = $(e.relatedTarget).data('id');
            // Fetch form edit via AJAX
            $.ajax({
                type: 'post',
                url: 'edit_usera.php', 
                data: 'urut=' + rowid,
                success: function(data) {
                    $('.fetched-data').html(data);
                },
                error: function() {
                    $('.fetched-data').html('<div class="alert alert-danger">Error: Gagal memuat form edit. Pastikan file edit_usera.php ada dan dapat diakses.</div>');
                }
            });
        });
    });

    // Toggle Status Function with AJAX
    function toggleStatus(userId, currentStatus) {
        // Show loading state on the button
        const button = event.target.closest('button');
        const originalHTML = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';

        console.log('Toggle Status Request:', { userId, currentStatus });

        // Send AJAX request to toggle status
        $.ajax({
            type: 'POST',
            url: 'toggle_user_status.php',
            data: {
                id: userId,
                current_status: currentStatus
            },
            dataType: 'json',
            success: function(response) {
                console.log('Success Response:', response);
                if (response.success) {
                    // Update button appearance based on new status
                    if (response.new_status == 1) {
                        button.className = 'btn btn-sm btn-success';
                        button.innerHTML = '<i class="fas fa-toggle-on"></i> Active';
                        button.setAttribute('onclick', 'toggleStatus(' + userId + ', 1)');
                        button.title = 'Click to deactivate';
                    } else {
                        button.className = 'btn btn-sm btn-danger';
                        button.innerHTML = '<i class="fas fa-toggle-off"></i> Inactive';
                        button.setAttribute('onclick', 'toggleStatus(' + userId + ', 0)');
                        button.title = 'Click to activate';
                    }
                    button.disabled = false;
                    
                    // Show success notification
                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message);
                    } else {
                        alert(response.message);
                    }
                } else {
                    // Restore original button state on error
                    button.innerHTML = originalHTML;
                    button.disabled = false;
                    
                    // Show error notification
                    if (typeof toastr !== 'undefined') {
                        toastr.error(response.message || 'Failed to update status');
                    } else {
                        alert(response.message || 'Failed to update status');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error Details:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: error
                });
                
                // Restore original button state on error
                button.innerHTML = originalHTML;
                button.disabled = false;
                
                // Show detailed error notification
                let errorMsg = 'Error: Unable to connect to server.';
                if (xhr.status === 403) {
                    errorMsg = 'Access Forbidden (403). Please check your session or login again.';
                } else if (xhr.status === 404) {
                    errorMsg = 'File not found (404). Please contact administrator.';
                } else if (xhr.status === 500) {
                    errorMsg = 'Server error (500). Please try again later.';
                }
                
                if (typeof toastr !== 'undefined') {
                    toastr.error(errorMsg);
                } else {
                    alert(errorMsg);
                }
            }
        });
    }
</script>

<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/responsive.bootstrap4.min.js"></script>

<script>
  $(document).ready(function () {
    $('#example2').DataTable({
      responsive: true,
      autoWidth: true
    });
  });
</script>