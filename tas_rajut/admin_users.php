<?php
require_once 'config/koneksi.php';
require_once 'middleware/auth_check.php';

// Proteksi: Pastikan hanya admin yang bisa masuk
if ($_SESSION['role'] !== 'admin') { 
    die("Akses ditolak! Halaman ini khusus Administrator."); 
}

$sukses = false;
$error = "";

// Proses Hapus User jika ada request POST action 'delete'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id_user = intval($_POST['id_user']);
    
    // Proteksi tambahan: Mencegah admin menghapus dirinya sendiri secara tidak sengaja
    if ($id_user === intval($_SESSION['user_id'])) {
        $error = "Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif!";
    } else {
        $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'user'");
        $delete_stmt->bind_param("i", $id_user);
        
        if ($delete_stmt->execute()) {
            $sukses = true;
        } else {
            $error = "Gagal menghapus user. Hubungi tim teknis database.";
        }
        $delete_stmt->close();
    }
}

// Ambil semua data pengguna dengan role 'user' (bukan sesama admin)
$result = $conn->query("SELECT id, username, role FROM users WHERE role = 'user' ORDER BY id DESC");
$list_user = [];
while ($row = $result->fetch_assoc()) {
    $list_user[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - TasRajut Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        html.dark body { background-color: #09090b !important; color: #e4e4e7 !important; }
        html.dark .bg-white { background-color: #18181b !important; border-color: #27272a !important; }
        html.dark h2, html.dark th { color: #f4f4f5 !important; }
        html.dark td { color: #a1a1aa !important; }
        html.dark tr { border-color: #27272a !important; }
        html.dark tr:hover { background-color: #27272a/30 !important; }
    </style>
    <script>
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="bg-[#FAF6F0] dark:bg-zinc-950 text-gray-800 dark:text-zinc-200 min-h-screen p-4 md:p-8 transition-colors duration-300">

    <div class="max-w-5xl mx-auto bg-white dark:bg-zinc-900 p-6 md:p-8 rounded-[2.5rem] border border-orange-100/60 dark:border-zinc-800/80 shadow-xl mt-4">
        
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8 pb-4 border-b border-gray-100 dark:border-zinc-800">
            <div class="flex items-center gap-3">
                <div>
                    <h2 class="text-2xl font-black text-gray-900 dark:text-zinc-100 tracking-tight">Manajemen Pelanggan</h2>
                    <p class="text-xs text-gray-400 dark:text-zinc-500 mt-0.5">Kelola kontrol akses data akun pengguna aplikasi TasRajut.</p>
                </div>
            </div>
            <a href="index.php" class="bg-gray-100 dark:bg-zinc-800 hover:bg-gray-200 dark:hover:bg-zinc-700 text-gray-700 dark:text-zinc-300 font-bold text-xs px-4 py-2.5 rounded-xl transition flex items-center gap-1.5">
                <i class="fas fa-arrow-left"></i> Kembali ke Beranda
            </a>
        </div>

        <?php if ($sukses): ?>
            <div class="mb-5 p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200/40 dark:border-emerald-900/40 text-emerald-700 dark:text-emerald-400 rounded-2xl text-xs font-bold flex items-center gap-2">
                <i class="fas fa-check-circle text-base"></i>
                <span>Akun pengguna berhasil dihapus permanen dari sistem database.</span>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="mb-5 p-4 bg-red-50 dark:bg-red-950/30 border border-red-200/40 dark:border-red-900/40 text-red-600 dark:text-red-400 rounded-2xl text-xs font-bold flex items-center gap-2">
                <i class="fas fa-exclamation-circle text-base"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <div class="w-full overflow-x-auto rounded-2xl border border-gray-100 dark:border-zinc-800">
            <table class="w-full text-left border-collapse bg-white dark:bg-zinc-900 text-sm">
                <thead>
                    <th class="p-4 bg-gray-50/70 dark:bg-zinc-950/50 font-black text-xs text-gray-400 uppercase tracking-wider w-20 text-center">ID User</th>
                    <th class="p-4 bg-gray-50/70 dark:bg-zinc-950/50 font-black text-xs text-gray-400 uppercase tracking-wider">Nama / Username Pelanggan</th>
                    <th class="p-4 bg-gray-50/70 dark:bg-zinc-950/50 font-black text-xs text-gray-400 uppercase tracking-wider w-40">Hak Akses</th>
                    <th class="p-4 bg-gray-50/70 dark:bg-zinc-950/50 font-black text-xs text-gray-400 uppercase tracking-wider w-32 text-center">Aksi Kendali</th>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                    <?php if (count($list_user) > 0): ?>
                        <?php foreach ($list_user as $user): ?>
                            <tr class="hover:bg-gray-50/40 dark:hover:bg-zinc-800/20 transition-colors">
                                <td class="p-4 font-bold text-gray-400 text-center">#<?= $user['id'] ?></td>
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-orange-100 dark:bg-orange-950/40 text-orange-700 dark:text-orange-400 flex items-center justify-center font-black text-xs uppercase">
                                            <?= substr($user['username'], 0, 2) ?>
                                        </div>
                                        <span class="font-bold text-gray-900 dark:text-zinc-100"><?= htmlspecialchars($user['username']) ?></span>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-400 px-2.5 py-1 rounded-md text-xs font-bold tracking-wide uppercase border border-gray-200/20">
                                        <?= htmlspecialchars($user['role']) ?>
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun pelanggan \'<?= htmlspecialchars($user['username']) ?>\' secara permanen? Sifat tindakan ini tidak dapat dibatalkan.');" class="inline-block">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id_user" value="<?= $user['id'] ?>">
                                        <button type="submit" class="bg-red-50 dark:bg-red-950/30 hover:bg-red-600 hover:text-white dark:hover:bg-red-600 text-red-600 dark:text-red-400 p-2 rounded-xl transition cursor-pointer text-xs font-bold flex items-center gap-1 mx-auto border border-red-100 dark:border-red-900/30 hover:border-transparent">
                                        <span>Hapus Akun</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="p-12 text-center text-gray-400 dark:text-zinc-500 font-medium">
                                <i class="fas fa-users-slash text-2xl mb-2 block"></i> Belum ada akun user / pelanggan terdaftar di dalam sistem.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
    </div>

</body>
</html>
<?php $conn->close(); ?>