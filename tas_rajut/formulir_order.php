<?php
require_once 'config/koneksi.php';
require_once 'middleware/auth_check.php'; // Pastikan hanya user login yang bisa akses

// Cek apakah parameter ID barang dikirim lewat URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_barang = intval($_GET['id']);

// Ambil detail barang dari database
$stmt = $conn->prepare("SELECT * FROM produk WHERE id = ?");
$stmt->bind_param("i", $id_barang);
$stmt->execute();
$result = $stmt->get_result();
$barang = $result->fetch_assoc();

// Jika barang tidak ditemukan atau stok habis, kembalikan ke index
if (!$barang || $barang['stok'] <= 0) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Pemesanan - <?= htmlspecialchars($barang['nama_barang']) ?></title>
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
             alt="Tas Rajut Background" 
             style="width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0;">
             
        <div style="position: absolute; inset: 0; background: linear-gradient(rgba(250, 246, 240, 0.85), rgba(217, 119, 6, 0.25));"></div>
    </div>

    <div class="relative z-10 bg-white/95 backdrop-blur-md max-w-2xl w-full rounded-[2.5rem] shadow-2xl border border-orange-100/60 overflow-hidden grid grid-cols-1 md:grid-cols-2 transition-all duration-300">
        
        <div class="p-8 bg-orange-50/40 flex flex-col justify-between border-b md:border-b-0 md:border-r border-orange-100/70">
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider bg-orange-100 text-orange-800 px-3 py-1 rounded-full">Detail Pesanan</span>
                <div class="w-full h-44 mt-5 rounded-2xl overflow-hidden bg-white border border-gray-100 shadow-2xs">
                    <?php 
                    $src_gambar = (strpos($barang['gambar'], 'http') === 0) ? $barang['gambar'] : "uploads/" . $barang['gambar'];
                    ?>
                    <img src="<?= htmlspecialchars($src_gambar) ?>" class="w-full h-full object-cover">
                </div>
                <h3 class="text-xl font-black text-gray-900 mt-4 leading-tight"><?= htmlspecialchars($barang['nama_barang']) ?></h3>
                <p class="text-xs text-gray-400 mt-1 flex items-center gap-1">
                    <i class="fas fa-cut text-[10px]"></i> Pengrajin: <?= htmlspecialchars($barang['pembuat']) ?>
                </p>
            </div>
            
            <div class="mt-8 pt-5 border-t border-orange-100">
                <p class="text-xs text-gray-400 font-bold uppercase tracking-wider">Total Pembayaran</p>
                <p class="text-3xl font-black text-orange-600 mt-1">Rp <?= number_format($barang['harga_satuan'], 0, ',', '.') ?></p>
            </div>
        </div>

        <div class="p-8 space-y-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 tracking-tight">Informasi Pengiriman</h2>
                <p class="text-xs text-gray-400 mt-1">Silakan lengkapi data di bawah ini untuk memproses pesanan Anda.</p>
            </div>
            
            <form action="proses_order.php" method="POST" class="space-y-4">
                <input type="hidden" name="id_barang" value="<?= $barang['id'] ?>">

                <div>
                    <label class="block text-xs font-black tracking-wider text-gray-500 uppercase mb-1.5">Nama Lengkap Penerima</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                            <i class="fas fa-user text-xs"></i>
                        </span>
                        <input type="text" name="nama_penerima" required placeholder="Masukkan nama penerima" 
                               class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-orange-500 bg-gray-50/50 focus:bg-white transition-colors">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-black tracking-wider text-gray-500 uppercase mb-1.5">Nomor WhatsApp / HP</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                            <i class="fab fa-whatsapp text-sm font-bold"></i>
                        </span>
                        <input type="tel" name="no_hp" required placeholder="Contoh: 08123456789" 
                               class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-orange-500 bg-gray-50/50 focus:bg-white transition-colors">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-black tracking-wider text-gray-500 uppercase mb-1.5">Alamat Lengkap Rumah</label>
                    <textarea name="alamat" rows="3" required placeholder="Nama jalan, RT/RW, Kelurahan, Kecamatan, Kota/Kabupaten, Kode Pos" 
                              class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-orange-500 bg-gray-50/50 focus:bg-white transition-colors resize-none"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-black tracking-wider text-gray-500 uppercase mb-1.5">Jasa Ekspedisi</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                            <i class="fas fa-truck text-xs"></i>
                        </span>
                        <select name="ekspedisi" required class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-orange-500 bg-gray-50/50 focus:bg-white transition-colors cursor-pointer appearance-none">
                            <option value="">-- Pilih Ekspedisi --</option>
                            <option value="J&T Express">J&T Express</option>
                            <option value="JNE Reguler">JNE Reguler</option>
                            <option value="Sicepat">SiCepat Kargo</option>
                            <option value="POS Indonesia">POS Indonesia</option>
                        </select>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400 pointer-events-none">
                            <i class="fas fa-chevron-down text-[10px]"></i>
                        </span>
                    </div>
                </div>

                <div class="pt-3 flex gap-3">
                    <a href="index.php" class="w-1/3 text-center bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-bold py-3.5 rounded-xl transition flex items-center justify-center">Batal</a>
                    <button type="submit" class="w-2/3 bg-orange-600 hover:bg-orange-700 text-white text-sm font-bold py-3.5 rounded-xl transition shadow-md hover:shadow-lg cursor-pointer flex items-center justify-center gap-1.5">
                        <span>Konfirmasi Pesanan</span>
                        <i class="fas fa-paper-plane text-xs"></i>
                    </button>
                </div>
            </form>
        </div>

    </div>

</body>
</html>