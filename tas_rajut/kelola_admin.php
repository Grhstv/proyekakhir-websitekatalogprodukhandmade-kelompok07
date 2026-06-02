<?php
require_once 'config/koneksi.php';
require_once 'middleware/auth_check.php';

// Proteksi halaman, pastikan hanya peran admin yang bisa mengakses
if ($_SESSION['role'] !== 'admin') { 
    die("Akses ditolak! Halaman ini hanya untuk administrator."); 
}

$admin_id = $_SESSION['user_id']; // Mengambil ID admin yang sedang login dari session
$sukses = false;
$error = "";

// Ambil info data admin terbaru dari database saat ini
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_baru = trim($_POST['username']);
    $password_baru = $_POST['password'];

    if (empty($username_baru)) {
        $error = "Username tidak boleh kosong!";
    } else {
        if (!empty($password_baru)) {
            // Jika admin mengisi field password, maka ganti username DAN password baru (di-hash)
            $password_hashed = password_hash($password_baru, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
            $update_stmt->bind_param("ssi", $username_baru, $password_hashed, $admin_id);
        } else {
            // Jika field password dikosongkan, hanya perbarui data username saja
            $update_stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
            $update_stmt->bind_param("si", $username_baru, $admin_id);
        }

        if ($update_stmt->execute()) {
            $sukses = true;
            $_SESSION['username'] = $username_baru; // Sinkronisasi session baru agar nama di navbar ikut berubah
            $admin_data['username'] = $username_baru; // Update visual local value
        } else {
            $error = "Gagal memperbarui data akun. Kemungkinan username sudah digunakan.";
        }
        $update_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Akun Admin - TasRajut</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
    <script>
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="relative flex items-center justify-center min-h-screen p-4 overflow-x-hidden md:p-8 dark:bg-zinc-950">

    <div class="absolute inset-0 z-0 w-full h-full overflow-hidden">
        <img src="foto/background_tasrajut.jpeg" alt="Tas Rajut Background" style="width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0;">
        <div class="dark:hidden" style="position: absolute; inset: 0; background: linear-gradient(rgba(250, 246, 240, 0.85), rgba(217, 119, 6, 0.25));"></div>
        <div class="hidden dark:block" style="position: absolute; inset: 0; background: linear-gradient(rgba(24, 24, 27, 0.9), rgba(9, 9, 11, 0.95));"></div>
    </div>

    <div class="relative z-10 bg-white/95 dark:bg-zinc-900/95 backdrop-blur-md p-8 md:p-10 rounded-[2.5rem] shadow-2xl border border-orange-100/60 dark:border-zinc-800/80 max-w-md w-full transition-all duration-300">
        
        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100 dark:border-zinc-800">
            <div class="w-12 h-12 bg-orange-50 dark:bg-zinc-800 rounded-xl flex items-center justify-center text-2xl border border-orange-100 dark:border-zinc-700">
                👤
            </div>
            <div>
                <h2 class="text-xl font-black text-gray-900 dark:text-zinc-100 tracking-tight">Pengaturan Admin</h2>
                <p class="text-xs text-gray-400 dark:text-zinc-500 mt-0.5">Perbarui nama pengguna keamanan kontrol sistem.</p>
            </div>
        </div>

        <?php if ($sukses): ?>
            <div class="mb-4 p-3.5 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200/40 dark:border-emerald-900/40 text-emerald-700 dark:text-emerald-400 rounded-xl text-xs font-bold flex items-center gap-2 animate-fade-in">
                <i class="fas fa-circle-check text-sm"></i>
                <span>Data akun kredensial admin berhasil diperbarui!</span>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="mb-4 p-3.5 bg-red-50 dark:bg-red-950/30 border border-red-200/40 dark:border-red-900/40 text-red-600 dark:text-red-400 rounded-xl text-xs font-bold flex items-center gap-2">
                <i class="fas fa-circle-exclamation text-sm"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <div>
                <label class="block text-xs font-black tracking-wider text-gray-400 dark:text-zinc-500 uppercase mb-2">Username Utama</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                        <i class="fas fa-user-shield text-sm"></i>
                    </span>
                    <input type="text" name="username" required value="<?= htmlspecialchars($admin_data['username']) ?>" placeholder="Masukkan username baru" 
                           class="w-full pl-11 pr-4 py-3 bg-gray-50/50 dark:bg-zinc-950/50 border border-gray-200 dark:border-zinc-800 focus:border-orange-500 rounded-xl text-sm focus:outline-none dark:text-zinc-200 transition-colors shadow-2xs">
                </div>
            </div>

            <div>
                <label class="block text-xs font-black tracking-wider text-gray-400 dark:text-zinc-500 uppercase mb-2">Password Baru</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                        <i class="fas fa-lock text-sm"></i>
                    </span>
                    <input type="password" name="password" placeholder="Isi hanya jika ingin mengganti password" 
                           class="w-full pl-11 pr-4 py-3 bg-gray-50/50 dark:bg-zinc-950/50 border border-gray-200 dark:border-zinc-800 focus:border-orange-500 rounded-xl text-sm focus:outline-none dark:text-zinc-200 transition-colors shadow-2xs">
                </div>
                <p class="text-[10px] text-gray-400 dark:text-zinc-500 mt-1.5">*Kosongkan kolom sandi jika Anda hanya berniat mengubah susunan nama *username* saja.</p>
            </div>

            <div class="pt-2 flex gap-3">
                <a href="index.php" class="w-1/3 text-center bg-gray-100 dark:bg-zinc-800 hover:bg-gray-200 dark:hover:bg-zinc-700 text-gray-600 dark:text-zinc-300 font-bold py-3.5 rounded-xl transition text-xs flex items-center justify-center">
                    Kembali
                </a>
                <button type="submit" class="w-2/3 bg-orange-600 hover:bg-orange-700 text-white font-bold py-3.5 rounded-xl transition text-xs cursor-pointer shadow-md flex items-center justify-center gap-2">
                    <span>Simpan Akun</span>
                    <i class="fas fa-user-check text-xs"></i>
                </button>
            </div>
        </form>
    </div>

</body>
</html>
<?php $conn->close(); ?>