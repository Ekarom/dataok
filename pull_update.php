<?php
/**
 * Script untuk menarik update dari arsip.p171.net/update ke data.p171.net
 * Cara menggunakan: php pull_update.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0);

echo "===========================================\n";
echo "Update Puller - arsip.p171.net ke data.p171.net\n";
echo "===========================================\n\n";

// Jalankan check_update.php untuk mendapatkan info update
echo "[1/4] Memeriksa update yang tersedia...\n";

// Jalankan check_update.php via command line
$check_cmd = "php " . escapeshellarg(__DIR__ . '/update/check_update.php');
$check_result = shell_exec($check_cmd);

if (!$check_result) {
    die("Error: Gagal menjalankan check_update.php\n");
}

echo "Raw response: $check_result\n";

$update_data = json_decode($check_result, true);

if (!$update_data) {
    die("Error: Gagal parsing JSON response.\nResponse: $check_result\n");
}

echo "Response dari server:\n";
print_r($update_data);
echo "\n";

// Cek apakah ada update
if (isset($update_data[0]['update_available']) && $update_data[0]['update_available'] === true) {
    $current_version = $update_data[0]['current_version'];
    $latest_version = $update_data[1]['latest_version'];
    $download_url = $update_data[1]['download_url'];
    $release_notes = $update_data[1]['release_notes'];
    
    echo "✓ Update tersedia!\n";
    echo "  Versi saat ini: $current_version\n";
    echo "  Versi terbaru: $latest_version\n";
    echo "  URL Download: $download_url\n\n";
    echo "Release Notes:\n";
    echo "----------------------------------------\n";
    echo $release_notes . "\n";
    echo "----------------------------------------\n\n";
    
    // Tanya konfirmasi
    echo "Apakah Anda ingin melanjutkan download? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    
    if (trim(strtolower($line)) !== 'y') {
        die("Update dibatalkan.\n");
    }
    
    echo "\n[2/4] Mendownload update...\n";
    
    // Download update
    $download_script = __DIR__ . '/update/download_update.php';
    $progress_file = __DIR__ . '/update/progress.json';
    
    // Reset progress
    @file_put_contents($progress_file, json_encode(['progress' => 0, 'message' => 'Starting...']));
    
    // Jalankan download di background
    $cmd = "php " . escapeshellarg($download_script) . " url=" . escapeshellarg($download_url) . " versi=" . escapeshellarg($latest_version);
    
    // Untuk Windows, gunakan start /B
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        pclose(popen("start /B " . $cmd . " > NUL 2>&1", "r"));
    } else {
        exec($cmd . " > /dev/null 2>&1 &");
    }
    
    // Monitor progress
    $last_percent = -1;
    $max_wait = 300; // 5 menit
    $waited = 0;
    
    while ($waited < $max_wait) {
        if (file_exists($progress_file)) {
            $progress_data = json_decode(file_get_contents($progress_file), true);
            
            if ($progress_data && isset($progress_data['progress'])) {
                $percent = (int)$progress_data['progress'];
                $message = $progress_data['message'] ?? '';
                
                if ($percent !== $last_percent) {
                    echo sprintf("  Progress: %3d%% - %s\n", $percent, $message);
                    $last_percent = $percent;
                }
                
                if ($percent >= 100) {
                    echo "\n✓ Download selesai!\n\n";
                    break;
                }
                
                if ($percent < 0) {
                    die("\n✗ Error: $message\n");
                }
            }
        }
        
        sleep(1);
        $waited++;
    }
    
    if ($waited >= $max_wait) {
        die("\n✗ Timeout: Download memakan waktu terlalu lama.\n");
    }
    
    echo "[3/4] Mengekstrak file update...\n";
    
    $zip_file = __DIR__ . '/update/update.zip';
    $extract_path = __DIR__;
    
    if (!file_exists($zip_file)) {
        die("✗ Error: File update.zip tidak ditemukan.\n");
    }
    
    if (!class_exists('ZipArchive')) {
        die("✗ Error: PHP ZipArchive extension tidak tersedia.\n");
    }
    
    $zip = new ZipArchive;
    $res = $zip->open($zip_file);
    
    if ($res === TRUE) {
        echo "  Mengekstrak ke: $extract_path\n";
        
        // List files yang akan diekstrak
        $file_count = $zip->numFiles;
        echo "  Total file: $file_count\n";
        
        if ($zip->extractTo($extract_path)) {
            echo "  ✓ Ekstraksi berhasil!\n";
            $zip->close();
        } else {
            $zip->close();
            die("  ✗ Error: Gagal mengekstrak file.\n");
        }
    } else {
        die("✗ Error: Gagal membuka file ZIP (Error code: $res)\n");
    }
    
    echo "\n[4/4] Membersihkan file temporary...\n";
    
    // Hapus file zip setelah ekstraksi (opsional)
    // @unlink($zip_file);
    echo "  File update.zip tetap disimpan untuk backup.\n";
    
    echo "\n===========================================\n";
    echo "✓ UPDATE BERHASIL!\n";
    echo "===========================================\n";
    echo "Versi baru: $latest_version\n";
    echo "Silakan restart aplikasi/web server Anda.\n\n";
    
} elseif (isset($update_data[0]['id']) && $update_data[0]['id'] == '4') {
    // Error
    echo "✗ Error: " . ($update_data[0]['Error1'] ?? 'Unknown error') . "\n";
    if (isset($update_data[0]['Detail'])) {
        echo "  Detail: " . $update_data[0]['Detail'] . "\n";
    }
    if (isset($update_data[0]['Hint'])) {
        echo "  Hint: " . $update_data[0]['Hint'] . "\n";
    }
} else {

    // Tidak ada update (Versions match or local is newer)
    echo "✓ Anda sudah menggunakan versi terbaru (atau versi lokal lebih baru).\n";
    echo "  Versi saat ini: " . ($update_data[0]['current_version'] ?? 'unknown') . "\n";
    echo "  Versi terbaru: " . ($update_data[0]['latest_version'] ?? 'unknown') . "\n\n";

    echo "Apakah Anda ingin memaksa update ulang (re-install)? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);

    if (trim(strtolower($line)) === 'y') {
        echo "\n[FORCE UPDATE] Memulai proses update ulang...\n";
        
        // Manual setup for force update
        $latest_version = $update_data[0]['latest_version'] ?? 'unknown';
        // Default URL based on typical structure if not provided in JSON
        $download_url = 'https://arsip.p171.net/update/update.zip'; 
        
        echo "\n[2/4] Mendownload update (FORCE)...\n";
    
        // Download update
        $download_script = __DIR__ . '/update/download_update.php';
        $progress_file = __DIR__ . '/update/progress.json';
        
        // Reset progress
        @file_put_contents($progress_file, json_encode(['progress' => 0, 'message' => 'Starting...']));
        
        // Jalankan download di background
        $cmd = "php " . escapeshellarg($download_script) . " url=" . escapeshellarg($download_url) . " versi=" . escapeshellarg($latest_version);
        
        // Untuk Windows, gunakan start /B
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            pclose(popen("start /B " . $cmd . " > NUL 2>&1", "r"));
        } else {
            exec($cmd . " > /dev/null 2>&1 &");
        }
        
        // Monitor progress
        $last_percent = -1;
        $max_wait = 300; // 5 menit
        $waited = 0;
        
        while ($waited < $max_wait) {
            if (file_exists($progress_file)) {
                $progress_data = json_decode(file_get_contents($progress_file), true);
                
                if ($progress_data && isset($progress_data['progress'])) {
                    $percent = (int)$progress_data['progress'];
                    $message = $progress_data['message'] ?? '';
                    
                    if ($percent !== $last_percent) {
                        echo sprintf("  Progress: %3d%% - %s\n", $percent, $message);
                        $last_percent = $percent;
                    }
                    
                    if ($percent >= 100) {
                        echo "\n✓ Download selesai!\n\n";
                        break;
                    }
                    
                    if ($percent < 0) {
                        die("\n✗ Error: $message\n");
                    }
                }
            }
            
            sleep(1);
            $waited++;
        }
        
        if ($waited >= $max_wait) {
            die("\n✗ Timeout: Download memakan waktu terlalu lama.\n");
        }
        
        echo "[3/4] Mengekstrak file update...\n";
        
        $zip_file = __DIR__ . '/update/update.zip';
        $extract_path = __DIR__;
        
        if (!file_exists($zip_file)) {
            die("✗ Error: File update.zip tidak ditemukan.\n");
        }
        
        if (!class_exists('ZipArchive')) {
            die("✗ Error: PHP ZipArchive extension tidak tersedia.\n");
        }
        
        $zip = new ZipArchive;
        $res = $zip->open($zip_file);
        
        if ($res === TRUE) {
            echo "  Mengekstrak ke: $extract_path\n";
            
            // List files yang akan diekstrak
            $file_count = $zip->numFiles;
            echo "  Total file: $file_count\n";
            
            if ($zip->extractTo($extract_path)) {
                echo "  ✓ Ekstraksi berhasil!\n";
                $zip->close();
            } else {
                $zip->close();
                die("  ✗ Error: Gagal mengekstrak file.\n");
            }
        } else {
            die("✗ Error: Gagal membuka file ZIP (Error code: $res)\n");
        }
        
        echo "\n[4/4] Membersihkan file temporary...\n";
        echo "  File update.zip tetap disimpan untuk backup.\n";
        
        echo "\n===========================================\n";
        echo "✓ FORCE UPDATE BERHASIL!\n";
        echo "===========================================\n";
        echo "Silakan restart aplikasi/web server Anda.\n\n";
    } else {
        echo "Update dibatalkan.\n";
    }
}


echo "\n";
?>
