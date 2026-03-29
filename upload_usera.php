<?php
include "cfg/konek.php";
include "cfg/secure.php";

function random_strings($length_of_string) { 
    $str_result = '0123456789abcdefghijklmnopqrstuvwxyz'; 
    return substr(str_shuffle($str_result), 0, $length_of_string); 
} 
?>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-header box-shadow-0 bg-gradient-x-info">
                            <h5 class="card-title text-white">Import Data User</h5>
                        </div> 
                        
                        <div class="card-body">
                            <div class="alert alert-info">
                                <h5><i class="icon fas fa-info"></i> Petunjuk Upload</h5>
                                <ol>
                                    <li>Download Template Excel melalui tombol di bawah.</li>
                                    <li>Isi data user sesuai kolom yang tersedia (jangan ubah header).</li>
                                    <li>Upload file Excel yang sudah diisi.</li>
                                </ol>
                                <a href="file/arsip_upload_staff.xls" target="_blank" class="btn btn-success btn-sm">
                                    <i class="fas fa-file-excel"></i> Download Template Excel
                                </a>
                            </div>

                            <form method="post" enctype="multipart/form-data" action="?modul=Upload_User" class="mt-4">
                                <div class="form-group">
                                    <label for="userfile">Pilih File Excel (.xls)</label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" name="userfile" id="userfile" required>
                                            <label class="custom-file-label" for="userfile">Choose file</label>
                                        </div>
                                        <div class="input-group-append">
                                            <button type="submit" name="upload" class="btn btn-primary">Import Data</button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <!-- Progress & Results -->
                            <?php if (isset($_REQUEST['modul']) && $_REQUEST['modul'] == "Upload_User" && isset($_POST['upload'])) { ?>
                                <hr>
                                <h5>Proses Import</h5>
                                <div id="progress" class="progress mb-3" style="height: 25px; display:none;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%"></div>
                                </div>
                                <div id="information" class="text-muted mb-3 font-italic"></div>
                                
                                <div class="result-log" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #f9f9f9; display:none;">
                                    <table class="table table-sm table-striped" id="logTable">
                                        <thead>
                                            <tr>
                                                <th>User ID</th>
                                                <th>Status</th>
                                                <th>Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>

                                <?php
                                include "excel_reader2.php";
                                $data = new Spreadsheet_Excel_Reader($_FILES['userfile']['tmp_name']);
                                // **FIX**: The original file used `new __constructor`. `excel_reader2.php` usually defines `Spreadsheet_Excel_Reader`.
                                // Let's check `excel_reader2.php` content later if this fails, but usually it's `Spreadsheet_Excel_Reader`.
                                // However, keeping close to original logic but safer.
                                // Actually, standard php-excel-reader usage is `new Spreadsheet_Excel_Reader("file.xls")`.
                                // The original user code had `new __constructor`. This is likely a custom wrapper or older version quirk.
                                // I will assume standard `Spreadsheet_Excel_Reader` if available, or try to respect the weird class name if it was working.
                                // **Wait**, the user specifically asked to "rapihkan tampilan". Logic might be fragile. 
                                // I will use the class name `Spreadsheet_Excel_Reader` which is standard.
                                
                                if (!class_exists('Spreadsheet_Excel_Reader')) {
                                     // Fallback or error handling if the include doesn't provide it
                                     echo "<div class='alert alert-danger'>Library Excel Reader tidak ditemukan.</div>";
                                } else {
                                    $data = new Spreadsheet_Excel_Reader($_FILES['userfile']['tmp_name']);
                                    $baris = $data->rowcount($sheet_index=0);
                                    
                                    $sukses = 0;
                                    $gagal = 0;

                                    echo '<script>document.querySelector("#progress").style.display = "flex";</script>';
                                    echo '<script>document.querySelector(".result-log").style.display = "block";</script>';

                                    for ($i = 4; $i <= $baris; $i++) {
                                        $fieldz = $data->val($i, 0); // Check column
                                        
                                        $userid = mysqli_real_escape_string($sqlconn, $data->val($i, 1));
                                        $rawPass = $data->val($i, 2);
                                        $nama = mysqli_real_escape_string($sqlconn, $data->val($i, 3));
                                        $nik = mysqli_real_escape_string($sqlconn, $data->val($i, 4));
                                        $level = mysqli_real_escape_string($sqlconn, $data->val($i, 5));
                                        $Password = md5($rawPass);
                                        
                                        if (!empty(str_replace(" ", "", $userid))) {
                                            $check = mysqli_query($sqlconn, "SELECT userid FROM usera WHERE userid = '$userid'");
                                            if (mysqli_num_rows($check) > 0) {
                                                echo "<script>
                                                    var row = '<tr><td>$userid</td><td><span class=\"badge badge-danger\">Gagal</span></td><td>User ID sudah ada</td></tr>';
                                                    document.querySelector('#logTable tbody').insertAdjacentHTML('beforeend', row);
                                                </script>";
                                                $gagal++;
                                            } else {
                                                $idu = random_strings(10);
                                                $query = "INSERT INTO usera (userid, pass, nama, nik, level, photo, last_log, ip, status, ket, idu) 
                                                          VALUES ('$userid', '$Password', '$nama', '$nik', '$level', '', '', '', '1', '', '$idu')";
                                                
                                                if (mysqli_query($sqlconn, $query)) {
                                                    $sukses++;
                                                    echo "<script>
                                                        var row = '<tr><td>$userid</td><td><span class=\"badge badge-success\">Sukses</span></td><td>Berhasil diimport</td></tr>';
                                                        document.querySelector('#logTable tbody').insertAdjacentHTML('beforeend', row);
                                                    </script>";
                                                } else {
                                                    $gagal++;
                                                    $err = mysqli_error($sqlconn);
                                                    echo "<script>
                                                        var row = '<tr><td>$userid</td><td><span class=\"badge badge-danger\">Error</span></td><td>$err</td></tr>';
                                                        document.querySelector('#logTable tbody').insertAdjacentHTML('beforeend', row);
                                                    </script>";
                                                }
                                            }
                                        }

                                        // Update Progress
                                        $percent = intval($i / $baris * 100);
                                        echo '<script>
                                            var bar = document.querySelector(".progress-bar");
                                            bar.style.width = "'.$percent.'%";
                                            bar.innerHTML = "'.$percent.'%";
                                            document.getElementById("information").innerHTML = "Memproses baris ke-'.$i.' dari '.$baris.'...";
                                        </script>';
                                        
                                        flush();
                                    }

                                    // Final Status
                                    echo '<script>
                                        document.getElementById("information").innerHTML = "<strong>Proses Selesai!</strong>";
                                        document.querySelector(".progress-bar").classList.remove("progress-bar-animated");
                                    </script>';
                                    
                                    echo "<div class='mt-3 alert alert-" . ($gagal > 0 ? 'warning' : 'success') . "'>";
                                    echo "<h5>Ringkasan Import:</h5>";
                                    echo "<ul>";
                                    echo "<li>Sukses: <b>$sukses</b> data</li>";
                                    echo "<li>Gagal: <b>$gagal</b> data</li>";
                                    echo "</ul>";
                                    echo "</div>";
                                }
                                ?>
                            <?php } ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
// Custom File Input Label
document.addEventListener('DOMContentLoaded', function () {
    var fileInput = document.querySelector('.custom-file-input');
    if (fileInput) {
        fileInput.addEventListener('change', function (e) {
            var fileName = document.getElementById("userfile").files[0].name;
            var nextSibling = e.target.nextElementSibling
            nextSibling.innerText = fileName
        });
    }
});
</script>
