<?php
require_once 'auth_check.php';
wajib_login('admin'); // Hanya admin yang boleh mengedit

require_once 'koneksi.php';

if (!isset($_GET['id']) || !isset($_GET['tipe'])) {
    header("Location: dashboard-admin.php");
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);
$tipe = mysqli_real_escape_string($conn, $_GET['tipe']);
$tabel = ($tipe === 'desa') ? 'permintaan_bantuan' : 'penawaran_bantuan';

$pesan = '';

// ========================================================
// PROSES UPDATE DATA KE DATABASE
// ========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($tipe === 'donatur') {
        $nama_instansi = mysqli_real_escape_string($conn, $_POST['nama_instansi']);
        $pj_donatur = mysqli_real_escape_string($conn, $_POST['pj_donatur']);
        $jenis_penawaran = mysqli_real_escape_string($conn, $_POST['jenis_penawaran']);
        $detail_bantuan = mysqli_real_escape_string($conn, $_POST['detail_bantuan']);

        $query_update = "UPDATE penawaran_bantuan SET 
                         nama_instansi = '$nama_instansi', 
                         pj_donatur = '$pj_donatur', 
                         jenis_penawaran = '$jenis_penawaran', 
                         detail_bantuan = '$detail_bantuan' 
                         WHERE id = '$id'";

    } else if ($tipe === 'desa') {
        $nama_pj = mysqli_real_escape_string($conn, $_POST['nama_pj']);
        $desa = mysqli_real_escape_string($conn, $_POST['desa']);
        $target_bantuan = mysqli_real_escape_string($conn, $_POST['target_bantuan']);
        $jumlah_kk = !empty($_POST['jumlah_kk']) ? intval($_POST['jumlah_kk']) : 'NULL';
        $alasan = mysqli_real_escape_string($conn, $_POST['alasan']);

        $query_update = "UPDATE permintaan_bantuan SET 
                         nama_pj = '$nama_pj', 
                         desa = '$desa', 
                         target_bantuan = '$target_bantuan', 
                         jumlah_kk = $jumlah_kk, 
                         alasan = '$alasan' 
                         WHERE id = '$id'";
    }

    if (mysqli_query($conn, $query_update)) {
        $pesan = "<div class='mb-6 p-4 bg-green-100 text-green-700 border border-green-200 rounded-lg font-bold'>Data berhasil diperbarui! <a href='dashboard-admin.php' class='underline'>Kembali ke Dashboard</a></div>";
    } else {
        $pesan = "<div class='mb-6 p-4 bg-red-100 text-red-700 border border-red-200 rounded-lg'>Gagal memperbarui: " . mysqli_error($conn) . "</div>";
    }
}

// ========================================================
// AMBIL DATA LAMA UNTUK DITAMPILKAN DI FORM
// ========================================================
$query_data = mysqli_query($conn, "SELECT * FROM $tabel WHERE id = '$id'");
if (mysqli_num_rows($query_data) === 0) {
    die("Data tidak ditemukan.");
}
$data = mysqli_fetch_assoc($query_data);
?>

<?php include 'components/header.php'; ?>

<main class="pt-36 pb-20 min-h-screen bg-slate-50">
  <div class="container mx-auto px-4 max-w-3xl">
    
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-100">
      <div class="bg-teal-500 p-6 text-white flex justify-between items-center relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-32 h-32 bg-teal-400 rounded-full opacity-50 blur-xl"></div>
        <div class="relative z-10">
            <h2 class="text-2xl font-bold">Edit Data <?= ucfirst($tipe) ?></h2>
        </div>
        <a href="dashboard-admin.php" class="relative z-10 text-teal-100 hover:text-white text-sm font-bold transition">← Kembali</a>
      </div>

      <div class="p-8">
        <?= $pesan ?>

        <?php if ($tipe === 'donatur'): ?>
            <form action="" method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Instansi</label>
                    <input type="text" name="nama_instansi" value="<?= htmlspecialchars($data['nama_instansi']) ?>" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none bg-slate-50 focus:bg-white transition" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Penanggung Jawab (PJ)</label>
                    <input type="text" name="pj_donatur" value="<?= htmlspecialchars($data['pj_donatur']) ?>" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none bg-slate-50 focus:bg-white transition" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Jenis Penawaran</label>
                    <select name="jenis_penawaran" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none bg-slate-50 focus:bg-white transition" required>
                        <option value="Sembako" <?= ($data['jenis_penawaran'] == 'Sembako') ? 'selected' : '' ?>>Paket Sembako</option>
                        <option value="Dana Tunai" <?= ($data['jenis_penawaran'] == 'Dana Tunai') ? 'selected' : '' ?>>Dana Tunai</option>
                        <option value="Material" <?= ($data['jenis_penawaran'] == 'Material') ? 'selected' : '' ?>>Material Bangunan</option>
                        <option value="Lainnya" <?= ($data['jenis_penawaran'] == 'Lainnya') ? 'selected' : '' ?>>Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Detail Bantuan</label>
                    <textarea name="detail_bantuan" rows="4" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none bg-slate-50 focus:bg-white transition" required><?= htmlspecialchars($data['detail_bantuan']) ?></textarea>
                </div>
                <button type="submit" class="w-full bg-teal-500 text-white font-bold py-3 rounded-lg hover:bg-teal-600 shadow-lg hover:shadow-teal-500/30 transform transition duration-300 mt-4">Simpan Perubahan</button>
            </form>

        <?php elseif ($tipe === 'desa'): ?>
            <form action="" method="POST" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Desa</label>
                        <input type="text" name="desa" value="<?= htmlspecialchars($data['desa']) ?>" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none bg-slate-50 focus:bg-white transition" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Penanggung Jawab (PJ)</label>
                        <input type="text" name="nama_pj" value="<?= htmlspecialchars($data['nama_pj']) ?>" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none bg-slate-50 focus:bg-white transition" required>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Target Bantuan</label>
                        <select name="target_bantuan" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none bg-slate-50 focus:bg-white transition" required>
                            <option value="warga" <?= ($data['target_bantuan'] == 'warga') ? 'selected' : '' ?>>Warga Terdampak</option>
                            <option value="fasilitas" <?= ($data['target_bantuan'] == 'fasilitas') ? 'selected' : '' ?>>Fasilitas Umum</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Jumlah KK</label>
                        <input type="number" name="jumlah_kk" value="<?= htmlspecialchars($data['jumlah_kk'] ?? '') ?>" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none bg-slate-50 focus:bg-white transition">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Alasan / Kondisi</label>
                    <textarea name="alasan" rows="5" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none bg-slate-50 focus:bg-white transition" required><?= htmlspecialchars($data['alasan']) ?></textarea>
                </div>
                <button type="submit" class="w-full bg-teal-500 text-white font-bold py-3 rounded-lg hover:bg-teal-600 shadow-lg hover:shadow-teal-500/30 transform transition duration-300 mt-4">Simpan Perubahan</button>
            </form>
        <?php endif; ?>

      </div>
    </div>
  </div>
</main>

<?php include 'components/footer.php'; ?>