<?php
require_once 'config/koneksi.php';
require_once 'middleware/auth_check.php';

// Pastikan request datang menggunakan metode POST dan membawa parameter ID barang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    
    // 1. Ambil data stok produk yang ada di database saat ini
    $stmt = $conn->prepare("SELECT stok FROM produk WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($row = $res->fetch_assoc()) {
        $stok_sekarang = $row['stok'];
        
        // 2. Cek apakah stok masih tersedia atau sudah 0
        if ($stok_sekarang > 0) {
            $stok_terbaru = $stok_sekarang - 1;
            
            // 3. Update / Kurangi stok di database
            $up_stmt = $conn->prepare("UPDATE produk SET stok = ? WHERE id = ?");
            $up_stmt->bind_param("ii", $stok_terbaru, $id);
            $up_stmt->execute();
            $up_stmt->close();
            
            // 4. Kirim respon balik ke JavaScript (Format: STATUS|PESAN|SISA_STOK)
            echo "BERHASIL|Nomor Order Anda: TRX-RAJUT-" . rand(100, 999) . "|" . $stok_terbaru;
        } else {
            // Jika stok 0 atau kurang
            echo "GAGAL|Koleksi rajutan ini telah habis dipesan pengguna lain.|0";
        }
    } else {
        // Jika ID barang tidak ditemukan di database
        echo "GAGAL|ID Produk tidak terdaftar di sistem sistem kami.|0";
    }
    $stmt->close();
} else {
    // Jika diakses langsung secara ilegal tanpa method POST
    echo "GAGAL|Metode akses tidak diizinkan.|0";
}

$conn->close();
?>