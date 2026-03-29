<?php
header('Content-Type: application/json');
error_reporting(E_ALL); 
ini_set('display_errors', 0); // Don't break JSON with raw errors

/**
 * Helper untuk fetch URL dengan User-Agent & SSL Bypass
 */
function fetchContent($url) {
    if (empty($url)) return false;
    $arrContextOptions = array(
        "ssl" => array(
            "verify_peer" => false,
            "verify_peer_name" => false,
        ),
        "http" => array(
            "timeout" => 10,
            "user_agent" => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
            "ignore_errors" => true 
        )
    );
    
    $context = stream_context_create($arrContextOptions);
    $content = @file_get_contents($url, false, $context);
    
    // Cek HTTP Status Code
    if (isset($http_response_header)) {
        if (isset($http_response_header[0]) && preg_match('{HTTP\/\S+\s+(\d+)}', $http_response_header[0], $matches)) {
            $status = $matches[1] ?? 'unknown';
            if ($status !== '200') {
                return false;
            }
        }
    }
    
    return $content;
}

function remoteUrlExists(string $url): bool {
    $content = fetchContent($url);
    return ($content !== false && strlen($content) > 0);
}

/**
 * Mengekstrak catatan rilis DAN URL unduhan dari blok log.
 */
function extract_log_data(string $full_log_content, string $latest_version): array {
    // Split berdasarkan format "X.X.X YYYYMMDD" (Lookahead untuk angka.angka.angka spasi angka)
    $blocks = preg_split('/(?=^\d+\.\d+\.\d+\s+\d+)/m', $full_log_content, -1, PREG_SPLIT_NO_EMPTY);
    $found_block_content = null;

    foreach ($blocks as $block) {
        if (str_starts_with(trim($block), $latest_version)) {
            $found_block_content = trim($block);
            break;
        }
    }

    if ($found_block_content) {
        $download_url = null;
        $release_notes = $found_block_content;
        
        // Cari URL zip di dalam blok
        if (preg_match('/^(https?:\/\/[^\s]+\.zip)$/m', $found_block_content, $url_matches)) {
            $download_url = $url_matches[1];
            // Hapus baris URL dari release notes agar tidak double
            $release_notes = preg_replace('/^' . preg_quote($download_url, '/') . '\R?/m', '', $release_notes);
        }
        return ['notes' => trim($release_notes), 'url' => $download_url];
    }

    return ['notes' => "Catatan rilis untuk $latest_version tidak ditemukan.", 'url' => null];
}

// Config
$remote_base = 'https://arsip.p171.net/update/files';
$remote_logs_url = "$remote_base/logs.txt";
$default_download_url = "$remote_base/update.zip";

// 1. Ambil Versi Lokal (Prioritas database via konek.php, fallback file)
$current_version = '0.0.0';
ob_start(); 
try {
    $konek_path = dirname(__DIR__) . '/cfg/konek.php';
    if (file_exists($konek_path)) {
        include $konek_path;
        if (isset($ver)) $current_version = $ver;
    }
} catch (Throwable $t) {}
ob_end_clean();

// Fallback ke current_version.txt jika $ver tidak ada atau masih 0.0.0
if ($current_version === '0.0.0' || empty($current_version)) {
    $local_v_file = __DIR__ . '/current_version.txt';
    if (file_exists($local_v_file)) {
        $current_version = trim(file_get_contents($local_v_file));
    }
}
if (empty($current_version)) $current_version = '0.0.0';

$response = [];
$local_logs_path = __DIR__ . '/files/logs.txt';
$all_log_content = false;
$debug_error_msg = "";

// 2. Fetch Remote Logs
$remote_content = fetchContent($remote_logs_url);
if ($remote_content !== false && strlen($remote_content) > 10) {
    $all_log_content = $remote_content;
} else {
    $err = error_get_last();
    $debug_error_msg = isset($err['message']) ? $err['message'] : "Gagal terhubung ke server remote ($remote_logs_url)";
}

// 3. Fallback Local Logs
if ($all_log_content === false && file_exists($local_logs_path)) {
    $all_log_content = file_get_contents($local_logs_path);
}   

if ($all_log_content !== false) {
    // Cari versi terbaru di baris pertama
    if (preg_match('/^(\d+\.\d+\.\d+)/', trim($all_log_content), $matches)) {
        $latest_version = $matches[1];
    } else {
        $latest_version = '0.0.0';
    }
    
    $response[0] = [
        'id'               => '2', 
        'update_available' => false, 
        'current_version'  => $current_version,
        'latest_version'   => $latest_version
    ];

    if (version_compare($current_version, $latest_version, '<')) {        
        $log_data = extract_log_data($all_log_content, $latest_version);
        $release_notes = $log_data['notes'];
        $download_url = !empty($log_data['url']) ? $log_data['url'] : $default_download_url;
        
        // Simpan catatan rilis untuk dipakai download_update.php
        @file_put_contents(__DIR__ . '/release_notes.txt', $release_notes);
        
        $response[0]['update_available'] = true;
        $response[1] = [
            'id'             => '2',
            'latest_version' => $latest_version,
            'download_url'   => $download_url,
            'release_notes'  => $release_notes
        ];
    }
    echo json_encode($response);
} else {   
    echo json_encode([[
        'id' => '4',
        'Error1' => 'Gagal mengambil data update.',
        'Detail' => $debug_error_msg,
        'Hint' => 'Periksa koneksi internet atau coba lagi nanti.'
    ]]);
}
?>
