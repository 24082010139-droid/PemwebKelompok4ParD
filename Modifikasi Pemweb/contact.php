<?php
require_once 'auth_check.php';
wajib_login();

require_once 'koneksi.php';

$success = '';
$error = '';

// ========================================================
// LOGIKA DONATUR (Menangani Upload File)
// ========================================================
if (isset($_POST['submit_donatur'])) {
    $user_id = $_SESSION['user_id'];
    $nama_instansi = mysqli_real_escape_string($conn, $_POST['nama_instansi']);
    $pj_donatur = mysqli_real_escape_string($conn, $_POST['pj_donatur']);
    $jabatan_donatur = mysqli_real_escape_string($conn, $_POST['jabatan_donatur']);
    $kontak_donatur = mysqli_real_escape_string($conn, $_POST['kontak_donatur']);
    $jenis_penawaran = mysqli_real_escape_string($conn, $_POST['jenis_penawaran']);
    $detail_bantuan = mysqli_real_escape_string($conn, $_POST['detail_bantuan']);
    
    // Logika Upload File Donatur
    $nama_file_donatur = "";
    if (isset($_FILES['dokumen_donatur']) && $_FILES['dokumen_donatur']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['dokumen_donatur']['tmp_name'];
        // Buat nama unik agar tidak bentrok
        $nama_file_donatur = time() . '_' . basename($_FILES['dokumen_donatur']['name']);
        $lokasi_simpan = 'uploads/' . $nama_file_donatur;
        
        move_uploaded_file($tmp_name, $lokasi_simpan);
    }

    $query = "INSERT INTO penawaran_bantuan 
              (user_id, nama_instansi, pj_donatur, jabatan_donatur, kontak_donatur, jenis_penawaran, detail_bantuan, dokumen_donatur, status, is_funded) 
              VALUES 
              ('$user_id', '$nama_instansi', '$pj_donatur', '$jabatan_donatur', '$kontak_donatur', '$jenis_penawaran', '$detail_bantuan', '$nama_file_donatur', 'pending', 0)";

    if (mysqli_query($conn, $query)) {
        $success = "Penawaran bantuan beserta dokumen berhasil dikirim!";
    } else {
        $error = "Gagal mengirim penawaran: " . mysqli_error($conn);
    }
}

// ========================================================
// LOGIKA DESA (Menangani Upload File)
// ========================================================
if (isset($_POST['submit_desa'])) {
    $user_id = $_SESSION['user_id'];
    $nama_pj = mysqli_real_escape_string($conn, $_POST['nama_pj']);
    $jabatan = mysqli_real_escape_string($conn, $_POST['jabatan']);
    $target_bantuan = mysqli_real_escape_string($conn, $_POST['target_bantuan']);
    $jumlah_kk = !empty($_POST['jumlah_kk']) ? intval($_POST['jumlah_kk']) : 'NULL'; 
    $provinsi = mysqli_real_escape_string($conn, $_POST['provinsi']);
    $kota = mysqli_real_escape_string($conn, $_POST['kota']);
    $kecamatan = mysqli_real_escape_string($conn, $_POST['kecamatan']);
    $desa = mysqli_real_escape_string($conn, $_POST['desa']);
    $alasan = mysqli_real_escape_string($conn, $_POST['alasan']);
    
    // Logika Upload File Desa
    $nama_file_desa = "";
    if (isset($_FILES['dokumen_desa']) && $_FILES['dokumen_desa']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['dokumen_desa']['tmp_name'];
        // Buat nama unik
        $nama_file_desa = time() . '_desa_' . basename($_FILES['dokumen_desa']['name']);
        $lokasi_simpan = 'uploads/' . $nama_file_desa;
        
        move_uploaded_file($tmp_name, $lokasi_simpan);
    }

    $query = "INSERT INTO permintaan_bantuan 
              (user_id, nama_pj, jabatan, target_bantuan, jumlah_kk, provinsi, kota, kecamatan, desa, alasan, dokumen_desa, status, is_funded) 
              VALUES 
              ('$user_id', '$nama_pj', '$jabatan', '$target_bantuan', $jumlah_kk, '$provinsi', '$kota', '$kecamatan', '$desa', '$alasan', '$nama_file_desa', 'pending', 0)";

    if (mysqli_query($conn, $query)) {
        $success = "Permintaan bantuan beserta dokumen berhasil diajukan!";
    } else {
        $error = "Gagal mengajukan permintaan: " . mysqli_error($conn);
    }
}
?>

<?php include 'components/header.php'; ?>

<main class="pt-36 pb-20 min-h-screen bg-slate-50">
  <div class="container mx-auto px-4">
    
    <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-100">
      
      <?php if ($_SESSION['role'] === 'donatur'): ?>
          <div class="bg-teal-500 p-8 text-center text-white relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-full bg-teal-600 opacity-20 transform -skew-y-3 origin-top-left z-0"></div>
            <div class="relative z-10">
                <h2 class="text-3xl font-bold mb-2">Form Penawaran Bantuan</h2>
                <p class="text-teal-100">Lengkapi detail donasi yang ingin disalurkan untuk membantu desa.</p>
            </div>
          </div>
      <?php elseif ($_SESSION['role'] === 'desa'): ?>
          <div class="bg-amber-500 p-8 text-center text-white relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-full bg-amber-600 opacity-20 transform -skew-y-3 origin-top-left z-0"></div>
            <div class="relative z-10">
                <h2 class="text-3xl font-bold mb-2">Form Pengajuan Bantuan</h2>
                <p class="text-amber-100">Ajukan kebutuhan bantuan sosial (Warga/Fasilitas) untuk desa Anda.</p>
            </div>
          </div>
      <?php endif; ?>

      <div class="p-8 md:p-12">
        <?php if($success): ?>
            <div class="mb-8 bg-green-50 text-green-700 p-4 rounded-lg font-medium border border-green-200"><?= $success ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="mb-8 bg-red-50 text-red-700 p-4 rounded-lg font-medium border border-red-200"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($_SESSION['role'] === 'donatur'): ?>
            <form action="" method="POST" enctype="multipart/form-data" class="space-y-6" onsubmit="return validasiForm(event)">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Instansi/Perusahaan</label>
                        <input type="text" name="nama_instansi" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none bg-slate-50 focus:bg-white transition" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Penanggung Jawab (PJ)</label>
                        <input type="text" name="pj_donatur" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none bg-slate-50 focus:bg-white transition" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Jabatan PJ</label>
                        <input type="text" name="jabatan_donatur" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none bg-slate-50 focus:bg-white transition" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Kontak (No. HP/WA)</label>
                        <input type="text" name="kontak_donatur" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none bg-slate-50 focus:bg-white transition" required>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Jenis Penawaran</label>
                    <select name="jenis_penawaran" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none bg-slate-50 focus:bg-white transition" required>
                        <option value="">-- Pilih Jenis Bantuan --</option>
                        <option value="Sembako">Paket Sembako</option>
                        <option value="Dana Tunai">Dana Tunai</option>
                        <option value="Material">Material Bangunan</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Detail Bantuan</label>
                    <textarea id="detail_bantuan" name="detail_bantuan" rows="4" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none bg-slate-50 focus:bg-white transition" placeholder="Minimal 20 Karakter..." required></textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Unggah Dokumen Pendukung (PDF/JPG/PNG)</label>
                    <input type="file" name="dokumen_donatur" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none bg-slate-50 focus:bg-white transition cursor-pointer" required>
                    <p class="text-xs text-slate-500 mt-1">*Maksimal ukuran file menyesuaikan server (umumnya 2MB).</p>
                </div>

                <div class="pt-4">
                    <button type="submit" name="submit_donatur" class="w-full bg-teal-500 hover:bg-teal-600 text-white font-bold py-4 rounded-lg shadow-lg hover:shadow-teal-500/30 transform transition duration-300">Kirim Penawaran Donasi</button>
                </div>
            </form>

        <?php elseif ($_SESSION['role'] === 'desa'): ?>
            <form action="" method="POST" enctype="multipart/form-data" class="space-y-6" onsubmit="return validasiForm(event)">
                
                <div class="bg-amber-50/50 p-4 rounded-xl border border-amber-200 mb-6">
                    <h4 class="font-bold text-amber-900 mb-4 border-b border-amber-200 pb-2">Informasi Penanggung Jawab</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Penanggung Jawab</label>
                            <input type="text" name="nama_pj" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none bg-white transition" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Jabatan</label>
                            <input type="text" name="jabatan" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none bg-white transition" required>
                        </div>
                    </div>
                </div>

                <div class="bg-amber-50/50 p-4 rounded-xl border border-amber-200 mb-6">
                    <h4 class="font-bold text-amber-900 mb-4 border-b border-amber-200 pb-2">Informasi Wilayah Desa</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Provinsi</label>
                            <input type="text" name="provinsi" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none bg-white transition" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Kota / Kabupaten</label>
                            <input type="text" name="kota" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none bg-white transition" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Kecamatan</label>
                            <input type="text" name="kecamatan" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none bg-white transition" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Desa / Kelurahan</label>
                            <input type="text" name="desa" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none bg-white transition" required>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Target Bantuan</label>
                        <select name="target_bantuan" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none bg-slate-50 focus:bg-white transition" required>
                            <option value="">-- Pilih Target --</option>
                            <option value="warga">Warga Terdampak</option>
                            <option value="fasilitas">Fasilitas Umum</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Jumlah KK</label>
                        <input type="number" id="jumlah_kk" name="jumlah_kk" placeholder="Kosongkan jika fasilitas" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none bg-slate-50 focus:bg-white transition">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Alasan Permintaan</label>
                    <textarea id="alasan" name="alasan" rows="5" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none bg-slate-50 focus:bg-white transition" placeholder="Minimal 20 Karakter..." required></textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Unggah Dokumen (Foto Kondisi/Surat Pengajuan)</label>
                    <input type="file" name="dokumen_desa" accept=".pdf,.jpg,.jpeg,.png" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none bg-slate-50 focus:bg-white transition cursor-pointer" required>
                    <p class="text-xs text-slate-500 mt-1">*Unggah file PDF, JPG, atau PNG.</p>
                </div>
                
                <div class="pt-4">
                    <button type="submit" name="submit_desa" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-4 rounded-lg shadow-lg hover:shadow-amber-500/30 transform transition duration-300">Ajukan Permintaan Bantuan</button>
                </div>
            </form>
        <?php endif; ?>

      </div>
    </div>
  </div>
</main>

<script>
function validasiForm(event) {
    const role = "<?= $_SESSION['role'] ?>";

    // Validasi khusus form Desa
    if (role === 'desa') {
        const inputAlasan = document.getElementById('alasan');
        const inputJumlahKk = document.getElementById('jumlah_kk');
        
        // Cek Panjang Alasan
        if (inputAlasan.value.trim().length < 20) {
            alert("⚠️ Validasi Gagal:\nPenjelasan kondisi/alasan desa harus diisi minimal 20 karakter agar jelas.");
            inputAlasan.focus();
            event.preventDefault(); // Cegah form terkirim
            return false;
        }

        // Cek Jumlah KK (Tidak boleh minus atau 0 jika diisi)
        if (inputJumlahKk.value !== "" && parseInt(inputJumlahKk.value) <= 0) {
            alert("⚠️ Validasi Gagal:\nJumlah KK terdampak harus lebih dari 0.");
            inputJumlahKk.focus();
            event.preventDefault(); 
            return false;
        }
    } 
    
    // Validasi khusus form Donatur
    else if (role === 'donatur') {
        const inputDetail = document.getElementById('detail_bantuan');

        // Cek Panjang Detail Bantuan
        if (inputDetail.value.trim().length < 20) {
            alert("⚠️ Validasi Gagal:\nDetail bantuan harus diisi minimal 20 karakter agar spesifik.");
            inputDetail.focus();
            event.preventDefault(); // Cegah form terkirim
            return false;
        }
    }

    return true; 
}
</script>

<?php include 'components/footer.php'; ?>