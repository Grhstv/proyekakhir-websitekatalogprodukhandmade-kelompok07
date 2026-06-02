<?php
require_once 'config/koneksi.php';
require_once 'middleware/auth_check.php';

if ($_SESSION['role'] !== 'admin') { die("Akses ditolak!"); }

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $stmt = $conn->prepare("SELECT gambar FROM produk WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($row = $res->fetch_assoc()) {
        if (!empty($row['gambar']) && file_exists('uploads/' . $row['gambar'])) {
            unlink('uploads/' . $row['gambar']);
        }
    }
    $stmt->close();
    
    $del_stmt = $conn->prepare("DELETE FROM produk WHERE id = ?");
    $del_stmt->bind_param("i", $id);
    $del_stmt->execute();
    $del_stmt->close();
}

header("Location: index.php");
exit;
?>