<?php
include_once "cfg/konek.php";
include_once "cfg/secure.php";

$user_role = $_SESSION['user_role'] ?? 'admin';
$skradm = $_SESSION['skradm'] ?? '';

if (!isset($_REQUEST['urut']) || empty($_REQUEST['urut'])) {
    if ($user_role === 'siswa') {
        $q_me = mysqli_query($sqlconn, "SELECT id FROM siswa WHERE nis = '$skradm'");
        $r_me = mysqli_fetch_assoc($q_me);
        $id = $r_me['id'] ?? '';
    } else {
        echo '<script>$(function() { toastr.warning("Pilih peserta didik terlebih dahulu."); setTimeout(function() { window.location.href = "dashboard"; }, 2000); });</script>';
        return;
    }
} else {
    $id = $_REQUEST['urut'];
    if ($user_role === 'siswa') {
        $q_me = mysqli_query($sqlconn, "SELECT id FROM siswa WHERE nis = '$skradm'");
        $r_me = mysqli_fetch_assoc($q_me);
        if ($id != $r_me['id']) {
            $id = $r_me['id'] ?? '';
        }
    }
}

// Ambil Informasi Siswa
$sqlSiswa = mysqli_query($sqlconn, "SELECT * FROM siswa WHERE id = '$id'");
$rSiswa = mysqli_fetch_array($sqlSiswa);

if (!$rSiswa) {
    echo '<script>$(function() { toastr.warning("Data siswa tidak ditemukan!"); setTimeout(function() { window.location.href = "' . ($user_role === 'admin' ? 'datasiswa' : 'dashboard') . '"; }, 2000); });</script>';
    return;
}

$nis = $rSiswa['nis'] ?? '';
$nama = $rSiswa['pd'] ?? '';
$kelas = $rSiswa['kelas'] ?? '';
$photo = $rSiswa['photo'] ?? '';

// Pembersihan data untuk query
$nis_clean = mysqli_real_escape_string($sqlconn, trim($nis));
$nama_clean = mysqli_real_escape_string($sqlconn, trim($nama));

// 1. Coba cari berdasarkan NIS (Metode Utama)
$query_nilai = "SELECT * FROM nilai WHERE TRIM(nis) = '$nis_clean'";
$sqlNilai = mysqli_query($sqlconn, $query_nilai);
$rNilai = mysqli_fetch_array($sqlNilai);

// 2. Jika gagal, coba cari berdasarkan Nama (Metode Cadangan)
if (!$rNilai && !empty($nama_clean) && $nama_clean !== '-') {
    $query_nilai_fallback = "SELECT * FROM nilai WHERE TRIM(pd) LIKE '%$nama_clean%'";
    $sqlNilai_fb = mysqli_query($sqlconn, $query_nilai_fallback);
    $rNilai = mysqli_fetch_array($sqlNilai_fb);
}

/* DEBUGGING MODE: 
Hapus komentar di bawah ini jika nilai masih tidak muncul untuk melihat apa yang salah 
*/
// echo "<!-- DEBUG: NIS: $nis_clean | Nama: $nama_clean | Hasil Query: " . ($rNilai ? 'Ditemukan' : 'Kosong') . " -->";
?>

<style>
    .table-grades th { vertical-align: middle !important; font-size: 12px; }
    .score-cell { font-size: 1.1rem; font-weight: 600; color: #2c3e50; }
    .profile-user-img { border: 3px solid #adb5bd; padding: 3px; }
    .bg-gradient-x-info { background: linear-gradient(90deg, #17a2b8 0%, #31d2f2 100%); }
    .bg-gradient-x-secondary { background: linear-gradient(90deg, #6c757d 0%, #adb5bd 100%); }
</style>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Profil -->
            <div class="col-md-3">
                <div class="card card-primary card-outline shadow-sm">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            <?php 
                            $img_path = '../file/fotopd/' . $photo;
                            if (!empty($photo) && file_exists($img_path)) { ?>
                                <img class="profile-user-img img-fluid img-circle" 
                                     style="width: 120px; height: 120px; object-fit: cover;"
                                     src="<?php echo $img_path; ?>" alt="Foto">
                            <?php } else { ?>
                                <img class="profile-user-img img-fluid img-circle" 
                                     style="width: 120px; height: 120px; object-fit: cover;" 
                                     src="../images/default.png" alt="Default">
                            <?php } ?>
                        </div>

                        <h3 class="profile-username text-center mt-3 text-bold"><?php echo htmlspecialchars($nama); ?></h3>
                        <p class="text-muted text-center"><i class="fas fa-id-card"></i> NIS: <?php echo htmlspecialchars($nis); ?></p>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b>Kelas</b> <a class="float-right badge bg-info"><?php echo htmlspecialchars($kelas); ?></a>
                            </li>
                            <li class="list-group-item">
                                <b>NISN</b> <a class="float-right text-dark"><?php echo htmlspecialchars($rSiswa['nisn'] ?? '-'); ?></a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Konten Nilai -->
            <div class="col-md-9">
                <div class="card shadow-sm">
                    <div class="card-header bg-gradient-x-secondary d-flex align-items-center">
                        <h5 class="card-title text-white mb-0"><i class="fas fa-chart-bar mr-2"></i> Rekapitulasi Nilai Akademik</h5>
                    </div>

                    <div class="card-body p-0">
                        <?php if ($rNilai): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered text-center m-0">
                                    <thead class="bg-gradient-x-secondary">
                                        <tr>
                                            <th colspan="6" class="bg-gradient-x-info text-white">Semester 1</th>
                                            <th colspan="6" class="bg-gradient-x-primary text-white">Semester 2</th>
                                            <th colspan="6" class="bg-gradient-x-danger text-white">Semester 3</th>
                                            <th colspan="6" class="bg-gradient-x-success text-white">Semester 4</th>
                                            <th colspan="6" class="bg-gradient-x-warning text-white">Semester 5</th>
                                        </tr>
                                        <tr class="text-sm">
                                            <?php for($i=0; $i<5; $i++): ?>
                                                <th>PKN</th><th>IND</th><th>MTK</th><th>IPA</th><th>IPS</th><th>ENG</th>
                                            <?php endfor; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <?php 
                                            $subjects = ['pkn', 'ind', 'mtk', 'ipa', 'ips', 'eng'];
                                            for ($s = 1; $s <= 5; $s++) {
                                                foreach ($subjects as $sub) {
                                                    $key = $sub . '_' . $s;
                                                    $val = $rNilai[$key] ?? '';
                                                    echo "<td class='score-cell'>" . ($val !== '' ? $val : '-') . "</td>";
                                                }
                                            }
                                            ?>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="py-5 text-center">
                                <div class="p-4 d-inline-block rounded-circle bg-light mb-3">
                                    <i class="fas fa-search-minus fa-3x text-warning"></i>
                                </div>
                                <h5 class="text-secondary">Data Nilai Tidak Ditemukan</h5>
                                <p class="text-muted">Sistem tidak menemukan data nilai untuk NIS: <strong><?php echo $nis; ?></strong></p>
                                <?php if($user_role === 'admin'): ?>
                                    <a href="arsipdata/datanilai" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Input Nilai Sekarang</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>