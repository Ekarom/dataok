<?php

// include "dbconn.php"; // Sudah di-include di config/secure.php

include "secure.php";



// Periksa apakah variabel conn $conn berhasil dibuat dari file 'conn.php'

if (!isset($sqlconn) || $sqlconn->connect_error) {

    header('Content-Type: application/json');

    $errorMessage = isset($sqlconn) ? 'Koneksi database gagal: ' . $sqlconn->connect_error : 'Variabel koneksi database ($conn) tidak ditemukan. Pastikan file dbconn.php sudah benar.';

    echo json_encode(['status' => 'error', 'message' => $errorMessage]);

    exit();

}



// Mengatur header output menjadi JSON

header('Content-Type: application/json');



// --- Fungsi ---

/**

 * Fungsi untuk menghasilkan HTML paginasi (penomoran halaman)

 * @param int $currentPage Halaman saat ini

 * @param int $totalPages Jumlah total halaman

 * @return string HTML untuk navigasi paginasi

 */

function buatPaginasi($currentPage, $totalPages) {
    if ($totalPages <= 1) {
        return '';
    }

    $html = '<ul class="pagination pagination-sm m-0 float-right">';

    // Tombol Previous
    $prevDisabled = ($currentPage <= 1) ? 'disabled' : '';
    $prevPage = $currentPage - 1;
    $html .= '<li class="page-item ' . $prevDisabled . '">';
    $html .= '<a class="page-link" href="javascript:void(0)" data-page="' . $prevPage . '">Previous</a>';
    $html .= '</li>';

    $adjacents = 2; 

    if ($totalPages <= 7) {
        for ($i = 1; $i <= $totalPages; $i++) {
            $active = ($i == $currentPage) ? 'active' : '';
            $html .= '<li class="page-item ' . $active . '">';
            $html .= '<a class="page-link" href="javascript:void(0)" data-page="' . $i . '">' . $i . '</a>';
            $html .= '</li>';
        }
    } else {
        $start = max(1, $currentPage - $adjacents);
        $end = min($totalPages, $currentPage + $adjacents);
        
        if ($currentPage <= $adjacents + 1) {
            $end = min($totalPages, 1 + 2 * $adjacents); 
        }
        if ($currentPage >= $totalPages - $adjacents) {
            $start = max(1, $totalPages - 2 * $adjacents); 
        }

        if ($start > 1) {
             $html .= '<li class="page-item"><a class="page-link" href="javascript:void(0)" data-page="1">1</a></li>';
             if ($start > 2) {
                 $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
             }
        }

        for ($i = $start; $i <= $end; $i++) {
            $active = ($i == $currentPage) ? 'active' : '';
            $html .= '<li class="page-item ' . $active . '">';
            $html .= '<a class="page-link" href="javascript:void(0)" data-page="' . $i . '">' . $i . '</a>';
            $html .= '</li>';
        }

        if ($end < $totalPages) {
             if ($end < $totalPages - 1) {
                 $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
             }
             $html .= '<li class="page-item"><a class="page-link" href="javascript:void(0)" data-page="' . $totalPages . '">' . $totalPages . '</a></li>';
        }
    }

    // Tombol Next
    $nextDisabled = ($currentPage >= $totalPages) ? 'disabled' : '';
    $nextPage = $currentPage + 1;
    $html .= '<li class="page-item ' . $nextDisabled . '">';
    $html .= '<a class="page-link" href="javascript:void(0)" data-page="' . $nextPage . '">Next</a>';
    $html .= '</li>';

    $html .= '</ul>';

    return $html;
}





// --- Logika Utama (Router Aksi) ---

// Memeriksa aksi yang diminta dari frontend

$action = isset($_POST['action']) ? $_POST['action'] : '';



switch ($action) {

    // Kasus untuk memuat data siswa (dengan pencarian dan paginasi)

    case 'muat':

        $search = isset($_POST['search']) ? $_POST['search'] : '';

        $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;

        $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;

        $offset = ($page - 1) * $limit;



        // --- Pengaturan Pengurutan (Default) ---
        $params = [];
        $types = '';
        
        $countQuery = "SELECT COUNT(id) as total FROM siswa";
        $dataQuery = "SELECT id, pd, jk, nis, nisn, kelas, photo FROM siswa";

        

        // Menambahkan kondisi WHERE jika ada kata kunci pencarian

        if (!empty($search)) {

            $keyword = "%" . $search . "%";

            $whereClause = " WHERE pd LIKE ? OR jk LIKE ? OR nis LIKE ? OR kelas LIKE ?";

            $countQuery .= $whereClause;

            $dataQuery .= $whereClause;

            // Menambahkan parameter untuk prepared statement

            array_push($params, $keyword, $keyword, $keyword, $keyword);

            $types .= 'ssss';

        }

        

        // Menghitung total data

        $stmt = $sqlconn->prepare($countQuery);

        if (!empty($search)) {

            $stmt->bind_param($types, ...$params);

        }

        $stmt->execute();

        $totalResults = $stmt->get_result()->fetch_assoc()['total'];

        $totalPages = ceil($totalResults / $limit);

        $stmt->close();



        // Mengambil data siswa dengan limit dan offset

        $dataQuery .= " ORDER BY pd ASC LIMIT ? OFFSET ?";

        array_push($params, $limit, $offset);

        $types .= 'ii';

        

        $stmt = $sqlconn->prepare($dataQuery);

        $stmt->bind_param($types, ...$params);

        $stmt->execute();

        $result = $stmt->get_result();



        // Membuat informasi jumlah data yang ditampilkan

        $recordsInfo = '';

        if ($totalResults > 0) {

            $startRecord = $offset + 1;

            $endRecord = $offset + $result->num_rows;

            $recordsInfo = "Menampilkan data {$startRecord} - {$endRecord} dari {$totalResults} total data.";

        }



        // Membangun HTML tabel dari data yang didapat
        $tableHtml = '<div class="table-responsive"><table class="table table-striped align-middle mb-0">';

        

        // --- Header Tabel Statis ---
        $tableHtml .= '<thead class="table-light"><tr class="text-center">
            <th>No</th>
            <th>Foto</th>
            <th>Nama Siswa</th>
            <th>JK</th>
            <th>NIS</th>
            <th>NISN</th>
            <th>Kelas</th>';

        $tableHtml .= '<th scope="col" style="width: 100px;">Aksi</th></tr></thead><tbody>';

        

        if ($result->num_rows > 0) {

            $nomor = $offset + 1;

            while ($row = $result->fetch_assoc()) {

                $tableHtml .= "<tr>";

                $tableHtml .= "<td>{$nomor}</td>";
                $photo = !empty($row['photo']) && file_exists("file/fotopd/" . $row['photo']) ? "file/fotopd/" . $row['photo'] : "images/male.png";
                $tableHtml .= "<td><img src='{$photo}' alt='User profile picture' class='profile-user-img img-fluid img-circle shadow-sm' style='width: 45px; height: 45px; object-fit: cover; border: 2px solid #fff;'></td>";
                $pd_clean = str_replace(['@', '#', '*'], '', $row['pd']);
                $tableHtml .= "<td class='px-3 text-left'>" . htmlspecialchars($pd_clean) . "</td>";
                $tableHtml .= "<td class=''>" . htmlspecialchars($row['jk']) . "</td>";
                $tableHtml .= "<td class='text-muted font-weight-bold'>" . htmlspecialchars($row['nis']) . "</td>";
                $tableHtml .= "<td class=''>" . htmlspecialchars($row['nisn']) . "</td>";
                $tableHtml .= "<td class=''>" . htmlspecialchars($row['kelas']) . "</td>";
                $tableHtml .= '<td class="p-1">
                                    <div class="btn-group">
                                        <button class="btn btn-primary btn-sm tombol-view" data-id="' . $row['id'] . '" title="View"><i class="fas fa-eye"></i></button>
                                    </div>
                                </td>';
                $tableHtml .= "</tr>";

                $nomor++;

            }

        } else {

            $tableHtml .= '<tr><td colspan="7">Tidak ada data ditemukan.</td></tr>';

        }
        $tableHtml .= '</tbody></table></div>';

        $stmt->close();



        // Mengirim response JSON yang berisi HTML tabel dan paginasi

        echo json_encode([

            'table' => $tableHtml,

            'pagination' => buatPaginasi($page, $totalPages),

            'recordsInfo' => $recordsInfo

        ]);

        break;



    // -- BARU: Kasus untuk menghapus semua data dari tabel siswa --

    case 'hapus_semua':

        // TRUNCATE TABLE lebih efisien daripada DELETE FROM tanpa WHERE dan akan mereset auto-increment

        if ($sqlconn->query('TRUNCATE TABLE siswa')) {

            echo json_encode(['status' => 'success', 'message' => 'Semua data siswa berhasil dihapus secara permanen.']);

        } else {

            echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus semua data: ' . $sqlconn->error]);

        }

        break;



    // -- DIPERBAIKI: Menggunakan MySQLi, bukan PDO --

    // Kasus untuk menghapus satu data siswa berdasarkan ID

    case 'hapus':

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        if ($id > 0) {

            $stmt = $sqlconn->prepare("DELETE FROM siswa WHERE id = ?");

            if ($stmt) {

                $stmt->bind_param('i', $id);

                if ($stmt->execute()) {

                    echo json_encode(['status' => 'success', 'message' => 'Data siswa berhasil dihapus.']);

                } else {

                    echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus data siswa: ' . $stmt->error]);

                }

                $stmt->close();

            } else {

                 echo json_encode(['status' => 'error', 'message' => 'Gagal mempersiapkan query: ' . $sqlconn->error]);

            }

        } else {

            echo json_encode(['status' => 'error', 'message' => 'ID siswa tidak valid atau tidak ditemukan.']);

        }

        break;



    // Kasus untuk mengambil detail satu siswa berdasarkan ID

    case 'ambil':

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        $stmt = $sqlconn->prepare("SELECT * FROM siswa WHERE id = ?");

        $stmt->bind_param('i', $id);

        $stmt->execute();

        $result = $stmt->get_result()->fetch_assoc();

        $stmt->close();

        echo json_encode($result);
        break;

    case 'ganti_foto':
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        
        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'ID siswa tidak valid.']);
            break;
        }

        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['status' => 'error', 'message' => 'Gagal mengupload file: error ' . ($_FILES['photo']['error'] ?? 'tidak diketahui')]);
            break;
        }

        $file = $_FILES['photo'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        if (!in_array($file['type'], $allowedTypes)) {
            echo json_encode(['status' => 'error', 'message' => 'Format file tidak didukung. Gunakan JPG, PNG, GIF, atau WebP.']);
            break;
        }

        if ($file['size'] > $maxSize) {
            echo json_encode(['status' => 'error', 'message' => 'Ukuran file terlalu besar. Maksimal 2MB.']);
            break;
        }

        $uploadDir = 'file/fotopd/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFileName = 'pd_' . $id . '_' . time() . '.' . $ext;
        $targetPath = $uploadDir . $newFileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Hapus foto lama jika ada
            $stmt_old = $sqlconn->prepare("SELECT photo FROM siswa WHERE id = ?");
            $stmt_old->bind_param('i', $id);
            $stmt_old->execute();
            $res_old = $stmt_old->get_result()->fetch_assoc();
            if ($res_old && !empty($res_old['photo']) && file_exists($uploadDir . $res_old['photo'])) {
                unlink($uploadDir . $res_old['photo']);
            }
            $stmt_old->close();

            // Update database
            $stmt_upd = $sqlconn->prepare("UPDATE siswa SET photo = ? WHERE id = ?");
            $stmt_upd->bind_param('si', $newFileName, $id);
            
            if ($stmt_upd->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Foto berhasil diperbarui.', 'new_photo' => $newFileName]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui database: ' . $stmt_upd->error]);
            }
            $stmt_upd->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal memindahkan file ke direktori tujuan.']);
        }
        break;



}



// Menutup conn database

$sqlconn->close();

?>

