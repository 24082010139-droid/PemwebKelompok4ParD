<?php
require_once 'auth_check.php';
wajib_login('admin'); // Proteksi ketat, HANYA admin yang boleh masuk

require_once 'koneksi.php';

$pesan = '';

// ========================================================
// LOGIKA 1: MENYETUJUI / MENOLAK DATA
// ========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_status'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $tipe = mysqli_real_escape_string($conn, $_POST['tipe']); // 'desa' atau 'donatur'
    $status_baru = mysqli_real_escape_string($conn, $_POST['action_status']); // 'approved' atau 'rejected'

    $tabel = ($tipe === 'desa') ? 'permintaan_bantuan' : 'penawaran_bantuan';
    $update_query = "UPDATE $tabel SET status = '$status_baru' WHERE id = '$id'";
    
    if (mysqli_query($conn, $update_query)) {
        $warna_alert = ($status_baru === 'approved') ? 'bg-green-100 text-green-700 border-green-200' : 'bg-red-100 text-red-700 border-red-200';
        $teks_status = ($status_baru === 'approved') ? 'disetujui' : 'ditolak';
        $pesan = "<div class='mb-6 p-4 $warna_alert border rounded-lg font-semibold flex items-center justify-between'>
                    <span>Status data berhasil diubah menjadi $teks_status!</span>
                    <button onclick=\"this.parentElement.style.display='none'\">&times;</button>
                  </div>";
    }
}

// ========================================================
// LOGIKA 2: MENGHAPUS DATA PERMANEN
// ========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_data'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $tipe = mysqli_real_escape_string($conn, $_POST['tipe']); 

    // Tentukan nama tabel dan nama kolom file secara dinamis
    $tabel = ($tipe === 'desa') ? 'permintaan_bantuan' : 'penawaran_bantuan';
    $kolom_file = ($tipe === 'desa') ? 'dokumen_desa' : 'dokumen_donatur';

    // Hapus file fisik dari folder uploads agar server tidak penuh
    $query_file = mysqli_query($conn, "SELECT $kolom_file FROM $tabel WHERE id = '$id'");
    
    // Tambahkan pengecekan if ($query_file) agar tidak error jika query gagal
    if ($query_file && $row_file = mysqli_fetch_assoc($query_file)) {
        $nama_file = $row_file[$kolom_file];
        if (!empty($nama_file) && file_exists("uploads/" . $nama_file)) {
            unlink("uploads/" . $nama_file); 
        }
    }

    $hapus_query = "DELETE FROM $tabel WHERE id = '$id'";
    if (mysqli_query($conn, $hapus_query)) {
        $pesan = "<div class='mb-6 p-4 bg-slate-800 text-white border border-slate-700 rounded-lg font-semibold flex items-center justify-between'>
                    <span>🗑️ Data program berhasil dihapus permanen!</span>
                    <button onclick=\"this.parentElement.style.display='none'\">&times;</button>
                  </div>";
    }
}

// ========================================================
// MENGAMBIL DATA DARI DATABASE
// ========================================================
$query_desa = mysqli_query($conn, "SELECT * FROM permintaan_bantuan ORDER BY FIELD(status, 'pending', 'approved', 'rejected'), created_at DESC");
$query_donatur = mysqli_query($conn, "SELECT * FROM penawaran_bantuan ORDER BY FIELD(status, 'pending', 'approved', 'rejected'), created_at DESC");

$pending_desa = mysqli_query($conn, "SELECT COUNT(*) as total FROM permintaan_bantuan WHERE status='pending'");
$count_desa = mysqli_fetch_assoc($pending_desa)['total'];

$pending_donatur = mysqli_query($conn, "SELECT COUNT(*) as total FROM penawaran_bantuan WHERE status='pending'");
$count_donatur = mysqli_fetch_assoc($pending_donatur)['total'];
?>

<?php include 'components/header.php'; ?>

<main class="pt-36 pb-20 min-h-screen bg-slate-100">
  <div class="container mx-auto px-4">
    
    <div class="bg-slate-900 text-white rounded-2xl shadow-xl p-8 mb-8 flex flex-col md:flex-row justify-between items-center relative overflow-hidden">
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-48 h-48 bg-teal-500 rounded-full opacity-20 blur-2xl"></div>
        
        <div class="relative z-10 mb-4 md:mb-0">
            <h1 class="text-3xl font-bold">Panel Administrator</h1>
            <p class="text-slate-400 mt-1">Kelola verifikasi pengajuan dan penawaran bantuan.</p>
        </div>
        
        <div class="relative z-10 flex items-center gap-3 md:gap-4">
            <div class="text-center px-4 py-2 border border-slate-700 rounded-lg bg-slate-800">
                <span class="block text-xs text-slate-400">Antrean Desa</span>
                <span class="text-xl font-bold text-amber-400"><?= $count_desa ?></span>
            </div>
            <div class="text-center px-4 py-2 border border-slate-700 rounded-lg bg-slate-800">
                <span class="block text-xs text-slate-400">Antrean Donatur</span>
                <span class="text-xl font-bold text-teal-400"><?= $count_donatur ?></span>
            </div>
        </div>
    </div> <?= $pesan ?>

    <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden mb-10">
        <div class="bg-amber-500 p-4">
            <h2 class="text-xl font-bold text-white">📋 Data Pengajuan Perangkat Desa</h2>
        </div>
        <div class="overflow-x-auto p-4">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b-2 border-slate-200 text-sm text-slate-600">
                        <th class="p-3">Tanggal</th>
                        <th class="p-3">Desa / PJ</th>
                        <th class="p-3">Kebutuhan</th>
                        <th class="p-3">Dokumen</th>
                        <th class="p-3 text-center">Status</th>
                        <th class="p-3 text-center">Aksi Admin</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <?php if(mysqli_num_rows($query_desa) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($query_desa)): ?>
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="p-3 text-slate-500"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                            <td class="p-3">
                                <span class="font-bold text-slate-800 block"><?= $row['desa'] ?></span>
                                <span class="text-xs text-slate-500"><?= $row['nama_pj'] ?></span>
                            </td>
                            <td class="p-3">
                                <span class="bg-amber-100 text-amber-800 px-2 py-1 rounded text-xs font-bold"><?= ucfirst($row['target_bantuan']) ?></span>
                            </td>
                            <td class="p-3">
                                <?php if(!empty($row['dokumen_desa'])): ?>
                                    <a href="uploads/<?= $row['dokumen_desa'] ?>" target="_blank" class="text-teal-600 hover:underline text-xs flex items-center">Lihat File ↗</a>
                                <?php else: ?>
                                    <span class="text-xs text-slate-400">Tidak ada</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-3 text-center">
                                <?php 
                                    if($row['status'] == 'pending') echo '<span class="bg-slate-200 text-slate-700 px-3 py-1 rounded-full text-xs font-bold">Menunggu</span>';
                                    elseif($row['status'] == 'approved') echo '<span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold">Disetujui</span>';
                                    elseif($row['status'] == 'rejected') echo '<span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold">Ditolak</span>';
                                ?>
                            </td>
                            
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <?php if($row['status'] == 'pending'): ?>
                                        <form method="POST" class="inline-block">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <input type="hidden" name="tipe" value="desa"> 
                                            <button type="submit" name="action_status" value="approved" class="bg-green-500 hover:bg-green-600 text-white px-2 py-1.5 rounded text-xs font-bold transition" title="Setujui">✓</button>
                                            <button type="submit" name="action_status" value="rejected" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1.5 rounded text-xs font-bold transition" title="Tolak">✕</button>
                                        </form>
                                    <?php endif; ?>

                                    <div class="inline-block <?= ($row['status'] == 'pending') ? 'ml-1 border-l pl-2 border-slate-200' : '' ?>">
                                        <a href="detail.php?id=<?= $row['id'] ?>&tipe=permintaan" class="bg-teal-500 hover:bg-teal-600 text-white px-2 py-1.5 rounded text-xs font-bold transition inline-flex items-center mr-1" title="Lihat Detail">👁️</a>
                                        <a href="edit.php?id=<?= $row['id'] ?>&tipe=desa" class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1.5 rounded text-xs font-bold transition inline-flex items-center mr-1">✏️ Edit</a>
                                        <form method="POST" class="inline-block">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <input type="hidden" name="tipe" value="desa"> 
                                            <button type="submit" name="hapus_data" class="bg-slate-700 hover:bg-slate-900 text-white px-2 py-1.5 rounded text-xs font-bold transition flex items-center" onclick="return confirm('Yakin ingin menghapus data ini secara permanen?')">🗑️</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="p-6 text-center text-slate-500">Belum ada data pengajuan desa.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden mb-10">
        <div class="bg-teal-500 p-4">
            <h2 class="text-xl font-bold text-white">📦 Data Penawaran Donatur</h2>
        </div>
        <div class="overflow-x-auto p-4">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b-2 border-slate-200 text-sm text-slate-600">
                        <th class="p-3">Tanggal</th>
                        <th class="p-3">Instansi / PJ</th>
                        <th class="p-3">Jenis Bantuan</th>
                        <th class="p-3">Dokumen</th>
                        <th class="p-3 text-center">Status</th>
                        <th class="p-3 text-center">Aksi Admin</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <?php if(mysqli_num_rows($query_donatur) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($query_donatur)): ?>
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="p-3 text-slate-500"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                            <td class="p-3">
                                <span class="font-bold text-slate-800 block"><?= $row['nama_instansi'] ?></span>
                                <span class="text-xs text-slate-500"><?= $row['pj_donatur'] ?></span>
                            </td>
                            <td class="p-3">
                                <span class="bg-teal-100 text-teal-800 px-2 py-1 rounded text-xs font-bold"><?= $row['jenis_penawaran'] ?></span>
                            </td>
                            <td class="p-3">
                                <?php if(!empty($row['dokumen_donatur'])): ?>
                                    <a href="uploads/<?= $row['dokumen_donatur'] ?>" target="_blank" class="text-teal-600 hover:underline text-xs flex items-center">Lihat File ↗</a>
                                <?php else: ?>
                                    <span class="text-xs text-slate-400">Tidak ada</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-3 text-center">
                                <?php 
                                    if($row['status'] == 'pending') echo '<span class="bg-slate-200 text-slate-700 px-3 py-1 rounded-full text-xs font-bold">Menunggu</span>';
                                    elseif($row['status'] == 'approved') echo '<span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold">Disetujui</span>';
                                    elseif($row['status'] == 'rejected') echo '<span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold">Ditolak</span>';
                                ?>
                            </td>
                            
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <?php if($row['status'] == 'pending'): ?>
                                        <form method="POST" class="inline-block">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <input type="hidden" name="tipe" value="donatur"> 
                                            <button type="submit" name="action_status" value="approved" class="bg-green-500 hover:bg-green-600 text-white px-2 py-1.5 rounded text-xs font-bold transition" title="Setujui">✓</button>
                                            <button type="submit" name="action_status" value="rejected" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1.5 rounded text-xs font-bold transition" title="Tolak">✕</button>
                                        </form>
                                    <?php endif; ?>

                                    <div class="inline-block <?= ($row['status'] == 'pending') ? 'ml-1 border-l pl-2 border-slate-200' : '' ?>">
                                        <a href="detail.php?id=<?= $row['id'] ?>&tipe=penawaran" class="bg-teal-500 hover:bg-teal-600 text-white px-2 py-1.5 rounded text-xs font-bold transition inline-flex items-center mr-1" title="Lihat Detail">👁️</a>
                                        <a href="edit.php?id=<?= $row['id'] ?>&tipe=donatur" class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1.5 rounded text-xs font-bold transition inline-flex items-center mr-1">✏️ Edit</a>
                                        <form method="POST" class="inline-block">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <input type="hidden" name="tipe" value="donatur"> 
                                            <button type="submit" name="hapus_data" class="bg-slate-700 hover:bg-slate-900 text-white px-2 py-1.5 rounded text-xs font-bold transition flex items-center" onclick="return confirm('Yakin ingin menghapus data ini secara permanen?')">🗑️</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="p-6 text-center text-slate-500">Belum ada data penawaran donatur.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

  </div>
</main>

<?php include 'components/footer.php'; ?>