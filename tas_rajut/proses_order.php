<?php
require_once 'config/koneksi.php';
require_once 'middleware/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Ambil ID User yang sedang aktif/login dari session
    $id_user_aktif = $_SESSION['user_id']; 

    $id_barang = intval($_POST['id_barang']);
    $nama_penerima = trim($_POST['nama_penerima']);
    $no_hp = trim($_POST['no_hp']);
    $alamat = trim($_POST['alamat']);
    $ekspedisi = trim($_POST['ekspedisi']);
    $nota_random = "TRX-" . strtoupper(uniqid());

    // 2. Cek ketersediaan stok
    $stmt = $conn->prepare("SELECT stok, nama_barang FROM produk WHERE id = ?");
    $stmt->bind_param("i", $id_barang);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if ($res && $res['stok'] > 0) {
        $stok_terbaru = $res['stok'] - 1;
        $nama_barang = $res['nama_barang'];

        // 3. Kurangi stok produk secara otomatis
        $up_stmt = $conn->prepare("UPDATE produk SET stok = ? WHERE id = ?");
        $up_stmt->bind_param("ii", $stok_terbaru, $id_barang);
        $up_stmt->execute();
        $up_stmt->close();

        // 4. SIMPAN DATA KE TABEL TRANSAKSI (Sudah include id_user dan status)
        $ins_stmt = $conn->prepare("INSERT INTO transaksi (nota_trx, id_user, id_produk, nama_penerima, no_hp, alamat, ekspedisi, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Menunggu')");
        
        // Jenis data: s (string), i (integer), i (integer), s (string), s (string), s (string), s (string) -> "siissss"
        $ins_stmt->bind_param("siissss", $nota_random, $id_user_aktif, $id_barang, $nama_penerima, $no_hp, $alamat, $ekspedisi);
        $ins_stmt->execute();
        $ins_stmt->close();
        
        // Tutup statement dan koneksi database di sini (sebelum render HTML)
        $stmt->close();
        $conn->close();

        // 5. Tampilkan Halaman Sukses
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <title>Pemesanan Berhasil!</title>
            <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
        </head>
        <body class="bg-[#FDF9F6] min-h-screen flex items-center justify-center p-4">
            <div class="bg-white max-w-md w-full p-8 rounded-3xl shadow-xl border border-orange-100 text-center space-y-6">
                <div class="w-20 h-20 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center text-4xl mx-auto">✓</div>
                <div class="space-y-2">
                    <h2 class="text-2xl font-black text-gray-900">Pesanan Diterima!</h2>
                    <p class="text-sm text-gray-500">Pesanan Anda telah tercatat di sistem riwayat pesanan.</p>
                </div>
                
                <div class="bg-gray-50 rounded-2xl p-4 text-left text-xs space-y-2.5 border border-dashed border-gray-200">
                    <div class="flex justify-between"><span class="text-gray-400">Nomor Nota:</span> <span class="font-bold text-gray-800"><?= $nota_random ?></span></div>
                    <div class="flex justify-between"><span class="text-gray-400">Produk:</span> <span class="font-bold text-gray-800"><?= htmlspecialchars($nama_barang) ?></span></div>
                    <div class="flex justify-between"><span class="text-gray-400">Kurir:</span> <span class="font-bold text-orange-600"><?= htmlspecialchars($ekspedisi) ?></span></div>
                </div>

                <div class="flex flex-col gap-2">
                    <a href="pesanan_saya.php" class="block w-full bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold py-3.5 rounded-xl transition shadow-md text-center">Lihat Pesanan Saya</a>
                    <a href="index.php" class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold py-3.5 rounded-xl transition text-center">Kembali ke Beranda</a>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    } else {
        // Jika stok habis, pastikan koneksi awal tetap ditutup
        $stmt->close();
        $conn->close();
        echo "<script>alert('Maaf, stok produk habis!'); window.location.href='index.php';</script>";
    }
}
?>