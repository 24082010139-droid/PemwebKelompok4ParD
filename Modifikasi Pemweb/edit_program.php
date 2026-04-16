<?php
require_once 'auth_check.php';
wajib_login();
require_once 'koneksi.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$tipe = isset($_GET['tipe']) ? $_GET['tipe'] : '';

$success = '';
$error = '';

// ========================================================
// 1. VALIDASI KEPEMILIKAN DAN STATUS PROGRAM
// ========================================================
$tabel = ($tipe === 'permintaan') ? 'permintaan_bantuan' : 'penawaran_bantuan';
$query_cek = "SELECT * FROM $tabel WHERE id = '$id' AND user_id = '$user_id'";
$result_cek = mysqli_query($conn, $query_cek);

if (mysqli_num_rows($result_cek) === 0) {
    die("<div class='p-10 text-center text-xl text-slate-500'>Data tidak ditemukan atau Anda tidak berhak mengedit data ini. <a href='index.php' class='text-teal-500 underline'>Kembali</a></div>");
}

$data_lama = mysqli_fetch_assoc($result_cek);

if ($data_lama['status'] !== 'pending') {
    die("<div class='p-10 text-center text-xl text-red-500'>Program ini sudah diproses (Disetujui/Ditolak) sehingga tidak dapat diedit lagi. <a href='index.php' class='text-teal-500 underline'>Kembali</a></div>");
}


// ========================================================
// 2. PROSES UPDATE DATA
// ========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ----- PROSES EDIT DONATUR -----
    if ($tipe === 'penawaran' && isset($_POST['update_donatur'])) {
        $jabatan_donatur = mysqli_real_escape_string($conn, $_POST['jabatan_donatur']);
        $kontak_donatur = mysqli_real_escape_string($conn, $_POST['kontak_donatur']);
        $jenis_penawaran = mysqli_real_escape_string($conn, $_POST['jenis_penawaran']);
        $detail_bantuan = mysqli_real_escape_string($conn, $_POST['detail_bantuan']);
        
        // Logika Upload Dokumen Baru (Opsional)
        $nama_file_donatur = $data_lama['dokumen_donatur']; // Default: pakai file lama
        if (isset($_FILES['dokumen_donatur']) && $_FILES['dokumen_donatur']['error'] === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['dokumen_donatur']['tmp_name'];
            $nama_file_donatur = time() . '_edit_' . basename($_FILES['dokumen_donatur']['name']);
            move_uploaded_file($tmp_name, 'uploads/' . $nama_file_donatur);
        }

        $query_update = "UPDATE penawaran_bantuan SET 
                         jabatan_donatur = '$jabatan_donatur', kontak_donatur = '$kontak_donatur', 
                         jenis_penawaran = '$jenis_penawaran', detail_bantuan = '$detail_bantuan', 
                         dokumen_donatur = '$nama_file_donatur' 
                         WHERE id = '$id'";

        if (mysqli_query($conn, $query_update)) {
            $success = "Data penawaran berhasil diperbarui!";
            $data_lama['jenis_penawaran'] = $jenis_penawaran; // Update UI langsung
            $data_lama['detail_bantuan'] = $detail_bantuan;
        } else {
            $error = "Gagal memperbarui: " . mysqli_error($conn);
        }
    }

    // ----- PROSES EDIT DESA -----
    if ($tipe === 'permintaan' && isset($_POST['update_desa'])) {
        $jabatan = mysqli_real_escape_string($conn, $_POST['jabatan']);
        $target_bantuan = mysqli_real_escape_string($conn, $_POST['target_bantuan']);
        $jumlah_kk = !empty($_POST['jumlah_kk']) ? intval($_POST['jumlah_kk']) : 'NULL'; 
        $provinsi = mysqli_real_escape_string($conn, $_POST['provinsi']);
        $kota = mysqli_real_escape_string($conn, $_POST['kota']);
        $kecamatan = mysqli_real_escape_string($conn, $_POST['kecamatan']);
        $alasan = mysqli_real_escape_string($conn, $_POST['alasan']);
        
        // Logika Upload Dokumen Baru (Opsional)
        $nama_file_desa = $data_lama['dokumen_desa']; // Default: pakai file lama
        if (isset($_FILES['dokumen_desa']) && $_FILES['dokumen_desa']['error'] === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['dokumen_desa']['tmp_name'];
            $nama_file_desa = time() . '_edit_desa_' . basename($_FILES['dokumen_desa']['name']);
            move_uploaded_file($tmp_name, 'uploads/' . $nama_file_desa);
        }

        $query_update = "UPDATE permintaan_bantuan SET 
                         jabatan = '$jabatan', target_bantuan = '$target_bantuan', jumlah_kk = $jumlah_kk, 
                         provinsi = '$provinsi', kota = '$kota', kecamatan = '$kecamatan', alasan = '$alasan', 
                         dokumen_desa = '$nama_file_desa' 
                         WHERE id = '$id'";

        if (mysqli_query($conn, $query_update)) {
            $success = "Data pengajuan desa berhasil diperbarui!";
            $data_lama['alasan'] = $alasan; // Update UI langsung
        } else {
            $error = "Gagal memperbarui: " . mysqli_error($conn);
        }
    }
}
?>

<?php include 'components/header.php'; ?>

<main class="pt-36 pb-20 min-h-screen bg-transparent">
  <div class="container mx-auto px-4 relative z-10">
    
    <div class="max-w-4xl mx-auto bg-white/90 backdrop-blur-md rounded-2xl shadow-xl overflow-hidden border border-slate-100">
      
      <div class="bg-slate-800 p-8 text-center text-white relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-full bg-slate-900 opacity-50 transform -skew-y-3 origin-top-left z-0"></div>
        <div class="relative z-10">
            <h2 class="text-3xl font-bold mb-2">Edit Form Pengajuan</h2>
            <p class="text-slate-300">Perbarui informasi yang salah sebelum diverifikasi oleh Admin.</p>
        </div>
      </div>

      <div class="p-8 md:p-12">
        <div class="flex justify-between items-center mb-6">
            <a href="javascript:history.back()" class="text-slate-500 hover:text-teal-600 font-bold text-sm underline">&larr; Kembali ke Dashboard</a>
        </div>

        <?php if($success): ?>
            <div class="mb-8 bg-green-50 text-green-700 p-4 rounded-lg font-medium border border-green-200"><?= $success ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="mb-8 bg-red-50 text-red-700 p-4 rounded-lg font-medium border border-red-200"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($tipe === 'penawaran'): ?>
            <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Instansi</label>
                        <input type="text" value="<?= htmlspecialchars($_SESSION['nama_organisasi'] ?? '') ?>" readonly class="w-full px-4 py-3 border border-slate-300 rounded-lg bg-slate-200 text-slate-500 cursor-not-allowed outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Penanggung Jawab</label>
                        <input type="text" value="<?= htmlspecialchars($_SESSION['nama_lengkap'] ?? '') ?>" readonly class="w-full px-4 py-3 border border-slate-300 rounded-lg bg-slate-200 text-slate-500 cursor-not-allowed outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Jabatan PJ</label>
                        <input type="text" name="jabatan_donatur" value="<?= htmlspecialchars($data_lama['jabatan_donatur']) ?>" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none bg-slate-50 focus:bg-white transition" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Kontak (No. HP/WA)</label>
                        <input type="text" name="kontak_donatur" value="<?= htmlspecialchars($data_lama['kontak_donatur']) ?>" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none bg-slate-50 focus:bg-white transition" required>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Jenis Penawaran</label>
                    <select name="jenis_penawaran" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none bg-slate-50 focus:bg-white transition" required>
                        <option value="Sembako" <?= ($data_lama['jenis_penawaran'] == 'Sembako') ? 'selected' : '' ?>>Paket Sembako</option>
                        <option value="Dana Tunai" <?= ($data_lama['jenis_penawaran'] == 'Dana Tunai') ? 'selected' : '' ?>>Dana Tunai</option>
                        <option value="Material" <?= ($data_lama['jenis_penawaran'] == 'Material') ? 'selected' : '' ?>>Material Bangunan</option>
                        <option value="Lainnya" <?= ($data_lama['jenis_penawaran'] == 'Lainnya') ? 'selected' : '' ?>>Lainnya</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Detail Bantuan</label>
                    <textarea name="detail_bantuan" rows="4" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none bg-slate-50 focus:bg-white transition" required><?= htmlspecialchars($data_lama['detail_bantuan']) ?></textarea>
                </div>

                <div class="bg-blue-50 p-4 border border-blue-200 rounded-lg">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Ganti Dokumen (Opsional)</label>
                    <input type="file" name="dokumen_donatur" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="w-full px-4 py-2 border border-white rounded-lg bg-white cursor-pointer">
                    <p class="text-xs text-blue-500 mt-2">*Biarkan kosong jika tidak ingin mengubah dokumen sebelumnya.</p>
                </div>

                <div class="pt-4">
                    <button type="submit" name="update_donatur" class="w-full bg-slate-800 hover:bg-teal-500 text-white font-bold py-4 rounded-lg shadow-lg transform transition duration-300">Simpan Perubahan</button>
                </div>
            </form>

        <?php elseif ($tipe === 'permintaan'): ?>
            <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Penanggung Jawab</label>
                        <input type="text" value="<?= htmlspecialchars($_SESSION['nama_lengkap'] ?? '') ?>" readonly class="w-full px-4 py-3 border border-slate-300 rounded-lg bg-slate-200 text-slate-500 cursor-not-allowed outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Jabatan</label>
                        <input type="text" name="jabatan" value="<?= htmlspecialchars($data_lama['jabatan']) ?>" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none bg-white transition" required>
                    </div>
                </div>

                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 mb-6 mt-4">
                    <h4 class="font-bold text-slate-700 mb-4 border-b border-slate-200 pb-2">Informasi Wilayah Desa</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Provinsi</label>
                            <input type="text" name="provinsi" value="<?= htmlspecialchars($data_lama['provinsi']) ?>" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none bg-white transition" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Kota / Kabupaten</label>
                            <input type="text" name="kota" value="<?= htmlspecialchars($data_lama['kota']) ?>" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none bg-white transition" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Kecamatan</label>
                            <input type="text" name="kecamatan" value="<?= htmlspecialchars($data_lama['kecamatan']) ?>" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none bg-white transition" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Desa</label>
                            <input type="text" value="<?= htmlspecialchars($_SESSION['asal_desa'] ?? '') ?>" readonly class="w-full px-4 py-3 border border-slate-300 rounded-lg bg-slate-200 text-slate-500 cursor-not-allowed outline-none">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Target Bantuan</label>
                        <select name="target_bantuan" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none bg-slate-50 focus:bg-white transition" required>
                            <option value="warga" <?= ($data_lama['target_bantuan'] == 'warga') ? 'selected' : '' ?>>Warga Terdampak</option>
                            <option value="fasilitas" <?= ($data_lama['target_bantuan'] == 'fasilitas') ? 'selected' : '' ?>>Fasilitas Umum</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Jumlah KK</label>
                        <input type="number" name="jumlah_kk" value="<?= htmlspecialchars($data_lama['jumlah_kk']) ?>" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none bg-slate-50 focus:bg-white transition">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Alasan Permintaan / Kondisi</label>
                    <textarea name="alasan" rows="5" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none bg-slate-50 focus:bg-white transition" required><?= htmlspecialchars($data_lama['alasan']) ?></textarea>
                </div>

                <div class="bg-blue-50 p-4 border border-blue-200 rounded-lg">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Ganti Dokumen (Opsional)</label>
                    <input type="file" name="dokumen_desa" accept=".pdf,.jpg,.jpeg,.png" class="w-full px-4 py-2 border border-white rounded-lg bg-white cursor-pointer">
                    <p class="text-xs text-blue-500 mt-2">*Biarkan kosong jika tidak ingin mengubah dokumen sebelumnya.</p>
                </div>
                
                <div class="pt-4">
                    <button type="submit" name="update_desa" class="w-full bg-slate-800 hover:bg-amber-500 text-white font-bold py-4 rounded-lg shadow-lg transform transition duration-300">Simpan Perubahan</button>
                </div>
            </form>
        <?php endif; ?>

      </div>
    </div>
  </div>
</main>

<?php include 'components/footer.php'; ?>