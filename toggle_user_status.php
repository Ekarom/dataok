<?php
// Start session first
session_start();

// AJAX handler for toggling user status
header('Content-Type: application/json');

// Check authentication before including files
if (!isset($_SESSION['skradm'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access. Please login first.'
    ]);
    exit();
}

// Include database connection only (skip secure.php to avoid redirect)
include "cfg/konek.php";
include "cfg/logger.php";

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'new_status' => null
];

// Validate database connection
if (!isset($sqlconn) || $sqlconn === false) {
    $response['message'] = 'Database connection error';
    echo json_encode($response);
    exit();
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit();
}

// Validate required parameters
if (!isset($_POST['id']) || !isset($_POST['current_status'])) {
    $response['message'] = 'Missing required parameters';
    echo json_encode($response);
    exit();
}

// Sanitize input
$user_id = mysqli_real_escape_string($sqlconn, $_POST['id']);
$current_status = mysqli_real_escape_string($sqlconn, $_POST['current_status']);

// Validate user ID is numeric
if (!is_numeric($user_id)) {
    $response['message'] = 'Invalid user ID';
    echo json_encode($response);
    exit();
}

// Check if user exists
$check_query = mysqli_query($sqlconn, "SELECT status, userid, nama FROM usera WHERE id = '$user_id'");

if (!$check_query) {
    $response['message'] = 'Database query error: ' . mysqli_error($sqlconn);
    echo json_encode($response);
    exit();
}

$user_data = mysqli_fetch_array($check_query);

if (!$user_data) {
    $response['message'] = 'User not found';
    echo json_encode($response);
    exit();
}

// Toggle status (0 to 1, or 1 to 0)
$new_status = ($current_status == "1") ? "0" : "1";

// Update user status
$update_query = mysqli_query($sqlconn, "UPDATE usera SET status = '$new_status' WHERE id = '$user_id'");

if ($update_query) {
    // Log the action
    $status_text = ($new_status == "1") ? "Aktif" : "Non-Aktif";
    $userid_target = $user_data['nama'] ?? $user_data['userid'];
    write_log("EDIT", "User: $userid_target - Berhasil Di Ubah Status Menjadi $status_text");

    // Prepare success response
    $response['success'] = true;
    $response['new_status'] = (int)$new_status;
    $response['message'] = "User: $userid_target - Berhasil Di Ubah Status Menjadi $status_text";
}
else {
    $response['message'] = 'Failed to update status: ' . mysqli_error($sqlconn);
}

// Return JSON response
echo json_encode($response);
exit();
?>
