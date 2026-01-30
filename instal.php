<?php
ob_start(); // BUFFERING ON
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0); // Matikan display error agar tidak merusak JSON

// --- KONFIGURASI SISTEM ---
$current_year     = date('Y');
$source_sql_file  = 'cfg/db_dnet.sql';       // Lokasi file SQL master
$target_db        = 'dnet_ad' . $current_year;  // Nama database target
$config_file_path = 'cfg/konek.php';      // Lokasi file koneksi final
$sample_file_path = 'cfg/sampel_konek.php'; // Lokasi sample config
$old_db_name_ref  = 'db_dnet';                  // String di sample config yang akan direplace

// --- AJAX HANDLERS (Untuk Step 2: Progress Bar & Import SQL) ---
if (isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');

    $db_host = 'localhost';
    $db_user = $_POST['db_user'] ?? '';
    $db_pass = $_POST['db_pass'] ?? '';

    // 1. Cek Koneksi Awal
    $conn = @mysqli_connect($db_host, $db_user, $db_pass);
    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'Koneksi MySQL Gagal: ' . mysqli_connect_error()]);
        exit;
    }

    // ACTION: Inisialisasi DB & Baca File SQL
    if ($_POST['ajax_action'] == 'init_db') {
        if (!file_exists($source_sql_file)) {
            echo json_encode(['status' => 'error', 'message' => "File SQL sumber tidak ditemukan di: $source_sql_file"]);
            exit;
        }

        // Buat Database Target
        if (!mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS $target_db CHARACTER SET latin1 COLLATE latin1_general_ci")) {
            echo json_encode(['status' => 'error', 'message' => "Gagal membuat database target: " . mysqli_error($conn)]);
            exit;
        }

        $sql_content = file_get_contents($source_sql_file);
        $queries = explode(';', $sql_content); // Pecah SQL per perintah

        $valid_queries = [];
        foreach ($queries as $q) {
            $q = trim($q);
            if (!empty($q)) {
                $valid_queries[] = $q;
            }
        }

        $_SESSION['instal_sql_queries'] = $valid_queries;

        echo json_encode([
            'status' => 'success',
            'total_queries' => count($valid_queries),
            'message' => 'Database dibuat. Memulai import data...'
        ]);
        exit;
    }

    // ACTION: Jalankan Satu Query (Import Progress)
    if ($_POST['ajax_action'] == 'run_import_query') {
        $index = (int) $_POST['query_index'];

        if (!isset($_SESSION['instal_sql_queries'][$index])) {
            echo json_encode(['status' => 'error', 'message' => "Index query tidak valid."]);
            exit;
        }

        $sql = $_SESSION['instal_sql_queries'][$index];
        mysqli_select_db($conn, $target_db);

        if (mysqli_query($conn, $sql)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => "Error pada query #$index: " . mysqli_error($conn)]);
        }
        exit;
    }

    // ACTION: Buat File Config & Test Koneksi Akhir
    if ($_POST['ajax_action'] == 'finalize_config') {
        if (!file_exists($sample_file_path)) {
            echo json_encode(['status' => 'error', 'message' => "File sampel '$sample_file_path' tidak ditemukan."]);
            exit;
        }

        $sample_content = file_get_contents($sample_file_path);
        $new_content = str_replace('databaseuserid', $db_user, $sample_content);
        $new_content = str_replace('databasepassword', $db_pass, $new_content);
        $new_content = str_replace($old_db_name_ref, $target_db, $new_content);

        if (!is_dir(dirname($config_file_path)))
            mkdir(dirname($config_file_path), 0777, true);

        if (file_put_contents($config_file_path, $new_content)) {
            $test_conn = @mysqli_connect($db_host, $db_user, $db_pass, $target_db);

            if ($test_conn) {
                // Tambahkan ke tabel dbset (History database)
                // Pastikan tabel dbset ada (biasanya dari import SQL)
                // Jika tidak ada, create dulu
                mysqli_query($test_conn, "CREATE TABLE IF NOT EXISTS dbset (id int AUTO_INCREMENT PRIMARY KEY, nama_db varchar(100), tahun varchar(10), aktif enum('0','1'))");
                
                $q_dbset = "INSERT INTO dbset VALUES (NULL, '$target_db', '$current_year', '1')";
                mysqli_query($test_conn, $q_dbset);

                $_SESSION['db_user'] = $db_user;
                $_SESSION['db_pass'] = $db_pass;
                $_SESSION['db_name'] = $target_db;
                unset($_SESSION['instal_sql_queries']);

                echo json_encode(['status' => 'success', 'message' => 'Konfigurasi berhasil & Koneksi Teruji!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'File dibuat, tapi test koneksi ke database baru gagal.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menulis file konek.php. Cek permission.']);
        }
        exit;
    }
}

// --- LOGIKA FORM PHP (Step 3, 4, 5) ---
$step = isset($_GET['step']) ? (int) $_GET['step'] : 1;
$message = "";
$status_class = "";

// STEP 3: CEK PERMISSION & CHMOD
$folders = ['dist/img', 'up/profil', 'file/2025', 'file/fotopd','file/legalisir','file/usulan'];

if ($step == 3 && isset($_POST['fix_permission'])) {
    $all_success = true;
    foreach ($folders as $folder) {
        if (is_dir($folder)) {
            if (!chmod($folder, 0777)) $all_success = false;
        } else {
            if (!mkdir($folder, 0777, true)) $all_success = false;
        }
    }
    if ($all_success) {
        header("Location: ?step=4");
        exit();
    } else {
        $message = "Gagal merubah permission. Cek owner server.";
        $status_class = "alert-warning";
    }
}

// FUNGSI KONEKSI DATABASE
function getDBConnection()
{
    global $target_db; // Pastikan kita menggunakan variabel global target_db
    if (isset($_SESSION['db_user'])) {
        // Gunakan $target_db secara eksplisit untuk mencegah error "No database selected"
        return @mysqli_connect('localhost', $_SESSION['db_user'], $_SESSION['db_pass'], $target_db);
    }
    return false;
}

// STEP 4: BUAT USER ADMIN
if ($step == 4 && isset($_POST['create_admin'])) {
    $conn = getDBConnection();
    if ($conn) {
        $user = mysqli_real_escape_string($conn, $_POST['userid']);
        $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $nama = "Administrator";
        $level = "1";
        $ip = $_SERVER['REMOTE_ADDR'];

        // Kosongkan tabel admin lama (Asumsi tabel 'usera' benar)
        mysqli_query($conn, "TRUNCATE TABLE usera");

        // Data array untuk insert (sesuaikan jumlah kolom tabel usera)
        $data_to_insert = [
            "NULL", "'$user'", "'$pass'", "'$nama'", "'$email'", 
            "''", "'default.png'", "'1'", "'$ip'", "''", "NOW()", "'$level'"
        ];

        $sql = "INSERT INTO usera VALUES (" . implode(", ", $data_to_insert) . ")";
        if (mysqli_query($conn, $sql)) {
            header("Location: ?step=5");
            exit();
        } else {
            $message = "<strong>Gagal membuat user:</strong> " . mysqli_error($conn);
            $status_class = "alert-danger";
        }
    } else {
        $message = "Koneksi database terputus. Silahkan mulai dari awal.";
        $status_class = "alert-danger";
    }
}

// STEP 5: DATA SEKOLAH (SINKRONISASI SCHEMA)
if ($step == 5 && isset($_POST['save_profil'])) {
    $conn = getDBConnection();
    
    if (!$conn) {
        $message = "Gagal koneksi ke database target.";
        $status_class = "alert-danger";
    } else {
        $nsekolah = mysqli_real_escape_string($conn, $_POST['nsekolah']);
        $alamat   = mysqli_real_escape_string($conn, $_POST['alamat']);
        $kec      = mysqli_real_escape_string($conn, $_POST['kecamatan']);
        $kel      = mysqli_real_escape_string($conn, $_POST['kelurahan']);
        $kota     = mysqli_real_escape_string($conn, $_POST['kota']);
        $prov     = mysqli_real_escape_string($conn, $_POST['provinsi']);
        $pos      = mysqli_real_escape_string($conn, $_POST['kodepos']);
        $email    = mysqli_real_escape_string($conn, $_POST['email']);
        $web      = mysqli_real_escape_string($conn, $_POST['website']);
        $tlp      = mysqli_real_escape_string($conn, $_POST['telepon']);

        // 1. DROP TABEL LAMA (Bersihkan tabel profils dan profil)
        mysqli_query($conn, "DROP TABLE IF EXISTS profils");
        mysqli_query($conn, "DROP TABLE IF EXISTS profil"); // Hapus versi lama jika ada

        // 2. BUAT STRUKTUR TABEL BARU (Sesuai konek.php)
        // Perubahan: profil -> profils, nm_sekolah -> nsekolah, logo -> logo_sekolah, bg_login -> background_login
        $sql_create_table = "CREATE TABLE `profils` (
          `id_profil` int(11) NOT NULL AUTO_INCREMENT,
          `nsekolah` varchar(255) NOT NULL,
          `alamat` text NOT NULL,
          `kecamatan` varchar(100) NOT NULL,
          `kelurahan` varchar(100) NOT NULL,
          `provinsi` varchar(100) NOT NULL,
          `kabupaten` varchar(100) NOT NULL,
          `kodepos` varchar(20) NOT NULL,
          `no_telp` varchar(50) NOT NULL,
          `email` varchar(100) NOT NULL,
          `website` varchar(100) NOT NULL,
          `extra_1` varchar(100) DEFAULT '-',
          `extra_2` varchar(100) DEFAULT '-',
          `extra_3` varchar(100) DEFAULT '-',
          `extra_4` varchar(100) DEFAULT '-',
          `extra_5` varchar(100) DEFAULT '-',
          `extra_6` varchar(100) DEFAULT '-',
          `extra_7` varchar(100) DEFAULT '-',
          `extra_8` varchar(100) DEFAULT '-',
          `extra_9` varchar(100) DEFAULT '-',
          `extra_10` varchar(100) DEFAULT '-',
          `extra_11` varchar(100) DEFAULT '-',
          `extra_12` varchar(100) DEFAULT '-',
          `extra_13` varchar(100) DEFAULT '-',
          `extra_14` varchar(100) DEFAULT '-',
          `extra_15` varchar(100) DEFAULT '-',
          `logo_sekolah` varchar(255) DEFAULT 'logo_default.png',
          `background_login` varchar(255) DEFAULT 'bg_default.jpg',
          PRIMARY KEY (`id_profil`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci";

        if (mysqli_query($conn, $sql_create_table)) {
            
            // 3. MASUKKAN DATA BARU
            $sql_insert = "INSERT INTO profils VALUES (
                NULL, 
                '$nsekolah', '$alamat', '$kec', '$kel', '$prov', '$kota', 
                '$pos', '$tlp', '$email', '$web', 
                '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', 
                'logo_default.png', 'bg_default.jpg'
            )";

            if (mysqli_query($conn, $sql_insert)) {
                // Redirect ke login.php sesuai request
                header("Location: login.php");
                exit();
            } else {
                $message = "Tabel berhasil dibuat, tapi gagal menyimpan data: " . mysqli_error($conn);
                $status_class = "alert-danger";
            }

        } else {
            $message = "Gagal membuat tabel profils baru: " . mysqli_error($conn);
            $status_class = "alert-danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Instalasi Website</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
    body { background-color: #121212; }
    .installer-container {
        max_width: 800px; margin: 50px auto; background: white;
        padding: 40px; border-radius: 10px; box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    }
    .folder-check { font-family: monospace; }
    .status-ok { color: green; }
    .status-fail { color: red; }
    .text-muted { color: #6c757d; }
</style>
</head>
<body>

<div class="container">
    <div class="installer-container">

        <!-- HEADER -->
        <div class="text-center mb-4">
            <h2><i class="fas fa-server"></i> Setup Data Sekolah</h2>
            <p class="text-muted">Wizard Instalasi Otomatis</p>
        </div>

        <!-- PROGRESS BAR -->
        <div class="progress mb-4" style="height: 5px;">
            <div class="progress-bar" role="progressbar" style="width: <?php echo ($step / 6) * 100; ?>%"></div>
        </div>

        <!-- ALERT MESSAGE -->
        <?php if ($message): ?>
            <div class="alert <?php echo $status_class; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- STEP 1: LANDING -->
        <?php if ($step == 1): ?>
            <div class="text-center py-5">
                <h3>Selamat Datang</h3>
                <p class="lead">Instalasi Website Data Sekolah</p>
                <p>Proses ini akan mengkonfigurasi database, permission folder, dan akun administrator.</p>
                <hr>
                <a href="?step=2" class="btn btn-primary btn-lg px-5">Mulai Instalasi <i class="fas fa-arrow-right"></i></a>
            </div>
        <?php endif; ?>

        <!-- STEP 2: DB CONFIG & IMPORT -->
        <?php if ($step == 2): ?>
            <h4><i class="fas fa-database"></i> Konfigurasi Database</h4>
            <p>Masukkan kredensial MySQL. Sistem akan mengimport database ke <code><?php echo $target_db; ?></code>.</p>

            <div id="form-db">
                <div class="mb-3"><label class="form-label">Host</label><input type="text" class="form-control" value="localhost" disabled></div>
                <div class="mb-3"><label class="form-label">MySQL Username</label><input type="text" id="db_user" class="form-control" placeholder="root" required></div>
                <div class="mb-3"><label class="form-label">MySQL Password</label><input type="password" id="db_pass" class="form-control"></div>
                <div class="d-flex justify-content-between">
                    <a href="?step=1" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                    <button type="button" onclick="startinstalation()" id="btn-start" class="btn btn-primary">Test Koneksi & Import <i class="fas fa-file-import"></i></button>
                </div>
            </div>

            <div id="progress-area" style="display:none;" class="mt-4">
                <h5>Proses Instalasi:</h5>
                <p id="status-text" class="text-muted small">Menghubungkan...</p>
                <div class="progress mb-3" style="height: 25px;">
                    <div id="db-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">0%</div>
                </div>
                <div id="log-area" class="border p-2 bg-light small" style="height: 100px; overflow-y: auto; font-family: monospace;">
                    <div>> Menunggu proses dimulai...</div>
                </div>
                <div class="mt-3 text-end">
                    <a href="?step=3" id="btn-next-step" class="btn btn-success btn-lg" style="display:none;">Lanjut <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>

            <script>
                async function startinstalation() {
                    const dbUser = document.getElementById('db_user').value;
                    const dbPass = document.getElementById('db_pass').value;
                    const btnStart = document.getElementById('btn-start');
                    const progressArea = document.getElementById('progress-area');
                    const progressBar = document.getElementById('db-progress-bar');
                    const statusText = document.getElementById('status-text');
                    const logArea = document.getElementById('log-area');
                    const btnNext = document.getElementById('btn-next-step');

                    if (!dbUser) { alert("userid harus diisi!"); return; }
                    btnStart.disabled = true;
                    progressArea.style.display = 'block';

                    const log = (msg) => { logArea.innerHTML += `<div>> ${msg}</div>`; logArea.scrollTop = logArea.scrollHeight; };
                    const sendRequest = async (action, data = {}) => {
                        const formData = new FormData();
                        formData.append('ajax_action', action);
                        formData.append('db_user', dbUser);
                        formData.append('db_pass', dbPass);
                        for (const key in data) formData.append(key, data[key]);
                        const res = await fetch('instal.php', { method: 'POST', body: formData });
                        return await res.json();
                    };

                    try {
                        log("Cek koneksi & Buat DB...");
                        const initRes = await sendRequest('init_db');
                        if (initRes.status !== 'success') throw new Error(initRes.message);

                        const totalQueries = initRes.total_queries;
                        log(`Ditemukan ${totalQueries} query SQL.`);
                        
                        for (let i = 0; i < totalQueries; i++) {
                            statusText.innerText = `Mengimport Data (${i + 1}/${totalQueries})`;
                            await sendRequest('run_import_query', { query_index: i });
                            const percent = Math.round(((i + 1) / totalQueries) * 90);
                            progressBar.style.width = percent + '%';
                            progressBar.innerText = percent + '%';
                        }

                        log("Finalisasi konfigurasi...");
                        const configRes = await sendRequest('finalize_config');
                        if (configRes.status === 'success') {
                            progressBar.style.width = '100%'; progressBar.innerText = '100%';
                            progressBar.classList.remove('progress-bar-striped'); progressBar.classList.add('bg-success');
                            statusText.innerHTML = "<span class='text-success fw-bold'>Selesai!</span>";
                            btnNext.style.display = 'inline-block';
                        } else {
                            throw new Error(configRes.message);
                        }
                    } catch (err) {
                        progressBar.classList.add('bg-danger');
                        statusText.innerHTML = `<span class='text-danger fw-bold'>Error: ${err.message}</span>`;
                        btnStart.disabled = false;
                    }
                }
            </script>
        <?php endif; ?>

        <!-- STEP 3: PERMISSION -->
        <?php if ($step == 3): ?>
            <h4><i class="fas fa-folder-open"></i> Cek Permission Folder</h4>
            <div class="card mb-4"><ul class="list-group list-group-flush">
                <?php
                $all_ok = true;
                foreach ($folders as $folder):
                    $exists = file_exists($folder);
                    $writable = is_writable($folder);
                    $perm = $exists ? substr(sprintf('%o', fileperms($folder)), -4) : 'Not Found';
                    if (!$writable) $all_ok = false;
                ?>
                <li class="list-group-item d-flex justify-content-between align-items-center folder-check">
                    <span><i class="fas fa-folder text-warning"></i> <?php echo $folder; ?></span>
                    <span><span class="badge bg-secondary"><?php echo $perm; ?></span> <?php echo $writable ? '<i class="fas fa-check-circle status-ok"></i> OK' : '<i class="fas fa-times-circle status-fail"></i> Fail'; ?></span>
                </li>
                <?php endforeach; ?>
            </ul></div>
            <form method="POST">
                <div class="d-flex justify-content-between">
                    <a href="?step=2" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                    <div>
                        <?php if ($all_ok): ?>
                            <a href="?step=4" class="btn btn-success">Lanjut <i class="fas fa-arrow-right"></i></a>
                        <?php else: ?>
                            <button type="submit" name="fix_permission" class="btn btn-warning">Perbaiki Otomatis</button>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        <?php endif; ?>

        <!-- STEP 4: CREATE ADMIN -->
        <?php if ($step == 4): ?>
            <h4><i class="fas fa-user-shield"></i> Buat Akun Administrator</h4>
            <form method="POST">
                <div class="mb-3"><label class="form-label">User ID</label><input type="text" name="userid" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
                <div class="d-flex justify-content-between">
                    <a href="?step=3" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                    <button type="submit" name="create_admin" class="btn btn-primary">Simpan & Lanjut <i class="fas fa-save"></i></button>
                </div>
            </form>
        <?php endif; ?>

        <!-- STEP 5: PROFIL SEKOLAH -->
        <?php if ($step == 5): ?>
            <h4><i class="fas fa-school"></i> Data Identitas Sekolah</h4>
            <p>Data ini akan membuat tabel baru <code>profils</code>.</p>
            <form method="POST">
                <div class="mb-3"><label class="form-label">Nama Sekolah</label><input type="text" name="nsekolah" class="form-control" required placeholder="Contoh: SDN 01 PAGI"></div>
                <div class="row">
                    <div class="col-md-12 mb-3"><label class="form-label">Alamat Jalan</label><textarea name="alamat" class="form-control" rows="2"></textarea></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Kecamatan</label><input type="text" name="kecamatan" class="form-control"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Kelurahan</label><input type="text" name="kelurahan" class="form-control"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Kota/Kab</label><input type="text" name="kota" class="form-control"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Provinsi</label><input type="text" name="provinsi" class="form-control"></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Kode Pos</label><input type="text" name="kodepos" class="form-control"></div>
                    <div class="col-md-8 mb-3"><label class="form-label">Telepon</label><input type="text" name="telepon" class="form-control"></div>
                </div>
                <div class="mb-3"><label class="form-label">Email Sekolah</label><input type="email" name="email" class="form-control"></div>
                <div class="mb-3"><label class="form-label">Website</label><input type="text" name="website" class="form-control" placeholder="http://"></div>

                <div class="d-flex justify-content-between">
                    <a href="?step=4" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                    <button type="submit" name="save_profil" class="btn btn-primary">Simpan Profil & Selesai <i class="fas fa-check"></i></button>
                </div>
            </form>
        <?php endif; ?>

        <!-- END PAGE -->
        <?php if ($step == 6): ?>
            <div class="text-center py-5">
                <div class="mb-4"><i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i></div>
                <h3>Instalasi Selesai!</h3>
                <p class="lead">Website Data Sekolah Siap Digunakan.</p>
                <div class="alert alert-success mt-3">Database: <strong><?php echo $target_db; ?></strong> berhasil dikonfigurasi.</div>
                <hr>
                <a href="login.php" class="btn btn-success btn-lg px-5">Masuk ke Halaman Admin</a>
            </div>
        <?php endif; ?>

    </div>
    <div class="text-center text-muted mb-5"><small>&copy; <?php echo date('Y'); ?> Installer System</small></div>
</div>
</body>
</html>