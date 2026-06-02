<?php
require_once 'config/koneksi.php';
require_once 'middleware/auth_check.php';

// Pastikan hanya admin yang bisa mengakses halaman ini
if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

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
    <title>Manajemen Katalog - TasRajut</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-serif-title { font-family: 'Playfair Display', serif; }
        html.dark body { background-color: #09090b !important; color: #e4e4e7 !important; }
        html.dark nav { background-color: rgba(24, 24, 27, 0.8) !important; border-color: rgba(39, 39, 42, 0.5) !important; }
        html.dark .bg-white { background-color: #18181b !important; border-color: #27272a !important; }
        html.dark h2, html.dark h3, html.dark .text-gray-900 { color: #f4f4f5 !important; }
        html.dark p, html.dark td, html.dark th { color: #a1a1aa !important; }
        html.dark .text-orange-600 { color: #f97316 !important; }
        html.dark .bg-gray-50/50 { background-color: #09090b !important; border-color: #27272a !important; }
        html.dark #theme-toggle { background-color: #27272a !important; color: #f97316 !important; }
    </style>
    <script>
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
                    <a href="index.php" class="text-gray-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 transition">Beranda</a>
                    <a href="admin_katalog.php" class="text-orange-600 bg-orange-50 dark:bg-orange-950/40 dark:text-orange-400 px-3 py-1.5 rounded-lg">Kelola Katalog</a>
                    
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
                
                <a href="tambah.php" class="bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs md:text-sm px-4 py-2 rounded-xl transition flex items-center gap-1 shadow-sm">
                    <span>+ Tambah</span>
                </a>
                <a href="logout.php" class="bg-red-50 dark:bg-red-950/30 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 font-bold text-xs px-3 py-2 rounded-xl transition border border-red-200/30 dark:border-red-900/40">Keluar</a>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-8">
        <div class="bg-white dark:bg-zinc-900 rounded-3xl p-6 shadow-xs border border-orange-100/40 dark:border-zinc-800 space-y-4 transition-colors duration-300">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-gray-100 dark:border-zinc-800 pb-4">
                <div>
                    <h3 class="text-xl font-black text-[#2D2D2D] dark:text-zinc-100 font-serif-title">Manajemen Katalog Produk</h3>
                    <p class="text-xs text-gray-400 dark:text-zinc-500 font-medium">Mode Admin: Kelola data, ubah informasi, atau hapus produk dari database sistem.</p>
                </div>
                <form method="GET" action="admin_katalog.php" class="flex gap-2 bg-gray-50 dark:bg-zinc-950 p-1.5 rounded-xl border border-orange-100/30 dark:border-zinc-800 w-full sm:w-auto">
                    <input type="text" name="search" value="<?= htmlspecialchars($search_text) ?>" placeholder="Cari di katalog..." class="px-3 py-1.5 text-xs focus:outline-none text-gray-700 dark:text-zinc-200 bg-transparent">
                    <button type="submit" class="bg-orange-600 text-white px-4 py-1.5 rounded-lg text-xs font-bold transition">Cari</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-gray-500 dark:text-zinc-400 whitespace-nowrap min-w-[800px]">
                    <thead>
                        <tr class="text-gray-400 dark:text-zinc-500 font-bold border-b border-gray-100 dark:border-zinc-800 bg-gray-50/50 dark:bg-zinc-950/50">
                            <th class="p-3 w-16">Foto</th>
                            <th class="p-3">Nama Produk</th>
                            <th class="p-3">Kategori</th>
                            <th class="p-3">Stok</th>
                            <th class="p-3">Harga</th>
                            <th class="p-3 text-center">Aksi Management</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-zinc-800/60 font-medium">
                        <?php if (count($result_data) > 0): ?>
                            <?php foreach($result_data as $row): ?>
                            <tr class="hover:bg-orange-50/10 dark:hover:bg-zinc-800/30 transition">
                                <td class="p-3">
                                    <img src="<?= !empty($row['gambar']) && file_exists('uploads/'.$row['gambar']) ? 'uploads/'.htmlspecialchars($row['gambar']) : 'https://placehold.co/100x100/fdf6f0/d97706?text=Tas' ?>" class="w-10 h-10 object-cover rounded-lg border border-gray-100 dark:border-zinc-700">
                                </td>
                                <td class="p-3 font-bold text-gray-900 dark:text-zinc-100"><?= htmlspecialchars($row['nama_barang']) ?></td>
                                <td class="p-3"><span class="bg-orange-50 dark:bg-orange-950/40 text-orange-700 dark:text-orange-400 font-semibold px-2 py-0.5 rounded text-[10px]"><?= htmlspecialchars($row['kategori']) ?></span></td>
                                <td class="p-3 font-bold text-gray-700 dark:text-zinc-300"><?= htmlspecialchars($row['stok']) ?> pcs</td>
                                <td class="p-3 font-bold text-orange-600 dark:text-orange-500">Rp <?= number_format($row['harga_satuan'], 0, ',', '.') ?></td>
                                <td class="p-3">
                                    <div class="flex justify-center gap-2">
                                        <a href="edit.php?id=<?= $row['id'] ?>" class="bg-amber-50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-400 hover:bg-amber-600 hover:text-white px-3 py-1.5 rounded-lg font-bold text-xs transition border border-amber-200/30 dark:border-amber-900/30 flex items-center gap-1">
                                            <span>Ubah</span>
                                        </a>
                                        <a href="hapus.php?id=<?= $row['id'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus produk <?= htmlspecialchars($row['nama_barang']) ?>?')" class="bg-red-50 dark:bg-red-950/20 text-red-600 dark:text-red-400 hover:bg-red-600 hover:text-white px-3 py-1.5 rounded-lg font-bold text-xs transition border border-red-200/30 dark:border-red-900/30 flex items-center gap-1">
                                            <span>Hapus</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="p-12 text-center text-gray-400 dark:text-zinc-500 font-medium">🔍 Belum ada koleksi rajutan terdaftar di database.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        // Dark Mode Toggle Logic
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
<?php $conn->close(); ?>