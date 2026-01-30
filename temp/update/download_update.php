<?php
// c:\wamp64\www\data\update\download_update.php
session_start();
header('Content-Type: application/json');

$url = isset($_GET['url']) ? $_GET['url'] : '';
$version = isset($_GET['version']) ? $_GET['version'] : '';

if (empty($url)) {
    echo json_encode(['error' => 'No URL provided']);
    exit;
}

// Mulai proses download dan update
try {
    // 1. Download File
    // Initialize progress
    $_SESSION['update_progress'] = 0;
    $_SESSION['update_message'] = "Menghubungkan ke server update...";
    session_write_close(); // Unlock session so get_progress can read
    
    $zipData = @file_get_contents($url);
    if ($zipData === false) {
        throw new Exception("Gagal mendownload file update dari: $url");
    }
    
    // Update progress
    session_start();
    $_SESSION['update_progress'] = 30;
    $_SESSION['update_message'] = "Download selesai. Mengekstrak...";
    session_write_close(); // Unlock
    
    // Simpan file sementara
    $tempZip = tempnam(sys_get_temp_dir(), 'update_');
    file_put_contents($tempZip, $zipData);
    
    // 2. Ekstrak File
    $zip = new ZipArchive;
    if ($zip->open($tempZip) === TRUE) {
        // Ekstrak ke root folder (c:\wamp64\www\data\)
        // Asumsi skrip ini ada di c:\wamp64\www\data\update\
        $extractPath = realpath(__DIR__ . '/..'); 
        $zip->extractTo($extractPath);
        $zip->close();
        
        // Update progress
        session_start();
        $_SESSION['update_progress'] = 80;
        $_SESSION['update_message'] = "Ekstraksi selesai. Memperbarui konfigurasi...";
        session_write_close(); // Unlock
        
        // 3. Update Versi di konek.php
        $konekPath = $extractPath . '/cfg/konek.php';
        
        // Include koneksi untuk update database
        if (file_exists($konekPath)) {
            // Kita include dari path relative script ini agar $sqlconn tersedia
            include('../cfg/konek.php');
            
            if (isset($sqlconn)) {
                $v_safe = mysqli_real_escape_string($sqlconn, $version);
                mysqli_query($sqlconn, "INSERT INTO version (version) VALUES ('$v_safe')");
            }
            
            // Opsional: Tetap update file konek.php jika masih digunakan sebagai backup
            $konekContent = file_get_contents($konekPath);
            // Ganti string version lama dengan yang baru
            // Regex untuk mencari $app_version = "x.y.z";
            $newContent = preg_replace('/(\$app_version\s*=\s*")([^"]+)(";)/', '${1}' . $version . '${3}', $konekContent);
            
            if ($newContent && $newContent !== $konekContent) {
                file_put_contents($konekPath, $newContent);
            }
        }
        
        unlink($tempZip); // Hapus file temp
        
        session_start();
        $_SESSION['update_progress'] = 100;
        $_SESSION['update_message'] = "Update Selesai!";
        session_write_close(); // Unlock for final read
        
        echo json_encode(['status' => 'success', 'message' => 'Update berhasil diinstal.']);
        
    } else {
        throw new Exception("Gagal membuka file zip update.");
    }
    
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    // Reset progress on error
    if (session_status() == PHP_SESSION_NONE) session_start();
    $_SESSION['update_progress'] = -1; 
    $_SESSION['update_message'] = $e->getMessage();
    session_write_close();
}
?>
