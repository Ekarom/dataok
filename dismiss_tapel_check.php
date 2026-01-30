<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['tapel_check_dismissed'] = true;
echo "success";
?>
