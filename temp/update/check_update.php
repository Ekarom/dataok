<?php
// c:\wamp64\www\data\update\check_update.php
header('Content-Type: application/json');

// URL Server Update Utama (yang sudah kita buat sebelumnya)
$updateServerUrl = "http://localhost/update/files.php";

// Ambil versi saat ini dari sistem
$currentVersion = "1.0.1"; // Default
if (file_exists("../cfg/konek.php")) {
    // Gunakan output buffering untuk mencegah output tidak sengaja dari konek.php (misal spasi/error) merusak JSON
    ob_start();
    include "../cfg/konek.php";
    ob_end_clean();
    if (isset($app_version)) {
        $currentVersion = $app_version;
    }
}

// Curl ke server update
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $updateServerUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Ikuti redirect (301/302)
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200 && $response) {
    $remoteData = json_decode($response, true);
    
    if ($remoteData && isset($remoteData['version'])) {
        $latestVersion = $remoteData['version'];
        
        // Logika perbandingan versi sederhana
        if (version_compare($latestVersion, $currentVersion, '>')) {
            // Ada update
            $output = array(
                array("update_available" => true),
                array(
                    "latest_version" => $latestVersion,
                    "release_notes" => $remoteData['message'],
                    "download_url" => $remoteData['download_url']
                )
            );
        } else {
            // Sudah versi terbaru
            $output = array(
                array("id" => "0", "message" => "You are on the latest version.")
            );
        }
    } else {
        // Respon server tidak valid
        $output = array(
            array("id" => "1", "Error1" => "Invalid response from update server.")
        );
    }
} else {
    // Gagal koneksi
    $output = array(
        array("id" => "1", "Error1" => "Could not connect to update server (HTTP $httpCode).")
    );
}

echo json_encode($output);
?>
