<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Proteksi: Jika belum login, tendang kembali ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>