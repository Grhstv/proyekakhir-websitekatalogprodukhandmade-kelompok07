<?php
require_once 'config/koneksi.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (!empty($username) && !empty($password)) {
        
        // 1. Cek apakah username sudah terdaftar di database
        $cek_stmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
        $cek_stmt->bind_param("s", $username);
        $cek_stmt->execute();
        $cek_stmt->store_result();

        if ($cek_stmt->num_rows > 0) {
            // Jika username duplikat, set pesan error dan batalkan proses insert
            $error = "Username sudah digunakan! Silakan pilih nama lain.";
            $cek_stmt->close();
        } else {
            $cek_stmt->close();

            // 2. Jika username aman, lakukan proses hashing password dan insert data
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $hashed_password, $role);

            if ($stmt->execute()) {
                $success = "Registrasi berhasil! Silakan <a href='login.php' class='underline font-black hover:text-emerald-800'>Login di sini</a>.";
            } else {
                $error = "Terjadi kesalahan saat menyimpan data. Coba lagi nanti.";
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - TasRajut</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="relative flex items-center justify-center min-h-screen p-4 overflow-x-hidden">

    <div class="absolute inset-0 z-0 w-full h-full overflow-hidden">
        <img src="foto/background_tasrajut.jpeg" 
             alt="Rajutan Background" 
             style="width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0;">
             
        <div style="position: absolute; inset: 0; background: linear-gradient(rgba(250, 246, 240, 0.85), rgba(217, 119, 6, 0.25));"></div>
    </div>

    <div class="relative z-10 bg-white/95 backdrop-blur-md p-10 md:p-12 rounded-[2.5rem] shadow-2xl border border-orange-100/60 max-w-lg w-full transition-all duration-300">
        
        <div class="flex flex-col items-center text-center mb-8">
            <div class="w-16 h-16 bg-orange-50 rounded-2xl flex items-center justify-center text-3xl shadow-xs mb-4 border border-orange-100">
                🧶
            </div>
            <h1 class="text-3xl md:text-4xl font-black text-orange-950 tracking-tight">
                Tas<span class="text-gray-900 font-serif font-medium text-2xl md:text-3xl ml-1">Rajut</span>
            </h1>
            <p class="text-sm text-gray-500 mt-2 max-w-sm">
                Bergabunglah untuk menjelajahi katalog rajutan tangan premium atau mulai mengelola tokomu.
            </p>
        </div>

        <?php if($error): ?> 
            <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3.5 rounded-xl mb-6 text-sm font-semibold flex items-center gap-2">
                <i class="fas fa-exclamation-circle text-base"></i>
                <span><?= $error ?></span>
            </div> 
        <?php endif; ?>

        <?php if($success): ?> 
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3.5 rounded-xl mb-6 text-sm font-semibold flex items-center gap-2">
                <i class="fas fa-check-circle text-base"></i>
                <span><?= $success ?></span>
            </div> 
        <?php endif; ?>

        <form method="POST" class="space-y-5" onsubmit="return validasiRegister()">
            <div>
                <label class="block text-xs font-black tracking-wider text-gray-400 uppercase mb-2">Username</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                        <i class="fas fa-user text-sm"></i>
                    </span>
                    <input type="text" id="username" name="username" required placeholder="Buat username baru" 
                           class="w-full pl-11 pr-4 py-3.5 bg-gray-50/50 border border-gray-200 focus:border-orange-500 rounded-xl text-base focus:outline-none transition-colors duration-200 shadow-2xs">
                </div>
            </div>
            
            <div>
                <label class="block text-xs font-black tracking-wider text-gray-400 uppercase mb-2">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                        <i class="fas fa-lock text-sm"></i>
                    </span>
                    <input type="password" id="password" name="password" required placeholder="Buat password aman" 
                           class="w-full pl-11 pr-4 py-3.5 bg-gray-50/50 border border-gray-200 focus:border-orange-500 rounded-xl text-base focus:outline-none transition-colors duration-200 shadow-2xs">
                </div>
            </div>

            <div>
                <label class="block text-xs font-black tracking-wider text-gray-400 uppercase mb-2">Pilih Hak Akses (Role)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                        <i class="fas fa-user-shield text-sm"></i>
                    </span>
                    <select name="role" class="w-full pl-11 pr-4 py-3.5 bg-gray-50/50 border border-gray-200 focus:border-orange-500 rounded-xl text-base focus:outline-none transition-colors duration-200 shadow-2xs cursor-pointer appearance-none">
                        <option value="user">User / Pembeli</option>
                        <option value="admin">Admin / Pengelola Barang</option>
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 pointer-events-none">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </span>
                </div>
            </div>

            <button type="submit" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-4 rounded-xl transition duration-200 text-base cursor-pointer shadow-md hover:shadow-lg flex items-center justify-center gap-2 mt-4">
                <span>Daftar Sekarang</span>
                <i class="fas fa-user-plus text-sm"></i>
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-gray-100 text-center">
            <p class="text-sm text-gray-500">
                Sudah punya akun? <a href="login.php" class="text-orange-600 font-bold hover:text-orange-700 hover:underline transition ml-1">Login di sini</a>
            </p>
        </div>
    </div>

    <script>
    function validasiRegister() {
        const user = document.getElementById('username').value.trim();
        const pass = document.getElementById('password').value.trim();
        if(user.length < 4) {
            alert('Username minimal harus berisi 4 karakter!');
            return false;
        }
        if(pass.length < 6) {
            alert('Password keamanan minimal harus 6 karakter!');
            return false;
        }
        return true;
    }
    </script>
</body>
</html>