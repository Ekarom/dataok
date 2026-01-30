<?php
// c:\wamp64\www\data\update\get_progress.php
session_start();
header('Content-Type: application/json');

// Update progress retrieval
if (!isset($_SESSION['update_progress'])) {
    $_SESSION['update_progress'] = 0;
    $_SESSION['update_message'] = "Waiting...";
}

$progress = $_SESSION['update_progress'];
$message = isset($_SESSION['update_message']) ? $_SESSION['update_message'] : '';

// Optional: Auto reset if complete or error to avoid sticking
if ($progress >= 100 || $progress < 0) {
    // Keep the message for one last poll then maybe reset? 
    // Usually client handles stopping the poll.
}

echo json_encode([
    'progress' => $progress,
    'message' => $message
]);
?>
