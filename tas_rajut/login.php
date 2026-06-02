<?php
require_once 'config/koneksi.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Verifikasi hash password
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id']  = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role']     = $row['role'];

            header("Location: index.php");
            exit;
        } else {
            $error = "Password yang Anda masukkan salah!";
        }
    } else {
        $error = "Username tidak terdaftar!";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TasRajut</title>
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
            <h1 class="text-3xl md:text-4xl font-black text-orange-600 tracking-tight">
                Tas<span class="text-gray-900 font-serif font-medium text-2xl md:text-3xl ml-1">Rajut</span>
            </h1>
            <p class="text-sm text-gray-500 mt-2 max-w-sm">
                Silakan masuk ke akun Anda untuk menjelajahi katalog premium atau mengelola data produk.
            </p>
        </div>

        <?php if($error): ?> 
            <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3.5 rounded-xl mb-6 text-sm font-semibold flex items-center gap-2">
                <i class="fas fa-exclamation-circle text-base"></i>
                <span><?= $error ?></span>
            </div> 
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-xs font-black tracking-wider text-gray-400 uppercase mb-2">Username</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                        <i class="fas fa-user text-sm"></i>
                    </span>
                    <input type="text" name="username" required placeholder="Masukkan username Anda" 
                           class="w-full pl-11 pr-4 py-3.5 bg-gray-50/50 border border-gray-200 focus:border-orange-500 rounded-xl text-base focus:outline-none transition-colors duration-200 shadow-2xs">
                </div>
            </div>
            
            <div>
                <label class="block text-xs font-black tracking-wider text-gray-400 uppercase mb-2">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                        <i class="fas fa-lock text-sm"></i>
                    </span>
                    <input type="password" name="password" required placeholder="••••••••" 
                           class="w-full pl-11 pr-4 py-3.5 bg-gray-50/50 border border-gray-200 focus:border-orange-500 rounded-xl text-base focus:outline-none transition-colors duration-200 shadow-2xs">
                </div>
            </div>

            <button type="submit" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-4 rounded-xl transition duration-200 text-base cursor-pointer shadow-md hover:shadow-lg flex items-center justify-center gap-2 mt-2">
                <span>Masuk Sistem</span>
                <i class="fas fa-arrow-right text-sm"></i>
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-gray-100 text-center">
            <p class="text-sm text-gray-500">
                Belum punya akun? <a href="register.php" class="text-orange-600 font-bold hover:text-orange-700 hover:underline transition ml-1">Daftar Baru</a>
            </p>
        </div>
    </div>

</body>
</html>