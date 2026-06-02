<?php
require_once 'config/koneksi.php';
require_once 'middleware/auth_check.php';

// Proteksi khusus Admin
if ($_SESSION['role'] !== 'admin') {
    die("Akses ditolak! Halaman ini khusus Administrator.");
}

$sukses = false;

// Proses Update Status Transaksi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_status'])) {
    // Sesuai SQL: Menggunakan id_transaksi, bukan id
    $id_trx = intval($_POST['id_transaksi']);
    $status_baru = $_POST['action_status']; 

    $update_stmt = $conn->prepare("UPDATE transaksi SET status = ? WHERE id_transaksi = ?");
    $update_stmt->bind_param("si", $status_baru, $id_trx);
    if ($update_stmt->execute()) {
        $sukses = true;
    }
    $update_stmt->close();
}

// SQL Diperbarui: Menghapus JOIN users karena kolomnya belum ada di tabel transaksi
$query = "SELECT t.*, p.nama_barang, p.gambar FROM transaksi t 
          INNER JOIN produk p ON t.id_produk = p.id
          ORDER BY t.waktu_pesan DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Transaksi Masuk - Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="bg-[#FAF6F0] dark:bg-zinc-950 text-gray-800 dark:text-zinc-200 min-h-screen p-4 md:p-8">

    <div class="max-w-6xl mx-auto bg-white dark:bg-zinc-900 p-6 md:p-8 rounded-[2.5rem] border border-orange-100/60 dark:border-zinc-800/80 shadow-xl">
        
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8 pb-4 border-b border-gray-100 dark:border-zinc-800">
            <div class="flex items-center gap-3">
                <div>
                    <h2 class="text-2xl font-black text-gray-900 dark:text-zinc-100 tracking-tight">Konfirmasi Pesanan Masuk</h2>
                    <p class="text-xs text-gray-400 dark:text-zinc-500 mt-0.5">Setujui pembayaran dan proses pengiriman kerajinan rajut pelanggan.</p>
                </div>
            </div>
            <a href="index.php" class="bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-zinc-300 font-bold text-xs px-4 py-2.5 rounded-xl flex items-center gap-1.5"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>

        <?php if ($sukses): ?>
            <div class="mb-5 p-4 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 rounded-2xl text-xs font-bold flex items-center gap-2">
                <i class="fas fa-check-circle text-base"></i> Status pesanan berhasil diperbarui & disinkronkan ke pelanggan.
            </div>
        <?php endif; ?>

        <div class="w-full overflow-x-auto rounded-2xl border border-gray-100 dark:border-zinc-800">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-50/70 dark:bg-zinc-950/50 text-gray-400 font-black text-xs uppercase">
                        <th class="p-4">Nota / Waktu</th>
                        <th class="p-4">Produk</th>
                        <th class="p-4">Tujuan Pengiriman</th>
                        <th class="p-4">Status Sekarang</th>
                        <th class="p-4 text-center">Aksi Tindakan Admin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50/30 dark:hover:bg-zinc-800/20">
                                <td class="p-4">
                                    <span class="block font-bold text-orange-600 dark:text-orange-400 text-xs"><?= $row['nota_trx'] ?></span>
                                    <span class="text-[10px] text-gray-400 block mt-1"><?= date('d/m/Y H:i', strtotime($row['waktu_pesan'])) ?></span>
                                </td>
                                <td class="p-4 font-medium"><?= htmlspecialchars($row['nama_barang']) ?></td>
                                <td class="p-4 text-xs">
                                    <p class="font-bold"><?= htmlspecialchars($row['nama_penerima']) ?> (<?= htmlspecialchars($row['no_hp']) ?>)</p>
                                    <p class="text-gray-400 mt-0.5 truncate max-w-xs"><?= htmlspecialchars($row['alamat']) ?></p>
                                    <span class="text-[10px] bg-gray-100 dark:bg-zinc-800 px-1.5 py-0.5 rounded text-gray-500 font-bold mt-1 inline-block"><?= htmlspecialchars($row['ekspedisi']) ?></span>
                                </td>
                                <td class="p-4">
                                    <?php 
                                    // Antisipasi jika kolom status belum dibuat di DB, default ke Menunggu
                                    $status_sekarang = isset($row['status']) ? $row['status'] : 'Menunggu';
                                    if ($status_sekarang === 'Diproses'): ?>
                                        <span class="text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/20 px-2 py-1 rounded-full text-[11px] font-bold">Diproses</span>
                                    <?php elseif ($status_sekarang === 'Selesai'): ?>
                                        <span class="text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/20 px-2 py-1 rounded-full text-[11px] font-bold">Selesai</span>
                                    <?php else: ?>
                                        <span class="text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/20 px-2 py-1 rounded-full text-[11px] font-bold">Menunggu</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <?php if ($status_sekarang === 'Menunggu' || !isset($row['status'])): ?>
                                            <form method="POST" class="inline">
                                                <input type="hidden" name="id_transaksi" value="<?= $row['id_transaksi'] ?>">
                                                <input type="hidden" name="action_status" value="Diproses">
                                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-3 py-1.5 rounded-lg cursor-pointer transition">Proses</button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ($status_sekarang !== 'Selesai'): ?>
                                            <form method="POST" class="inline">
                                                <input type="hidden" name="id_transaksi" value="<?= $row['id_transaksi'] ?>">
                                                <input type="hidden" name="action_status" value="Selesai">
                                                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-3 py-1.5 rounded-lg cursor-pointer transition">Selesai</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-xs text-gray-400 italic">No Action</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="p-12 text-center text-gray-400"><i class="fas fa-receipt text-xl block mb-2"></i> Belum ada pesanan masuk dari pembeli.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
<?php $conn->close(); ?>