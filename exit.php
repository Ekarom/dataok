<?php
// Start session
session_start();

// Hapus semua variabel session
session_unset();
session_destroy();

// Hapus cookies yang digunakan saat login
$cookies = ['id', 'nama', 'level', 'tapel', 'poto', 'nik', 'username', 'password', 'scr', 'device_token'];

foreach ($cookies as $cookie) {
    if (isset($_COOKIE[$cookie])) {
        setcookie($cookie, '', time() - 3600, "/");
        unset($_COOKIE[$cookie]);
    }
}

// Redirect langsung ke login.php
header("Location: login.php");
exit();
?>
<script>
    // Disable back button after logout
    function disableBackButton() {
        window.history.forward();
    }
    setTimeout("disableBackButton()", 0);
</script>
