<?php
require_once 'config/koneksi.php';
require_once 'middleware/auth_check.php';

$search_query = "%";
$search_text = "";

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_text = trim($_GET['search']); 
    $search_query = "%" . $search_text . "%";
}

$stmt = $conn->prepare("SELECT * FROM produk WHERE nama_barang LIKE ? OR kategori LIKE ? ORDER BY id DESC");
$stmt->bind_param("ss", $search_query, $search_query);
$stmt->execute();
$result = $stmt->get_result();
$result_data = [];

while ($row = $result->fetch_assoc()) {
    $result_data[] = $row;
}

$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ResepRajut - Temukan & Bagikan Desain Tas Rajut Terbaik</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Gaya CSS khusus untuk font, transisi warna, dan pengaturan Dark Mode manual */
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-serif-title { font-family: 'Playfair Display', serif; }
        html.dark body { background-color: #09090b !important; color: #e4e4e7 !important; }
        html.dark nav { background-color: rgba(24, 24, 27, 0.8) !important; border-color: rgba(39, 39, 42, 0.5) !important; }
        html.dark .bg-white, html.dark .bg-\[\#FDF6F0\] { background-color: #18181b !important; border-color: #27272a !important; }
        html.dark h2, html.dark h3, html.dark h4, html.dark .text-gray-900, html.dark .text-\[\#2D2D2D\] { color: #f4f4f5 !important; }
        html.dark p, html.dark span, html.dark td, html.dark th { color: #a1a1aa !important; }
        html.dark .text-orange-600, html.dark .text-orange-500 { color: #f97316 !important; }
        html.dark .bg-gray-50, html.dark .bg-\[\#FFF9F5\], html.dark .bg-gray-50\/50 { background-color: #09090b !important; border-color: #27272a !important; }
        html.dark border-gray-100, html.dark .border-orange-100\/30, html.dark .border-orange-100\/40, html.dark .border-gray-50 { border-color: #27272a !important; }
        html.dark #theme-toggle { background-color: #27272a !important; color: #f97316 !important; border-color: #3f3f46 !important; }
        html.dark input { color: #f4f4f5 !important; }
    </style>
    
    <script>
        /* Fitur Auto-Dark Mode: Mengecek memori browser (localStorage) atau sistem operasi user.
           Jika sebelumnya diatur 'dark', maka html otomatis ditambahkan class 'dark' sebelum halaman dirender */
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="bg-[#FAF6F0] dark:bg-zinc-950 text-gray-800 dark:text-zinc-200 min-h-screen transition-colors duration-300">
    
    <nav class="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-md sticky top-0 z-50 border-b border-orange-100/30 dark:border-zinc-800/50 px-4 py-3 transition-colors duration-300">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-8">
                <a href="index.php" class="flex items-center gap-2">
                    <span class="text-2xl font-black tracking-tight text-orange-600 font-serif-title">
                        Tas<span class="text-[#2D2D2D] dark:text-zinc-100 font-sans font-medium text-xl">Rajut</span>
                    </span>
                </a>
                <div class="hidden lg:flex items-center gap-6 text-sm font-semibold">
                    <a href="index.php" class="text-orange-600 bg-orange-50 dark:bg-orange-950/40 dark:text-orange-400 px-3 py-1.5 rounded-lg">Beranda</a>
                    <a href="pesanan_saya.php" class="text-gray-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 font-bold text-sm transition flex items-center gap-1">Pesanan Saya</a>
                    
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="admin_katalog.php" class="text-gray-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 font-bold text-sm transition flex items-center gap-1">Kelola Katalog</a>
                    <a href="admin_users.php" class="text-gray-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 font-bold text-sm transition flex items-center gap-1">Kelola User</a>
                    <a href="admin_transaksi.php" class="text-gray-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 font-bold text-sm transition flex items-center gap-1">Pesanan Masuk</a>
                    <?php endif; ?>
                    
                    <button id="theme-toggle" class="w-9 h-9 rounded-xl bg-orange-50 dark:bg-zinc-800 text-orange-600 dark:text-orange-400 hover:scale-105 transition flex items-center justify-center cursor-pointer border border-orange-100/30 dark:border-zinc-700">
                        <i id="theme-toggle-dark-icon" class="fas fa-moon text-sm"></i>
                        <i id="theme-toggle-light-icon" class="fas fa-sun text-sm"></i>
                    </button>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 bg-gray-50 dark:bg-zinc-800 border border-gray-100 dark:border-zinc-700 px-3 py-1.5 rounded-full text-xs font-bold text-gray-600 dark:text-zinc-300">
                    <div class="w-5 h-5 bg-orange-200 dark:bg-orange-900/60 text-orange-700 dark:text-orange-400 rounded-full flex items-center justify-center text-[10px]">
                        <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                    </div>
                    <span><?= htmlspecialchars($_SESSION['username']) ?> (<span class="text-orange-600 dark:text-orange-400"><?= ucfirst($_SESSION['role']) ?></span>)</span>
                </div>
                
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="tambah.php" class="bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs md:text-sm px-4 py-2 rounded-xl transition flex items-center gap-1 shadow-sm">
                        <span>+ Tambah</span>
                    </a>
                <?php endif; ?>
                <a href="logout.php" class="bg-red-50 dark:bg-red-950/30 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 font-bold text-xs px-3 py-2 rounded-xl transition border border-red-200/30 dark:border-red-900/40">Keluar</a>
            </div>
        </div>
    </nav>
    
    <main class="max-w-7xl mx-auto px-4 py-8 space-y-12">
        <div class="bg-[#FDF6F0] dark:bg-zinc-900 rounded-[2.5rem] p-8 md:p-12 lg:p-16 flex flex-col lg:flex-row items-center justify-between gap-12 border border-orange-100/40 dark:border-zinc-800 relative overflow-hidden transition-colors duration-300">
            <div class="max-w-xl space-y-6 z-10 w-full lg:w-1/2">
                <div class="inline-flex items-center gap-1.5 bg-[#F4EBE1] dark:bg-zinc-800 text-orange-800 dark:text-orange-400 px-4 py-1.5 rounded-full text-xs font-bold tracking-wide uppercase border border-orange-200/20 dark:border-zinc-700">
                CRAFT PREMIUM HANDMADE
                </div>
                <h2 class="text-4xl md:text-5xl lg:text-6xl font-black text-[#2D2D2D] dark:text-zinc-100 leading-[1.1] font-serif-title">
                    Temukan & Dapatkan <br>
                    <span class="text-orange-600 dark:text-orange-500">Tas Rajut</span> Terbaik
                </h2>
                
                <form method="GET" action="index.php" class="flex flex-col sm:flex-row gap-2 max-w-lg bg-white dark:bg-zinc-950 p-2.5 rounded-2xl shadow-sm border border-orange-100/30 dark:border-zinc-800">
                    <input type="text" name="search" value="<?= htmlspecialchars($search_text) ?>" placeholder="Cari produk... misal: Sling, Tote" class="flex-1 px-4 py-2.5 text-sm focus:outline-none text-gray-700 dark:text-zinc-200 bg-transparent">
                    <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white px-7 py-3 rounded-xl text-sm font-bold transition shadow-xs cursor-pointer">Cari</button>
                </form>
            </div>

            <div class="w-full lg:w-1/2 flex justify-center lg:justify-end z-10">
                <div class="relative w-72 h-72 sm:w-80 sm:h-80 md:w-96 md:h-96">
                    <div class="absolute inset-0 bg-orange-200/40 dark:bg-orange-500/10 rounded-full blur-2xl transform translate-x-4 translate-y-4"></div>
                    <img src="foto/tas1.png" alt="Premium Handmade Crochet Bag" class="w-full h-full object-cover rounded-full shadow-md border-4 border-white dark:border-zinc-800 transform rotate-2 hover:rotate-0 transition duration-500">
                    <div class="absolute -bottom-4 -left-4 bg-white dark:bg-zinc-800 px-4 py-2.5 rounded-2xl shadow-sm border border-orange-100 dark:border-zinc-700 flex items-center gap-2">
                        <span class="text-xl"></span>
                        <div class="text-left">
                            <p class="text-[10px] font-bold text-gray-400 dark:text-zinc-500 uppercase tracking-wider">Kualitas</p>
                            <p class="text-xs font-black text-gray-800 dark:text-zinc-200">100% Rajutan Tangan</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <h3 class="text-2xl font-black text-[#2D2D2D] dark:text-zinc-100 font-serif-title">Koleksi Terbaru</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                
                <?php if (count($result_data) > 0): ?>
                    <?php foreach ($result_data as $row): ?>
                        <div id="item-card-<?= $row['id'] ?>" class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xs border border-gray-100 dark:border-zinc-800/80 hover:shadow-md transition duration-300 flex flex-col overflow-hidden relative group">
                            
                            <div class="w-full h-44 bg-[#FFF9F5] dark:bg-zinc-950 flex items-center justify-center overflow-hidden border-b border-gray-50 dark:border-zinc-800/50">
                                <?php 
                                // Kondisi penentuan link gambar: Jika link gambar berupa url luar (awalan http), gunakan langsung.
                                if (strpos($row['gambar'], 'http') === 0) {
                                    $src_gambar = $row['gambar']; 
                                } else {
                                    // Jika nama file lokal, arahkan ke folder lokal 'uploads/'
                                    $src_gambar = "uploads/" . $row['gambar']; 
                                }
                                ?>
                                <img src="<?= htmlspecialchars($src_gambar) ?>" alt="<?= htmlspecialchars($row['nama_barang']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                            </div>

                            <div class="p-4 flex-1 flex flex-col justify-between space-y-3">
                                <div>
                                    <h4 class="text-sm font-black text-gray-900 dark:text-zinc-100 group-hover:text-orange-600 dark:group-hover:text-orange-400 transition truncate"><?= htmlspecialchars($row['nama_barang']) ?></h4>
                                    <p class="text-[11px] text-gray-400 dark:text-zinc-500 font-medium mt-0.5">oleh <?= htmlspecialchars($row['pembuat']) ?></p>
                                </div>
                                <div class="flex items-center gap-2 text-[11px] font-bold text-gray-500 dark:text-zinc-400">
                                    <span class="text-orange-500">★ 4.7</span>
                                    <span>•</span>
                                    <span class="bg-orange-50 dark:bg-orange-950/40 text-orange-700 dark:text-orange-400 px-2 py-0.5 rounded text-[10px] label-stok">Stok: <?= htmlspecialchars($row['stok']) ?></span>
                                </div>
                                <div class="pt-2 border-t border-gray-50 dark:border-zinc-800/60 flex justify-between items-center">
                                    <div>
                                        <p class="text-[9px] text-gray-400 dark:text-zinc-500 font-bold uppercase tracking-wider">Harga Satuan</p>
                                        <p class="text-sm font-black text-orange-600 dark:text-orange-500">Rp <?= number_format($row['harga_satuan'], 0, ',', '.') ?></p>
                                    </div>
                                    
                                    <?php if ($_SESSION['role'] === 'admin'): ?>
                                        <?php else: ?>
                                        <?php if ($row['stok'] > 0): ?>
                                            <a href="formulir_order.php?id=<?= $row['id'] ?>" class="bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold px-3.5 py-2 rounded-xl transition text-center inline-block shadow-xs btn-action">Pesan</a>
                                        <?php else: ?>
                                            <button disabled class="bg-gray-100 dark:bg-zinc-800 text-gray-400 dark:text-zinc-600 text-xs font-bold px-3.5 py-2 rounded-xl cursor-not-allowed btn-action">Habis</button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full bg-white dark:bg-zinc-900 p-12 text-center rounded-2xl border dark:border-zinc-800 text-gray-400 dark:text-zinc-500 font-medium">Tidak menemukan koleksi tas rajut yang cocok.</div>  
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="bg-white dark:bg-zinc-900 border-t border-orange-100/60 dark:border-zinc-800/80 mt-20 pt-16 pb-8 transition-colors duration-300">
        <div class="max-w-4xl mx-auto px-4 grid grid-cols-1 md:grid-cols-2 gap-12">
            <div class="space-y-4">
                <h3 class="text-sm font-black text-gray-900 dark:text-zinc-100 uppercase tracking-wider border-b border-orange-100 dark:border-zinc-800 pb-2">Tentang Kami</h3>
                <p class="text-xs text-gray-500 dark:text-zinc-400 leading-relaxed">
                    TasRajut merupakan brand lokal indonesia yang bergerak di industri fashion sejak tahun 1963 dengan menghasilkan produk tas, sepatu, baju dan aksesories yang berkualitas.
                </p>
            </div>
            <div class="space-y-4">
                <h3 class="text-sm font-black text-gray-900 dark:text-zinc-100 uppercase tracking-wider border-b border-orange-100 dark:border-zinc-800 pb-2">Informasi Kantor</h3>
                <div class="text-xs text-gray-500 dark:text-zinc-400 space-y-3">
                    <p class="flex items-start gap-2 leading-relaxed">
                        <i class="fas fa-map-marker-alt text-orange-600 dark:text-orange-500 mt-0.5"></i> 
                        <span>Jl. Raya Telang, Telang, Kec. Kamal, Kab. Bangkalan, Jawa Timur 69162</span>
                    </p>
                </div>
            </div>
        </div>
        <div class="max-w-4xl mx-auto px-4 mt-12 pt-6 border-t border-gray-100 dark:border-zinc-800 text-center text-[11px] text-gray-400 font-medium">
            &copy; <?= date('Y') ?> TasRajut. All Rights Reserved.
        </div>
    </footer>

    <script>
    const themeToggleBtn = document.getElementById('theme-toggle');
    const darkIcon = document.getElementById('theme-toggle-dark-icon');
    const lightIcon = document.getElementById('theme-toggle-light-icon');

    function syncIcons() {
        if (document.documentElement.classList.contains('dark')) {
            darkIcon.classList.add('hidden');     
            lightIcon.classList.remove('hidden');  
        } else {
            lightIcon.classList.add('hidden');     
            darkIcon.classList.remove('hidden');   
        }
    }
    syncIcons();

    themeToggleBtn.addEventListener('click', function() {
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('color-theme', 'light');
        } else {
            document.documentElement.classList.add('dark');
            localStorage.setItem('color-theme', 'dark');
        }
        syncIcons(); 
    });
    </script>
</body>
</html>
<?php 

$conn->close(); 
?>
