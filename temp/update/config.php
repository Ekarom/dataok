<?php
/**
 * Update System Configuration
 * Centralized configuration for update server endpoints
 */

// ============================================
// UPDATE SERVER CONFIGURATION
// ============================================

// Mode: 'local' atau 'remote'
// local  = Update dari localhost/data/update/
// remote = Update dari server eksternal
define('UPDATE_MODE', 'remote'); // Ubah ke 'local' untuk testing lokal

// Remote Update Server URLs
define('REMOTE_UPDATE_SERVER', 'https://arsip.p171.net/update/files.php');
define('REMOTE_FILES_BASE', 'https://arsip.p171.net/update/files/');

// Local Update Server URLs
define('LOCAL_UPDATE_SERVER', 'http://localhost/data/update/files.php');
define('LOCAL_FILES_BASE', 'http://localhost/data/update/files/');

// ============================================
// ACTIVE CONFIGURATION (Auto-selected)
// ============================================

if (UPDATE_MODE === 'remote') {
    define('UPDATE_SERVER_URL', REMOTE_UPDATE_SERVER);
    define('UPDATE_FILES_BASE', REMOTE_FILES_BASE);
} else {
    define('UPDATE_SERVER_URL', LOCAL_UPDATE_SERVER);
    define('UPDATE_FILES_BASE', LOCAL_FILES_BASE);
}

// ============================================
// UPDATE SETTINGS
// ============================================

// Timeout untuk koneksi update (detik)
define('UPDATE_TIMEOUT', 15);

// Retry attempts jika gagal
define('UPDATE_RETRY_ATTEMPTS', 2);

// Enable debug mode (tampilkan error detail)
define('UPDATE_DEBUG', false);

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Get current update mode
 * @return string 'local' or 'remote'
 */
function getUpdateMode() {
    return UPDATE_MODE;
}

/**
 * Get update server URL
 * @return string
 */
function getUpdateServerUrl() {
    return UPDATE_SERVER_URL;
}

/**
 * Get files base URL
 * @return string
 */
function getFilesBaseUrl() {
    return UPDATE_FILES_BASE;
}

/**
 * Check if using remote server
 * @return bool
 */
function isRemoteUpdate() {
    return UPDATE_MODE === 'remote';
}

/**
 * Check if using local server
 * @return bool
 */
function isLocalUpdate() {
    return UPDATE_MODE === 'local';
}
?>
