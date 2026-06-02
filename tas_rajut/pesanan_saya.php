<?php
require_once 'config/koneksi.php';
require_once 'middleware/auth_check.php';

// Proteksi keamanan data: Ambil pesanan milik user yang sedang login saja
$id_user_aktif = $_SESSION['user_id'];

$query = "SELECT t.*, p.nama_barang, p.harga_satuan, p.gambar, p.pembuat 
          FROM transaksi t 
          INNER JOIN produk p ON t.id_produk = p.id 
          WHERE t.id_user = ? 
          ORDER BY t.waktu_pesan DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_user_aktif);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - TasRajut</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        html.dark body { background-color: #09090b !important; color: #e4e4e7 !important; }
        html.dark header { background-color: rgba(24, 24, 27, 0.8) !important; border-color: rgba(39, 39, 42, 0.5) !important; }
        html.dark .bg-white { background-color: #18181b !important; border-color: #27272a !important; }
        html.dark h1, html.dark h3, html.dark .text-gray-900, html.dark text-[#2D2D2D] { color: #f4f4f5 !important; }
        html.dark p, html.dark span, html.dark div { color: #a1a1aa; }
        html.dark .text-gray-800 { color: #e4e4e7 !important; }
        html.dark .text-orange-600, html.dark .text-orange-500 { color: #f97316 !important; }
        html.dark .border-gray-100, html.dark .border-gray-200, html.dark .border-orange-100\/50 { border-color: #27272a !important; }
        html.dark .bg-gray-50 { background-color: #09090b !important; }
    </style>

    <script>
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="bg-[#FDF9F6] dark:bg-zinc-950 text-gray-800 dark:text-zinc-200 min-h-screen pb-12 transition-colors duration-300">

    <header class="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-md border-b border-orange-100/50 dark:border-zinc-800/50 sticky top-0 z-50 px-6 py-4 flex justify-between items-center max-w-6xl mx-auto rounded-b-2xl shadow-xs transition-colors duration-300">
        <a href="index.php" class="text-xl font-black tracking-tight text-[#2D2D2D] dark:text-zinc-100">Tas<span class="text-orange-600 dark:text-orange-500">Rajut</span></a>
        <a href="index.php" class="text-xs font-bold text-orange-600 dark:text-orange-400 hover:underline">← Kembali ke Beranda</a>
    </header>

    <main class="max-w-4xl mx-auto mt-8 px-4 space-y-6">
        <div class="border-b border-gray-200 dark:border-zinc-800 pb-2">
            <h1 class="text-2xl font-black text-gray-900 dark:text-zinc-100">Riwayat Pesanan Anda</h1>
            <p class="text-xs text-gray-400 dark:text-zinc-500 mt-0.5">Daftar produk rajutan hand-made yang baru saja Anda pesan.</p>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="space-y-4">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800/80 p-5 shadow-2xs flex flex-col md:flex-row justify-between gap-6 hover:border-orange-200 dark:hover:border-orange-900/50 transition duration-300">
                        
                        <!-- DETAIL UTAMA BARANG -->
                        <div class="flex gap-4 items-start">
                            <div class="w-20 h-20 bg-gray-50 dark:bg-zinc-950 rounded-xl overflow-hidden border border-gray-100 dark:border-zinc-800 flex-shrink-0">
                                <?php 
                                $src_gambar = (strpos($row['gambar'], 'http') === 0) ? $row['gambar'] : "uploads/" . $row['gambar'];
                                ?>
                                <img src="<?= htmlspecialchars($src_gambar) ?>" class="w-full h-full object-cover">
                            </div>
                            <div>
                                <span class="text-[9px] font-bold px-2 py-0.5 bg-orange-50 dark:bg-orange-950/40 text-orange-700 dark:text-orange-400 rounded-md uppercase tracking-wider border border-orange-100/20 dark:border-orange-900/30"><?= $row['nota_trx'] ?></span>
                                <h3 class="text-base font-black text-gray-900 dark:text-zinc-100 mt-1"><?= htmlspecialchars($row['nama_barang']) ?></h3>
                                <p class="text-xs text-gray-400 dark:text-zinc-500">Oleh: <?= htmlspecialchars($row['pembuat']) ?></p>
                                <p class="text-xs font-bold text-orange-600 dark:text-orange-500 mt-1">Rp <?= number_format($row['harga_satuan'], 0, ',', '.') ?></p>
                            </div>
                        </div>

                        <!-- ALAMAT & PENGIRIMAN -->
                        <div class="text-xs space-y-1 border-t md:border-t-0 md:border-l border-gray-100 dark:border-zinc-800 pt-3 md:pt-0 md:pl-6 flex-1">
                            <p class="text-[10px] font-bold text-gray-400 dark:text-zinc-500 uppercase tracking-wider">Detail Pengiriman:</p>
                            <p class="font-bold text-gray-800 dark:text-zinc-200"><?= htmlspecialchars($row['nama_penerima']) ?> <span class="text-gray-400 dark:text-zinc-500 font-normal">(<?= htmlspecialchars($row['no_hp']) ?>)</span></p>
                            <p class="text-gray-500 dark:text-zinc-400 leading-relaxed max-w-md"><?= htmlspecialchars($row['alamat']) ?></p>
                            <p class="text-gray-400 dark:text-zinc-500 mt-1">Kurir: <span class="font-bold text-gray-700 dark:text-zinc-300"><?= htmlspecialchars($row['ekspedisi']) ?></span></p>
                        </div>

                        <!-- STATUS & WAKTU (DINAMIS SINKRON DENGAN ADMIN) -->
                        <div class="flex flex-col justify-between items-end border-t md:border-t-0 border-gray-100 dark:border-zinc-800 pt-3 md:pt-0 text-right">
                            <div class="text-[10px] text-gray-400 dark:text-zinc-500 font-medium"><?= date('d M Y, H:i', strtotime($row['waktu_pesan'])) ?> WIB</div>
                            
                            <?php if ($row['status'] === 'Diproses'): ?>
                                <span class="inline-flex items-center gap-1 bg-blue-50 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400 px-3 py-1 rounded-full text-[10px] font-bold border border-blue-200/30 dark:border-blue-900/30">
                                Pesanan Diproses
                                </span>
                            <?php elseif ($row['status'] === 'Selesai'): ?>
                                <span class="inline-flex items-center gap-1 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 px-3 py-1 rounded-full text-[10px] font-bold border border-emerald-200/30 dark:border-emerald-900/30">
                                Selesai / Dikirim
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1 bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 px-3 py-1 rounded-full text-[10px] font-bold border border-amber-200/30 dark:border-amber-900/30">
                                Menunggu Konfirmasi
                                </span>
                            <?php endif; ?>
                        </div>

                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 p-12 text-center space-y-3">
                <p class="text-sm font-bold text-gray-700 dark:text-zinc-300">Belum ada pesanan aktif</p>
                <p class="text-xs text-gray-400 dark:text-zinc-500 max-w-xs mx-auto">Silakan jelajahi katalog halaman utama untuk memesan produk rajutan favoritmu.</p>
                <a href="index.php" class="inline-block bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition mt-2 shadow-xs">Belanja Sekarang</a>
            </div>
        <?php endif; ?>
    </main>

</body>
</html>
<?php 
$stmt->close();
$conn->close(); 
?>