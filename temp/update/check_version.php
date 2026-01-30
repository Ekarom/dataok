<?php
/**
 * Update Server Endpoint (Dynamic Scanner)
 * URL: http://localhost/data/update/check_version.php
 * 
 * Scans the 'files' directory for update packages (e.g., update_1.0.5.zip)
 * and returns the latest version information.
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$filesDir = __DIR__ . '/files/';
// Detect URL path relative to server root
// Simplified: Use relative path for internal consistency
$baseUrl = "files/";

// 1. Scan directory for zip files
$files = glob($filesDir . 'update_*.zip');

$latestVersion = '0.0.0';
$latestFile = '';

foreach ($files as $file) {
    $basename = basename($file);
    // Extract version from filename: update_1.2.3.zip -> 1.2.3
    if (preg_match('/update_([0-9.]+)\.zip/', $basename, $matches)) {
        $version = $matches[1];
        if (version_compare($version, $latestVersion, '>')) {
            $latestVersion = $version;
            $latestFile = $basename;
        }
    }
}

if ($latestFile) {
    $response = array(
        'version' => $latestVersion,
        'message' => "Update version $latestVersion found.",
        'download_url' => $baseUrl . $latestFile,
        'release_date' => date('Y-m-d', filemtime($filesDir . $latestFile))
    );
} else {
    $response = array(
        'version' => '0.0.0',
        'message' => 'No updates available.',
        'download_url' => '',
        'release_date' => date('Y-m-d')
    );
}

echo json_encode($response);
exit;
?>
