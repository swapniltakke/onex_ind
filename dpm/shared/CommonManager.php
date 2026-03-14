<?php
$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
if (strpos($current_url, 'dpm') !== false) {
    if (!isset($_SESSION['username']) || $_SESSION['username'] == '') {
        echo '<script type="text/JavaScript">';
        echo 'top.window.document.location="logout.php"';
        echo '</script>';
        exit();
    }
}
?>