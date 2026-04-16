<?php
require_once 'auth_check.php';
require_once 'koneksi.php';

// Tentukan role: Jika belum login, kita anggap sebagai 'guest' (Tamu)
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';

// Admin tetap diarahkan ke dashboard
if ($role === 'admin') {
    header("Location: dashboard-admin.php");
    exit;
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$kategori = isset($_GET['kategori']) ? mysqli_real_escape_string($conn, $_GET['kategori']) : '';

$query_penawaran = null;
$query_permintaan = null;

// ========================================================
// 1. QUERY PENAWARAN (Dilihat oleh DESA dan GUEST)
// ========================================================
if ($role === 'desa' || $role === 'guest') {
    $sql1 = "SELECT * FROM penawaran_bantuan WHERE status = 'approved' AND is_funded = 0";
    if ($search != '') { $sql1 .= " AND (nama_instansi LIKE '%$search%' OR detail_bantuan LIKE '%$search%')"; }
    if ($role === 'desa' && $kategori != '') { $sql1 .= " AND jenis_penawaran = '$kategori'"; }
    $sql1 .= " ORDER BY created_at DESC";
    $query_penawaran = mysqli_query($conn, $sql1);
}

// ========================================================
// 2. QUERY PERMINTAAN (Dilihat oleh DONATUR dan GUEST)
// ========================================================
if ($role === 'donatur' || $role === 'guest') {
    $sql2 = "SELECT * FROM permintaan_bantuan WHERE status = 'approved' AND is_funded = 0";
    if ($search != '') { $sql2 .= " AND (desa LIKE '%$search%' OR alasan LIKE '%$search%')"; }
    if ($role === 'donatur' && $kategori != '') { $sql2 .= " AND target_bantuan = '$kategori'"; }
    $sql2 .= " ORDER BY created_at DESC";
    $query_permintaan = mysqli_query($conn, $sql2);
}
?>

<?php include 'components/header.php'; ?>

<main class="pt-36 pb-20 min-h-screen bg-transparent">
  <div class="container mx-auto px-4 relative z-10">
    
    <div class="text-center mb-10">
      <h1 class="text-4xl font-extrabold text-slate-900 mb-4">Program Bantuan Aktif</h1>
      <?php if ($role === 'desa'): ?>
        <p class="text-slate-600 max-w-2xl mx-auto font-medium">Temukan berbagai penawaran bantuan dari instansi/donatur yang siap disalurkan ke desa Anda.</p>
      <?php elseif ($role === 'donatur'): ?>
        <p class="text-slate-600 max-w-2xl mx-auto font-medium">Daftar desa yang membutuhkan uluran tangan Anda. Pilih program yang ingin Anda danai untuk mulai membawa perubahan.</p>
      <?php else: ?>
        <p class="text-slate-600 max-w-2xl mx-auto font-medium">Jelajahi ekosistem SI BanTal. Lihat desa yang membutuhkan bantuan atau temukan donatur yang siap menyalurkan dananya.</p>
      <?php endif; ?>
    </div>

    <div class="bg-white/80 backdrop-blur-md p-4 rounded-xl shadow-lg border border-white/50 mb-10 max-w-4xl mx-auto">
        <form action="" method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="flex-grow">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari nama instansi, desa, atau kata kunci..." class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none bg-white/90">
            </div>
            
            <?php if ($role !== 'guest'): ?>
            <div class="w-full md:w-64">
                <?php if ($role === 'desa'): ?>
                    <select name="kategori" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none bg-white/90">
                        <option value="">Semua Jenis Bantuan</option>
                        <option value="Sembako" <?= ($kategori == 'Sembako') ? 'selected' : '' ?>>Sembako</option>
                        <option value="Dana Tunai" <?= ($kategori == 'Dana Tunai') ? 'selected' : '' ?>>Dana Tunai</option>
                        <option value="Material" <?= ($kategori == 'Material') ? 'selected' : '' ?>>Material Bangunan</option>
                    </select>
                <?php elseif ($role === 'donatur'): ?>
                    <select name="kategori" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none bg-white/90">
                        <option value="">Semua Target</option>
                        <option value="warga" <?= ($kategori == 'warga') ? 'selected' : '' ?>>Warga Terdampak</option>
                        <option value="fasilitas" <?= ($kategori == 'fasilitas') ? 'selected' : '' ?>>Fasilitas Umum</option>
                    </select>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <button type="submit" class="bg-teal-500 hover:bg-teal-600 text-white font-bold py-3 px-8 rounded-lg transition shadow-md">Cari</button>
            <?php if($search != '' || $kategori != ''): ?>
                <a href="portofolio.php" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-3 px-4 rounded-lg transition text-center flex items-center justify-center">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if ($role === 'donatur' || $role === 'guest'): ?>
        
        <?php if ($role === 'guest'): ?>
            <div class="flex items-center mb-6 mt-12">
                <div class="w-8 h-8 bg-amber-500 rounded-full flex items-center justify-center mr-3 shadow-md"><span class="text-white font-bold">1</span></div>
                <h2 class="text-2xl font-bold text-slate-800">Daftar Kebutuhan Desa</h2>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
            <?php if(mysqli_num_rows($query_permintaan) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($query_permintaan)): ?>
                    <?php 
                        // Tentukan link tujuan berdasarkan status login
                        $link_tujuan = ($role === 'guest') ? 'login.php' : 'detail.php?id=' . $row['id'] . '&tipe=permintaan'; 
                    ?>
                    <a href="<?= $link_tujuan ?>" class="block group h-full">
                        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/50 overflow-hidden group-hover:shadow-2xl group-hover:-translate-y-2 transition-all duration-300 transform flex flex-col h-full cursor-pointer">
                            <div class="bg-amber-500 p-4 relative">
                                <span class="bg-white text-amber-600 text-xs font-bold px-3 py-1 rounded-full absolute top-4 right-4 shadow capitalize">Target: <?= $row['target_bantuan'] ?></span>
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
                                    
                                    <?php if ($role === 'guest'): ?>
                                        <div class="block text-center w-full bg-slate-800 group-hover:bg-slate-900 text-white font-bold py-2.5 rounded-lg transition duration-300 mt-3 shadow-md">🔒 Login untuk Mendanai</div>
                                    <?php else: ?>
                                        <div class="block text-center w-full bg-teal-500 group-hover:bg-teal-600 text-white font-bold py-2.5 rounded-lg transition duration-300 mt-3 shadow-md shadow-teal-500/30">Lihat Detail & Danai</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-10 bg-white/60 backdrop-blur-sm rounded-2xl border border-dashed border-slate-300">
                    <p class="text-slate-500 font-medium">Belum ada pengajuan bantuan dari desa yang diverifikasi.</p>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($role === 'desa' || $role === 'guest'): ?>
        
        <?php if ($role === 'guest'): ?>
            <div class="flex items-center mb-6 mt-12 border-t border-slate-200/50 pt-12">
                <div class="w-8 h-8 bg-teal-500 rounded-full flex items-center justify-center mr-3 shadow-md"><span class="text-white font-bold">2</span></div>
                <h2 class="text-2xl font-bold text-slate-800">Daftar Penawaran Donatur</h2>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
            <?php if(mysqli_num_rows($query_penawaran) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($query_penawaran)): ?>
                    <?php 
                        // Tentukan link tujuan berdasarkan status login
                        $link_tujuan = ($role === 'guest') ? 'login.php' : 'detail.php?id=' . $row['id'] . '&tipe=penawaran'; 
                    ?>
                    <a href="<?= $link_tujuan ?>" class="block group h-full">
                        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/50 overflow-hidden group-hover:shadow-2xl group-hover:-translate-y-2 transition-all duration-300 transform flex flex-col h-full cursor-pointer">
                            <div class="bg-teal-500 p-4 relative">
                                <span class="bg-white text-teal-600 text-xs font-bold px-3 py-1 rounded-full absolute top-4 right-4 shadow"><?= $row['jenis_penawaran'] ?></span>
                                <h3 class="text-xl font-bold text-white mt-6"><?= $row['nama_instansi'] ?></h3>
                                <p class="text-teal-100 text-sm">PJ: <?= $row['pj_donatur'] ?></p>
                            </div>
                            <div class="p-6 flex-grow flex flex-col">
                                <h4 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-2">Detail Bantuan</h4>
                                <p class="text-slate-700 text-sm mb-4 line-clamp-3"><?= $row['detail_bantuan'] ?></p>
                                <div class="mt-auto pt-4 border-t border-slate-100">
                                    <p class="text-xs text-slate-400 mb-3">Tgl Penawaran: <?= date('d M Y', strtotime($row['created_at'])) ?></p>
                                    
                                    <?php if ($role === 'guest'): ?>
                                        <div class="block text-center w-full bg-slate-800 group-hover:bg-slate-900 text-white font-bold py-2.5 rounded-lg transition duration-300 mt-3 shadow-md">🔒 Login untuk Mengajukan</div>
                                    <?php else: ?>
                                        <div class="block text-center w-full bg-amber-500 group-hover:bg-amber-600 text-white font-bold py-2.5 rounded-lg transition duration-300 mt-3 shadow-md shadow-amber-500/30">Lihat Detail & Klaim</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-10 bg-white/60 backdrop-blur-sm rounded-2xl border border-dashed border-slate-300">
                    <p class="text-slate-500 font-medium">Belum ada penawaran bantuan dari donatur yang tersedia saat ini.</p>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

  </div>
</main>

<?php include 'components/footer.php'; ?>