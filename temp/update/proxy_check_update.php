<?php
/**
 * Proxy untuk mengakses arsip.p171.net/update/files.php
 * Workaround untuk masalah 403 Forbidden
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$currentVersion = $_GET['current'] ?? '0.0.0';
$remoteUrl = 'https://arsip.p171.net/update/files.php?current=' . urlencode($currentVersion);

// Gunakan cURL dengan headers yang lebih lengkap
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $remoteUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => 0,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_HTTPHEADER => [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        'Accept: application/json',
        'Cache-Control: no-cache'
    ]
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Log untuk debugging
error_log("Proxy Check Update - HTTP Code: $httpCode, URL: $remoteUrl");

if ($httpCode === 200 && $response) {
    // Validasi JSON response
    $jsonData = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo $response;
    } else {
        http_response_code(500);
        echo json_encode([
            'error' => true,
            'message' => 'Respon JSON tidak valid dari server',
            'raw_response' => substr($response, 0, 200)
        ]);
    }
} else {
    // Return error dengan detail
    http_response_code($httpCode ?: 500);
    echo json_encode([
        'error' => true,
        'message' => 'Gagal mengambil data dari server remote',
        'http_code' => $httpCode,
        'curl_error' => $curlError,
        'remote_url' => $remoteUrl
    ]);
}
?>
