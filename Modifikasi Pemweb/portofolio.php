<?php
require_once 'auth_check.php';
// Pastikan user sudah login. (Admin akan kita arahkan kembali ke dashboard admin jika nyasar ke sini)
wajib_login(); 
if ($_SESSION['role'] === 'admin') {
    header("Location: dashboard-admin.php");
    exit;
}

require_once 'koneksi.php';

$role = $_SESSION['role'];

// Tangkap input pencarian dan filter
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$kategori = isset($_GET['kategori']) ? mysqli_real_escape_string($conn, $_GET['kategori']) : '';

// ========================================================
// LOGIKA QUERY BERDASARKAN ROLE (POIN 8) & FILTER (POIN 7)
// ========================================================
if ($role === 'desa') {
    // DESA: Melihat PENAWARAN BANTUAN (dari Donatur) yang sudah 'approved' dan belum didanai (is_funded = 0)
    $sql = "SELECT * FROM penawaran_bantuan WHERE status = 'approved' AND is_funded = 0";
    
    // Fitur Search & Filter
    if ($search != '') {
        $sql .= " AND (nama_instansi LIKE '%$search%' OR detail_bantuan LIKE '%$search%')";
    }
    if ($kategori != '') {
        $sql .= " AND jenis_penawaran = '$kategori'";
    }
    
    $sql .= " ORDER BY created_at DESC";
    $query_data = mysqli_query($conn, $sql);

} elseif ($role === 'donatur') {
    // DONATUR: Melihat PERMINTAAN BANTUAN (dari Desa) yang sudah 'approved' dan belum didanai (is_funded = 0)
    $sql = "SELECT * FROM permintaan_bantuan WHERE status = 'approved' AND is_funded = 0";
    
    // Fitur Search & Filter
    if ($search != '') {
        $sql .= " AND (desa LIKE '%$search%' OR alasan LIKE '%$search%')";
    }
    if ($kategori != '') {
        $sql .= " AND target_bantuan = '$kategori'";
    }
    
    $sql .= " ORDER BY created_at DESC";
    $query_data = mysqli_query($conn, $sql);
}
?>

<?php include 'components/header.php'; ?>

<main class="pt-36 pb-20 min-h-screen bg-slate-50">
  <div class="container mx-auto px-4">
    
    <div class="text-center mb-10">
      <h1 class="text-4xl font-extrabold text-slate-900 mb-4">Program Bantuan Aktif</h1>
      <?php if ($role === 'desa'): ?>
        <p class="text-slate-500 max-w-2xl mx-auto">Temukan berbagai penawaran bantuan dari instansi/donatur yang siap disalurkan ke desa Anda. Silakan ajukan diri pada program yang sesuai.</p>
      <?php elseif ($role === 'donatur'): ?>
        <p class="text-slate-500 max-w-2xl mx-auto">Daftar desa yang membutuhkan uluran tangan Anda. Pilih program yang ingin Anda danai untuk mulai membawa perubahan.</p>
      <?php endif; ?>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-md border border-slate-200 mb-10 max-w-4xl mx-auto">
        <form action="" method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="flex-grow">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari nama <?= ($role === 'desa') ? 'instansi' : 'desa' ?> atau kata kunci..." class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none">
            </div>
            
            <div class="w-full md:w-64">
                <?php if ($role === 'desa'): ?>
                    <select name="kategori" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none">
                        <option value="">Semua Jenis Bantuan</option>
                        <option value="Sembako" <?= ($kategori == 'Sembako') ? 'selected' : '' ?>>Sembako</option>
                        <option value="Dana Tunai" <?= ($kategori == 'Dana Tunai') ? 'selected' : '' ?>>Dana Tunai</option>
                        <option value="Material" <?= ($kategori == 'Material') ? 'selected' : '' ?>>Material Bangunan</option>
                    </select>
                <?php elseif ($role === 'donatur'): ?>
                    <select name="kategori" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none">
                        <option value="">Semua Target</option>
                        <option value="warga" <?= ($kategori == 'warga') ? 'selected' : '' ?>>Warga Terdampak</option>
                        <option value="fasilitas" <?= ($kategori == 'fasilitas') ? 'selected' : '' ?>>Fasilitas Umum</option>
                    </select>
                <?php endif; ?>
            </div>

            <button type="submit" class="bg-teal-500 hover:bg-teal-600 text-white font-bold py-3 px-8 rounded-lg transition shadow-md">
                Cari
            </button>
            <?php if($search != '' || $kategori != ''): ?>
                <a href="portofolio.php" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-3 px-4 rounded-lg transition text-center">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        
        <?php if(mysqli_num_rows($query_data) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($query_data)): ?>
                
                <?php if ($role === 'desa'): ?>
                    <div class="bg-white rounded-2xl shadow-lg border border-slate-100 overflow-hidden hover:shadow-xl transition-shadow flex flex-col h-full">
                        <div class="bg-teal-500 p-4 relative">
                            <span class="bg-white text-teal-600 text-xs font-bold px-3 py-1 rounded-full absolute top-4 right-4 shadow">
                                <?= $row['jenis_penawaran'] ?>
                            </span>
                            <h3 class="text-xl font-bold text-white mt-6"><?= $row['nama_instansi'] ?></h3>
                            <p class="text-teal-100 text-sm">PJ: <?= $row['pj_donatur'] ?></p>
                        </div>
                        <div class="p-6 flex-grow flex flex-col">
                            <h4 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-2">Detail Bantuan</h4>
                            <p class="text-slate-700 text-sm mb-4 line-clamp-3"><?= $row['detail_bantuan'] ?></p>
                            <div class="mt-auto pt-4 border-t border-slate-100">
                                <p class="text-xs text-slate-400 mb-3">Tgl Penawaran: <?= date('d M Y', strtotime($row['created_at'])) ?></p>
                                <button onclick="alert('Fitur Apply akan segera dibuat di tahap selanjutnya!')" class="w-full bg-slate-900 hover:bg-teal-500 text-white font-bold py-2.5 rounded-lg transition">
                                    Ajukan Desa Saya
                                </button>
                            </div>
                        </div>
                    </div>

                <?php elseif ($role === 'donatur'): ?>
                    <div class="bg-white rounded-2xl shadow-lg border border-slate-100 overflow-hidden hover:shadow-xl transition-shadow flex flex-col h-full">
                        <div class="bg-amber-500 p-4 relative">
                            <span class="bg-white text-amber-600 text-xs font-bold px-3 py-1 rounded-full absolute top-4 right-4 shadow capitalize">
                                Target: <?= $row['target_bantuan'] ?>
                            </span>
                            <h3 class="text-xl font-bold text-white mt-6">Desa <?= $row['desa'] ?></h3>
                            <p class="text-amber-100 text-sm"><?= $row['kecamatan'] ?>, <?= $row['kota'] ?></p>
                        </div>
                        <div class="p-6 flex-grow flex flex-col">
                            <h4 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-2">Kondisi / Alasan</h4>
                            <p class="text-slate-700 text-sm mb-4 line-clamp-3"><?= $row['alasan'] ?></p>
                            
                            <?php if($row['jumlah_kk']): ?>
                                <div class="bg-slate-50 p-2 rounded border border-slate-100 mb-4 flex items-center justify-center">
                                    <span class="text-sm font-bold text-slate-600">Dibutuhkan untuk <?= $row['jumlah_kk'] ?> KK</span>
                                </div>
                            <?php endif; ?>

                            <div class="mt-auto pt-4 border-t border-slate-100">
                                <p class="text-xs text-slate-400 mb-3">Tgl Pengajuan: <?= date('d M Y', strtotime($row['created_at'])) ?></p>
                                <button onclick="alert('Fitur Apply akan segera dibuat di tahap selanjutnya!')" class="w-full bg-teal-500 hover:bg-teal-600 text-white font-bold py-2.5 rounded-lg transition">
                                    Danai Program Ini
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-span-full text-center py-20">
                <div class="inline-block p-6 bg-slate-100 rounded-full mb-4">
                    <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-slate-800">Tidak Ada Program Aktif</h3>
                <p class="text-slate-500 mt-2">Coba sesuaikan kata kunci pencarian, atau tunggu program baru diverifikasi oleh Admin.</p>
            </div>
        <?php endif; ?>

    </div>
  </div>
</main>

<?php include 'components/footer.php'; ?>