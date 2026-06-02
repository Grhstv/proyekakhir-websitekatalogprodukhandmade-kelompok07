<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. Hapus semua variabel session
session_unset();

// 2. Hancurkan session yang ada di server
session_destroy();

// 3. Bersihkan cookie session jika ada (opsional namun baik untuk keamanan)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Tendang kembali ke halaman login
header("Location: login.php");
exit;
?>