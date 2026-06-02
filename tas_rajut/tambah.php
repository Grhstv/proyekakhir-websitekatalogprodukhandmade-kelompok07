<?php
require_once 'config/koneksi.php';
require_once 'middleware/auth_check.php';

if ($_SESSION['role'] !== 'admin') { die("Akses ditolak!"); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama_barang']);
    $kategori = $_POST['kategori'];
    $stok     = intval($_POST['stok']);
    $harga    = intval($_POST['harga_satuan']);
    $pembuat  = trim($_POST['pembuat']);
    $nama_gambar = "";

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $nama_gambar = time() . '_' . uniqid() . '.' . $ext;
        if (!is_dir('uploads')) mkdir('uploads', 0755, true);
        move_uploaded_file($_FILES['gambar']['tmp_name'], 'uploads/' . $nama_gambar);
    }

    $stmt = $conn->prepare("INSERT INTO produk (nama_barang, kategori, stok, harga_satuan, pembuat, gambar) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiiss", $nama, $kategori, $stok, $harga, $pembuat, $nama_gambar);
    
    if ($stmt->execute()) {
        header("Location: index.php");
        exit;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk Baru - TasRajut</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="relative flex items-center justify-center min-h-screen p-4 overflow-x-hidden md:p-8">

    <div class="absolute inset-0 z-0 w-full h-full overflow-hidden">
        <img src="foto/background_tasrajut.jpeg" 
             alt="Tas Rajut Background" 
             style="width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0;">
             
        <div style="position: absolute; inset: 0; background: linear-gradient(rgba(250, 246, 240, 0.85), rgba(217, 119, 6, 0.25));"></div>
    </div>

    <div class="relative z-10 bg-white/95 backdrop-blur-md p-8 md:p-10 rounded-[2.5rem] shadow-2xl border border-orange-100/60 max-w-2xl w-full transition-all duration-300 my-6">
        
        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100">
            <div>
                <h2 class="text-2xl font-black text-gray-900 tracking-tight">Koleksi Manajemen</h2>
                <p class="text-xs text-gray-400 mt-0.5">Tambahkan produk tas rajut tangan baru ke dalam katalog sistem.</p>
            </div>
        </div>

        <form method="POST" enctype="multipart/form-data" class="space-y-5">
            
            <div>
                <label class="block text-xs font-black tracking-wider text-gray-400 uppercase mb-2">Nama Tas</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                        <i class="fas fa-bag-shopping text-sm"></i>
                    </span>
                    <input type="text" name="nama_barang" required placeholder="Contoh: Hana Sling Bag Vintage" 
                           class="w-full pl-11 pr-4 py-3 bg-gray-50/50 border border-gray-200 focus:border-orange-500 rounded-xl text-base focus:outline-none transition-colors duration-200 shadow-2xs">
                </div>
            </div>

            <div>
                <label class="block text-xs font-black tracking-wider text-gray-400 uppercase mb-2">Kategori</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                        <i class="fas fa-tags text-sm"></i>
                    </span>
                    <select name="kategori" class="w-full pl-11 pr-4 py-3 bg-gray-50/50 border border-gray-200 focus:border-orange-500 rounded-xl text-base focus:outline-none transition-colors duration-200 shadow-2xs cursor-pointer appearance-none">
                        <option value="Sling Bag">Sling Bag</option>
                        <option value="Tote Bag">Tote Bag</option>
                        <option value="Pouch Mini">Pouch Mini</option>
                        <option value="Backpack">Backpack</option>
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 pointer-events-none">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-black tracking-wider text-gray-400 uppercase mb-2">Stok Awal</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                            <i class="fas fa-cubes text-sm"></i>
                        </span>
                        <input type="number" name="stok" required min="0" placeholder="0" 
                               class="w-full pl-11 pr-4 py-3 bg-gray-50/50 border border-gray-200 focus:border-orange-500 rounded-xl text-base focus:outline-none transition-colors duration-200 shadow-2xs">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-black tracking-wider text-gray-400 uppercase mb-2">Harga Satuan (Rp)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                            <i class="fas fa-money-bill-wave text-sm"></i>
                        </span>
                        <input type="number" name="harga_satuan" required min="0" placeholder="Contoh: 150000" 
                               class="w-full pl-11 pr-4 py-3 bg-gray-50/50 border border-gray-200 focus:border-orange-500 rounded-xl text-base focus:outline-none transition-colors duration-200 shadow-2xs">
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-black tracking-wider text-gray-400 uppercase mb-2">Nama Pengrajin / Pembuat</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                        <i class="fas fa-user-gear text-sm"></i>
                    </span>
                    <input type="text" name="pembuat" required placeholder="Contoh: UMKM Rajut Lestari" 
                           class="w-full pl-11 pr-4 py-3 bg-gray-50/50 border border-gray-200 focus:border-orange-500 rounded-xl text-base focus:outline-none transition-colors duration-200 shadow-2xs">
                </div>
            </div>

            <div>
                <label class="block text-xs font-black tracking-wider text-gray-400 uppercase mb-2">Foto Dokumentasi Barang</label>
                <div class="w-full p-4 border-2 border-dashed border-gray-200 rounded-xl bg-gray-50/50 hover:bg-gray-50 transition-colors">
                    <input type="file" name="gambar" accept="image/*" required 
                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 file:cursor-pointer cursor-pointer">
                </div>
            </div>

            <div class="pt-4 flex flex-col-reverse sm:flex-row gap-3">
                <a href="index.php" class="w-full sm:w-1/3 text-center bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-3.5 rounded-xl transition text-base flex items-center justify-center">
                    Batal
                </a>
                <button type="submit" class="w-full sm:w-2/3 bg-orange-600 hover:bg-orange-700 text-white font-bold py-3.5 rounded-xl transition duration-200 text-base cursor-pointer shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                    <span>Simpan Produk</span>
                    <i class="fas fa-cloud-arrow-up text-sm"></i>
                </button>
            </div>
        </form>
    </div>

</body>
</html>