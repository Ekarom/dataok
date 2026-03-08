<?php

// File ini khusus menangani upload agar tidak tercampur HTML dari index.php

session_start(); // Aktifkan session jika koneksi database memerlukannya

ob_start();

include "cfg/konek.php";



// Bersihkan buffer output agar murni JSON

while (ob_get_level()) {

    ob_end_clean();

}

header('Content-Type: application/json');



if (isset($_FILES['file_upload']) && isset($_POST['target_column'])) {



    $column = $_POST['target_column'];

    $rotation = isset($_POST['rotation']) ? intval($_POST['rotation']) : 0;

    $validColumns = ['logo_sekolah', 'background_login', 'logo_pemda'];



    if (!in_array($column, $validColumns)) {

        echo json_encode(['status' => 'error', 'msg' => 'Kolom tidak valid']);

        exit;

    }



    $file = $_FILES['file_upload'];

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);

    $allowed = ['jpg', 'jpeg', 'png'];



    if (!in_array(strtolower($ext), $allowed)) {

        echo json_encode(['status' => 'error', 'msg' => 'Format harus JPG atau PNG']);

        exit;

    }



    // Buat nama file unik

    // FIX: Gunakan folder 'images/' agar sesuai dengan login.php dan konek.php

    $targetDir = "images/"; 

    if (!file_exists($targetDir))

        mkdir($targetDir, 0777, true);



    $fileName = $column . "_" . time() . "." . $ext;

    $targetFilePath = $targetDir . $fileName;



    if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {



        // Proses Rotasi Gambar

        if ($rotation != 0) {

            $source = imagecreatefromstring(file_get_contents($targetFilePath));

            if ($source) {

                $rotate = imagerotate($source, $rotation * -1, 0);



                // Simpan kembali

                if (strtolower($ext) == 'png') {

                    imagepng($rotate, $targetFilePath);

                } else {

                    imagejpeg($rotate, $targetFilePath);

                }

                imagedestroy($source);

                imagedestroy($rotate);

            }

        }



        // Simpan nama file ke database

        // Column name sudah divalidasi di atas, aman untuk digunakan langsung

        

        // FIX: Primary key adalah 'id', bukan 'id_profil' (berdasarkan cek di konek.php)

        $check = mysqli_query($sqlconn, "SELECT id, `$column` FROM profils WHERE id = 1");

        

        if (mysqli_num_rows($check) > 0) {

            // Update jika ada - hapus file lama terlebih dahulu

            $row = mysqli_fetch_assoc($check);

            $oldFile = $row[$column];

            

            // Hapus file lama jika ada dan bukan file default

            if (!empty($oldFile) && $oldFile != '' && $oldFile != 'default.png' && $oldFile != 'logo_default.png' && $oldFile != 'bg_default.jpg' ) {

                $oldFilePath = $targetDir . $oldFile;

                

                // Pastikan file benar-benar ada dan dalam direktori yang benar

                if (file_exists($oldFilePath) && is_file($oldFilePath)) {

                    // Validasi path untuk keamanan (pastikan masih dalam targetDir)

                    $realTargetDir = realpath($targetDir);

                    $realOldFile = realpath($oldFilePath);

                    

                    if ($realOldFile && strpos($realOldFile, $realTargetDir) === 0) {

                        if (@unlink($oldFilePath)) {

                            error_log("File lama berhasil dihapus: $oldFilePath");

                        } else {

                            error_log("Gagal menghapus file lama: $oldFilePath");

                        }

                    }

                }

            }

            

            // Update dengan file baru - gunakan backticks untuk nama kolom

            $stmt = $sqlconn->prepare("UPDATE profils SET `$column` = ? WHERE id = 1");

            $stmt->bind_param("s", $fileName);

            $exec = $stmt->execute();

            $stmt->close();

        } else {

            // Insert jika belum ada

            // FIX: Primary key 'id'

            $stmt = $sqlconn->prepare("INSERT INTO profils (id, `$column`) VALUES (1, ?)");

            $stmt->bind_param("s", $fileName);

            $exec = $stmt->execute();

            $stmt->close();

        }



        if ($exec) {

            echo json_encode(['status' => 'success', 'msg' => 'Upload berhasil', 'url' => $targetFilePath]);

        } else {

            echo json_encode(['status' => 'error', 'msg' => 'Gagal simpan database: ' . $sqlconn->error]);

        }



    } else {

        echo json_encode(['status' => 'error', 'msg' => 'Gagal memindahkan file upload']);

    }

} else {

    echo json_encode(['status' => 'error', 'msg' => 'Tidak ada data file yang dikirim']);

}

exit;

?>