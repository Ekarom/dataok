<?php

include "cfg/konek.php";



// --- FUNGSI UPDATE DATA (Global Scope) ---
function updateData($conn, $fields, $id = 1)
{
    $setQuery = [];
    $types = "";
    $values = [];

    foreach ($fields as $col => $val) {
        $setQuery[] = "`$col` = ?";
        $types .= "s";
        $values[] = $val;
    }
    $values[] = $id;
    $types .= "i";

    $sql = "UPDATE profils SET " . implode(", ", $setQuery) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return false;
    
    $stmt->bind_param($types, ...$values);
    return $stmt->execute();
}

// --- SEEDING DATA AWAL (PENTING!) ---
$cek_awal = $sqlconn->query("SELECT id FROM profils WHERE id = 1");
if ($cek_awal && $cek_awal->num_rows == 0) {
    $sql_seed = "INSERT INTO profils (
        id, nsekolah, alamat, kecamatan, kelurahan, provinsi, kabupaten, kodepos, no_telp, email, website, 
        nipkasudin, nrkkasudin, kasudin, nipkepsek, nrkkepsek, kepsek, nippengawas, nrpengawas, pengawas, nikipsi, nrkpsi, nampsi, nipkasi, nrkasi, kasi, nipktu, nrktu, ktu, logo_sekolah, background_login
    ) VALUES (
        1, '', '', '', '', '', '', '', '', '', '', 
        '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', 
        '-', '-', '-', '-', '-', 'logo_default.png', 'bg_default.jpg'
    )";
    $sqlconn->query($sql_seed);
}



// --- LOGIK POST REQUEST ---

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {



    // A. Update Data Sekolah
    if ($_POST['action'] == 'update_sekolah') {
        $fields = [
            'nsekolah' => $_POST['nsekolah'],
            'npsn' => $_POST['npsn'],
            'alamat' => $_POST['njalan'],
            'kecamatan' => $_POST['nkec'],
            'kelurahan' => $_POST['nkel'],
            'provinsi' => $_POST['nprovinsi'],
            'kabupaten' => $_POST['nkab'],
            'kodepos' => $_POST['pos'],
            'no_telp' => $_POST['tlp'],
            'email' => $_POST['email'],
            'website' => $_POST['web']
        ];
        if (updateData($sqlconn, $fields)) {
            echo "<script>alert('Data Sekolah Berhasil Disimpan!');</script>";
        } else {
             echo "<script>alert('Gagal Menyimpan Data Sekolah!');</script>";
        }
    }

    // B. Update Data Pejabat (Mapping ke extra_1 - extra_15)
    $pejabatConfig = [
        'kasudin'  => ['nama' => 'kasudin',  'nip' => 'nipkasudin',  'nrk' => 'nrkkasudin'],
        'kepsek'   => ['nama' => 'kepsek',  'nip' => 'nipkepsek',  'nrk' => 'nrkkepsek'],
        'pengawas' => ['nama' => 'pengawas',  'nip' => 'nippengawas',  'nrk' => 'nrkpengawas'],
        'kasi'     => ['nama' => 'kasi', 'nip' => 'nipkasi', 'nrk' => 'nrkkasi'],
        'ktu'      => ['nama' => 'ktu', 'nip' => 'nipktu', 'nrk' => 'nrkktu']
    ];

    foreach ($pejabatConfig as $jabatan => $cols) {
        if ($_POST['action'] == 'update_' . $jabatan) {
            $fields = [
                $cols['nama'] => $_POST['nama'],
                $cols['nip']  => $_POST['nip'],
                $cols['nrk']  => $_POST['nrk']
            ];
            if (updateData($sqlconn, $fields)) {
                echo "<script>alert('Data " . ucfirst($jabatan) . " Berhasil Disimpan!');</script>";
            }
        }
    }
}



// --- FETCH DATA UTAMA ---
$query = "SELECT * FROM profils WHERE id = 1 LIMIT 1";
$result = $sqlconn->query($query);
$data = ($result) ? $result->fetch_assoc() : null;

// Inisialisasi data kosong jika belum ada record (Fallback)
if (!$data) {
    $columns = [
        'nsekolah', 'npsn', 'alamat', 'kecamatan', 'kelurahan', 'provinsi', 'kabupaten', 'kodepos', 'no_telp', 'email', 'website',
        'nipkasudin', 'nrkkasudin', 'kasudin', 'nipkepsek', 'nrkkepsek', 'kepsek', 'nippengawas', 'nrkpengawas', 'pengawas', 'nipkasi', 'nrkkasi', 'kasi', 'nipktu', 'nrkktu', 'ktu', 'logo_sekolah', 'background_login'
    ];
    $data = array_fill_keys($columns, '');
}



?>



<!-- UI HTML -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<style>

    .img-preview-container {

        width: 100%;

        height: 200px;

        border: 2px dashed #ccc;

        display: flex;

        justify-content: center;

        align-items: center;

        margin-bottom: 10px;

        overflow: hidden;

        background: #f4f6f9;

        position: relative;

    }



    .img-preview-container img {

        max-width: 100%;

        max-height: 100%;

        transition: transform 0.3s ease;

    }



    .progress {

        height: 20px;

        display: none;

        margin-top: 10px;

    }

</style>



<div class="content-wrapper">

    <section class="content-header">

        <div class="container-fluid">

            <div class="row mb-2">

                <div class="col-sm-6">

                    <h1>Profil & Konfigurasi Sekolah</h1>

                </div>

                <div class="col-sm-6">

                    <ol class="breadcrumb float-sm-right">

                        <li class="breadcrumb-item"><a href="#">Home</a></li>

                        <li class="breadcrumb-item active">Profil</li>

                    </ol>

                </div>

            </div>

        </div>

    </section>



    <section class="content">

        <div class="container-fluid">

            <div class="row">



                <!-- KOLOM KIRI: Data Sekolah -->

                <div class="col-md-8">

                    <div class="card">

                        <div class="card-header bg-menu-gradient">

                            <h3 class="card-title">Data Identitas Sekolah</h3>

                        </div>

                        <form method="POST" action="">

                            <input type="hidden" name="action" value="update_sekolah">

                            <div class="card-body">

                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Nama Sekolah</label>
                                    <div class="col-sm-9"><input type="text" class="form-control" name="nsekolah"
                                            value="<?= $data['nsekolah'] ?>"></div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Jalan</label>
                                    <div class="col-sm-9"><input type="text" class="form-control" name="njalan"
                                            value="<?= isset($data['alamat']) ? $data['alamat'] : '' ?>"></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group"><label>Kelurahan</label><input type="text"
                                                class="form-control" name="nkel" value="<?= isset($data['kelurahan']) ? $data['kelurahan'] : '' ?>"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group"><label>Kecamatan</label><input type="text"
                                                class="form-control" name="nkec" value="<?= isset($data['kecamatan']) ? $data['kecamatan'] : '' ?>"></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group"><label>Kabupaten/Kota</label><input type="text"
                                                class="form-control" name="nkab" value="<?= isset($data['kabupaten']) ? $data['kabupaten'] : '' ?>"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group"><label>Provinsi</label><input type="text"
                                                class="form-control" name="nprovinsi" value="<?= isset($data['provinsi']) ? $data['provinsi'] : '' ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group"><label>Kode Pos</label><input type="text"
                                                class="form-control" name="pos" value="<?= isset($data['kodepos']) ? $data['kodepos'] : '' ?>"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group"><label>Telepon</label><input type="text"
                                                class="form-control" name="tlp" value="<?= isset($data['no_telp']) ? $data['no_telp'] : '' ?>"></div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Email</label>
                                    <div class="col-sm-9"><input type="email" class="form-control" name="email"
                                            value="<?= $data['email'] ?>"></div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Website</label>
                                    <div class="col-sm-9"><input type="text" class="form-control" name="web"
                                            value="<?= isset($data['website']) ? $data['website'] : '' ?>"></div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">NPSN</label>
                                    <div class="col-sm-9"><input type="text" class="form-control" name="npsn"
                                            value="<?= isset($data['npsn']) ? $data['npsn'] : '' ?>"></div>
                                </div>
                            </div>

                            <div class="card-footer text-right">

                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>

                            </div>

                        </form>

                    </div>



                    <!-- TABEL PEJABAT (TABS) -->

                    <div class="card card-navy card-outline card-tabs">
            
                        <div class="card-header bg-menu-gradient p-0 pt-1 border-bottom-0">

                            <ul class="nav nav-tabs" id="custom-tabs-three-tab" role="tablist">

                                <li class="nav-item"><a class="nav-link active" id="tab-kasudin" data-toggle="pill"
                                        href="#content-kasudin" role="tab" style="color: #fff;">Kasudin</a></li>

                                <li class="nav-item"><a class="nav-link" id="tab-kepsek" data-toggle="pill"
                                        href="#content-kepsek" role="tab" style="color: #fff;">Kepsek</a></li>

                                <li class="nav-item"><a class="nav-link" id="tab-pengawas" data-toggle="pill"

                                        href="#content-pengawas" role="tab" style="color: #fff;">Pengawas</a></li>

                                <li class="nav-item"><a class="nav-link" id="tab-kasi" data-toggle="pill"

                                        href="#content-kasi" role="tab" style="color: #fff;">Kasi</a></li>

                                <li class="nav-item"><a class="nav-link" id="tab-ktu" data-toggle="pill"

                                        href="#content-ktu" role="tab" style="color: #fff;">KTU</a></li>

                            </ul>

                        </div>

                        <div class="card-body">

                            <div class="tab-content" id="custom-tabs-three-tabContent">



                                <?php
                                // Loop untuk membuat form Pejabat secara otomatis
                                $pejabatMap = [
                                    'kasudin' => ['label' => 'Kepala Suku Dinas', 'cols' => ['kasudin', 'nipkasudin', 'nrkkasudin']],
                                    'kepsek' => ['label' => 'Kepala Sekolah', 'cols' => ['kepsek', 'nipkepsek', 'nrkkepsek']],
                                    'pengawas' => ['label' => 'Pengawas Sekolah', 'cols' => ['pengawas', 'nippengawas', 'nrpengawas']],
                                    'kasi' => ['label' => 'Kepala Seksi', 'cols' => ['kasi', 'nipkasi', 'nrkasi']],
                                    'ktu' => ['label' => 'Kepala Tata Usaha', 'cols' => ['ktu', 'nipktu', 'nrktu']]
                                ];

                                foreach ($pejabatMap as $key => $info) {
                                    $active = ($key == 'kasudin') ? 'show active' : '';
                                    ?>
                                    <div class="tab-pane fade <?= $active ?>" id="content-<?= $key ?>" role="tabpanel">
                                        <form method="POST" action="">
                                            <input type="hidden" name="action" value="update_<?= $key ?>">
                                            <div class="form-group">
                                                <label>Nama <?= $info['label'] ?></label>
                                                <input type="text" class="form-control" name="nama"
                                                    value="<?= isset($data[$info['cols'][0]]) ? $data[$info['cols'][0]] : '' ?>">
                                            </div>
                                            <div class="form-group">
                                                <label>NIP</label>
                                                <input type="text" class="form-control" name="nip"
                                                    value="<?= isset($data[$info['cols'][1]]) ? $data[$info['cols'][1]] : '' ?>"
                                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                                    inputmode="numeric" placeholder="Hanya Angka">
                                            </div>
                                            <div class="form-group">
                                                <label>NRK</label>
                                                <input type="text" class="form-control" name="nrk"
                                                    value="<?= isset($data[$info['cols'][2]]) ? $data[$info['cols'][2]] : '' ?>"
                                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                                    inputmode="numeric" placeholder="Hanya Angka">
                                            </div>
                                            <button type="submit" class="btn btn-success">Simpan</button>
                                        </form>
                                    </div>
                                <?php } ?>



                            </div>

                        </div>

                    </div>

                </div>



                <!-- KOLOM KANAN: Upload Gambar -->
                <div class="col-md-4">

                    <?php
                    $uploads = [
                        'logo_sekolah' => 'Logo Sekolah',
                        'background_login' => 'Background Login',
                    ];

                    foreach ($uploads as $field => $title) {
                        $currentImg = !empty($data[$field]) ? "images/" . $data[$field] : "images/noimage.png";
                        ?>
                        <div class="card card-danger card-outline">
                            <div class="card-header bg-menu-gradient">
                                <h3 class="card-title"><?= $title ?></h3>
                            </div>
                            <div class="card-body text-center">
                                <!-- Area Preview -->
                                <div class="img-preview-container">
                                    <img id="preview_<?= $field ?>" src="<?= $currentImg ?>" alt="Preview">
                                </div>

                                <!-- Kontrol Rotasi (Hanya aktif saat preview file baru) -->
                                <div class="btn-group mb-2" id="controls_<?= $field ?>" style="display:none;">
                                    <button type="button" class="btn btn-sm btn-default"
                                        onclick="rotateImage('<?= $field ?>', -90)"><i class="fas fa-undo"></i></button>
                                    <button type="button" class="btn btn-sm btn-default"
                                        onclick="rotateImage('<?= $field ?>', 90)"><i class="fas fa-redo"></i></button>
                                </div>

                                <!-- Input File -->
                                <div class="form-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="file_<?= $field ?>"
                                            accept="image/jpeg, image/png" onchange="previewFile('<?= $field ?>')">
                                        <label class="custom-file-label text-left" for="file_<?= $field ?>">Pilih
                                            file...</label>
                                    </div>
                                    <small class="text-muted">Format: JPG/PNG</small>
                                </div>

                                <!-- Progress Bar -->
                                <div class="progress" id="progress_wrap_<?= $field ?>">
                                    <div class="progress-bar bg-success progress-bar-striped progress-bar-animated"
                                        id="progress_<?= $field ?>" role="progressbar" style="width: 0%">0%</div>
                                </div>

                                <button type="button" class="btn btn-warning btn-block mt-2"
                                    onclick="uploadFile('<?= $field ?>')">
                                    <i class="fas fa-upload"></i> Upload
                                </button>
                            </div>
                        </div>
                    <?php } ?>

                </div>

                <!-- /.col -->

            </div>

        </div>

    </section>

</div>



<!-- JAVASCRIPT UNTUK PREVIEW, ROTATE & UPLOAD -->

<script>

    var rotations = { 'logo_pemda': 0, 'logo_sekolah': 0, 'background_login': 0 };



    function previewFile(field) {

        var preview = document.querySelector('#preview_' + field);

        var file = document.querySelector('#file_' + field).files[0];

        var controls = document.querySelector('#controls_' + field);

        var reader = new FileReader();



        if (file) {

            reader.onloadend = function () {

                preview.src = reader.result;

                rotations[field] = 0;

                updateRotation(field);

                controls.style.display = 'inline-block';

            }

            reader.readAsDataURL(file);

        }

    }



    function rotateImage(field, degree) {

        rotations[field] += degree;

        updateRotation(field);

    }



    function updateRotation(field) {

        var preview = document.querySelector('#preview_' + field);

        preview.style.transform = 'rotate(' + rotations[field] + 'deg)';

    }



    function uploadFile(field) {

        var fileInput = document.getElementById('file_' + field);

        if (fileInput.files.length === 0) {

            toastr.warning("Pilih gambar terlebih dahulu!");

            return;

        }



        var file = fileInput.files[0];

        var formData = new FormData();

        formData.append("file_upload", file);

        formData.append("target_column", field);



        var finalRotation = rotations[field] % 360;

        formData.append("rotation", finalRotation);



        var xhr = new XMLHttpRequest();



        // Progress Bar Handler

        xhr.upload.addEventListener("progress", function (evt) {

            if (evt.lengthComputable) {

                var percentComplete = parseInt((evt.loaded / evt.total) * 100);

                var progressWrap = document.getElementById('progress_wrap_' + field);

                var progressBar = document.getElementById('progress_' + field);



                progressWrap.style.display = 'flex';

                progressBar.style.width = percentComplete + '%';

                progressBar.innerHTML = percentComplete + '%';

            }

        }, false);



        xhr.onreadystatechange = function () {

            if (xhr.readyState === 4) {

                if (xhr.status === 200) {

                    try {

                        // console.log("Raw Response:", xhr.responseText); // Debug jika perlu

                        var resp = JSON.parse(xhr.responseText);

                        if (resp.status === 'success') {

                            toastr.success(resp.msg);

                            setTimeout(function () {

                                document.getElementById('progress_wrap_' + field).style.display = 'none';

                                document.getElementById('controls_' + field).style.display = 'none';

                            }, 2000);

                        } else {

                            toastr.error(resp.msg);

                        }

                    } catch (e) {

                        toastr.error("Respon tidak valid (bukan JSON).");

                        console.log("Error Parsing JSON:", e);

                        console.log("Raw Data:", xhr.responseText);

                    }

                } else {

                    toastr.error("Gagal menghubungi server upload.");

                }

            }

        };



        // UPDATE PENTING: Arahkan ke file ajax_upload.php, bukan file ini sendiri

        xhr.open("POST", "upload_datasek.php", true);

        xhr.send(formData);

    }

</script>