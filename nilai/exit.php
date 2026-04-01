<?php
// Start session
session_name('NILAISESSID');
session_start();

// Hapus semua variabel session
session_unset();
session_destroy();

// Hapus cookies yang digunakan saat login (Kecuali device_token agar "Ingat Saya" persisten)
$cookies = ['id', 'nama', 'level', 'tapel', 'poto', 'nik', 'username', 'password', 'scr'];

foreach ($cookies as $cookie) {
    if (isset($_COOKIE[$cookie])) {
        setcookie($cookie, '', time() - 3600, "/");
        unset($_COOKIE[$cookie]);
    }
}

// Redirect langsung ke login
header("Location: login");
exit();
?>
<script>
    // Disable back button after logout
    function disableBackButton() {
        window.history.forward();
    }
    setTimeout("disableBackButton()", 0);
</script>