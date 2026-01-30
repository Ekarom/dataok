<?php
/**
 * Update Server Endpoint (Files Scanner)
 * Scans for available update files and returns version information
 * URL: /update/files.php
 */

// Load configuration
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Files directory (local to this script)
$filesDir = __DIR__ . '/files/';

// Get current version from client
$current = isset($_GET['current']) ? trim($_GET['current']) : '1.0.0';

// Validate version format
if (!preg_match('/^\d+\.\d+\.\d+$/', $current)) {
    $current = '1.0.0';
}

// Starting version limit (minimum version to offer)
$startFrom = '1.0.1';

// Scan for .zip files
$files = glob($filesDir . '*.zip');
$targetVersion = null;
$targetFile = null;

if ($files && count($files) > 0) {
    // Collect all valid version numbers
    $versions = [];
    foreach ($files as $file) {
        $basename = basename($file);
        if (preg_match('/^(\d+\.\d+\.\d+)\.zip$/', $basename, $matches)) {
            $versions[] = $matches[1];
        }
    }
    
    // Sort versions in ascending order
    usort($versions, 'version_compare');
    
    // Find the NEXT version in sequence (first version > current)
    foreach ($versions as $v) {
        // Skip versions lower than minimum
        if (version_compare($v, $startFrom, '<')) {
            continue;
        }
        
        // Find first version strictly greater than current
        if (version_compare($v, $current, '>')) {
            $targetVersion = $v;
            $targetFile = $v . '.zip';
            break;
        }
    }
}

// Build response
if ($targetVersion && $targetFile) {
    // Update available
    $msgFile = $filesDir . $targetVersion . '.json';
    $message = file_exists($msgFile) 
        ? file_get_contents($msgFile) 
        : "Versi terbaru $targetVersion sudah tersedia.";
    
    // Construct download URL based on mode
    $downloadUrl = getFilesBaseUrl() . $targetFile;
    
    $response = [
        'status' => 'update_available',
        'version' => $targetVersion,
        'current' => $current,
        'message' => $message,
        'download_url' => $downloadUrl,
        'file_size' => file_exists($filesDir . $targetFile) ? filesize($filesDir . $targetFile) : null,
        'mode' => getUpdateMode()
    ];
} else {
    // No update available
    $response = [
        'status' => 'up_to_date',
        'version' => $current,
        'current' => $current,
        'message' => 'Sistem sudah menggunakan versi terbaru.',
        'download_url' => '',
        'mode' => getUpdateMode()
    ];
}

// Add debug info if enabled
if (UPDATE_DEBUG) {
    $response['debug'] = [
        'files_dir' => $filesDir,
        'files_found' => $files ? count($files) : 0,
        'versions_available' => isset($versions) ? $versions : [],
        'start_from' => $startFrom
    ];
}

echo json_encode($response, JSON_PRETTY_PRINT);
exit;
?>
