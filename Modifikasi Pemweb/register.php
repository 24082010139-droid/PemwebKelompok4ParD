<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. Cek apakah file benar-benar ada
if (!file_exists('koneksi.php')) {
    die("Error: File koneksi.php tidak ditemukan di folder ini!");
}

require 'koneksi.php';

// 2. Cek apakah variabel $conn sudah ada setelah di-require
if (!isset($conn)) {
    die("Error: File koneksi.php berhasil dimuat, tapi variabel \$conn tidak ditemukan di dalamnya. Periksa penulisan di koneksi.php!");
}

$error = '';
$success = '';

if (isset($_POST['register'])) {
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $konfirmasi = $_POST['konfirmasi'];
    $role = $_POST['role'];

    // Tangkap data tambahan (Bisa null jika tidak diisi)
    $asal_desa = !empty($_POST['asal_desa']) ? "'" . mysqli_real_escape_string($conn, $_POST['asal_desa']) . "'" : "NULL";
    $nama_organisasi = !empty($_POST['nama_organisasi']) ? "'" . mysqli_real_escape_string($conn, $_POST['nama_organisasi']) . "'" : "NULL";

    // Validasi Password
    if ($password !== $konfirmasi) {
        $error = 'Konfirmasi password tidak cocok!';
    } else {
        // Cek apakah email sudah terdaftar
        $cek_email = mysqli_query($conn, "SELECT email FROM users WHERE email = '$email'");
        if (mysqli_num_rows($cek_email) > 0) {
            $error = 'Email sudah terdaftar. Silakan gunakan email lain.';
        } else {
            // Enkripsi Password
            $password_hashed = password_hash($password, PASSWORD_DEFAULT);

            // Insert ke Database (Menyertakan asal_desa dan nama_organisasi)
            $query = "INSERT INTO users (nama_lengkap, email, password, role, asal_desa, nama_organisasi) 
                      VALUES ('$nama_lengkap', '$email', '$password_hashed', '$role', $asal_desa, $nama_organisasi)";
            
            if (mysqli_query($conn, $query)) {
                $success = 'Pendaftaran berhasil! Silakan Login.';
            } else {
                $error = 'Terjadi kesalahan: ' . mysqli_error($conn);
            }
        }
    }
}

// PENANDA HALAMAN AUTENTIKASI
$is_auth_page = true; 
?>

<?php include 'components/header.php'; ?>

<main class="relative py-10 bg-slate-50 min-h-screen flex items-center justify-center overflow-hidden">
  
  <div class="absolute inset-0 z-0 pointer-events-none">
    <div class="absolute top-[-10%] right-[-5%] w-[40rem] h-[40rem] bg-teal-200/50 rounded-full mix-blend-multiply filter blur-[100px] opacity-70"></div>
    <div class="absolute bottom-[-10%] left-[-10%] w-[35rem] h-[35rem] bg-amber-200/50 rounded-full mix-blend-multiply filter blur-[100px] opacity-70"></div>
    <div class="absolute top-[20%] left-[20%] w-[25rem] h-[25rem] bg-emerald-200/40 rounded-full mix-blend-multiply filter blur-[80px] opacity-60"></div>
  </div>
  
  <div class="container mx-auto px-4 relative z-10">
    <div class="max-w-4xl mx-auto bg-white/90 backdrop-blur-sm rounded-2xl shadow-2xl overflow-hidden flex flex-col md:flex-row border border-white/50">
      
      <div class="w-full md:w-5/12 bg-teal-500 text-white p-10 flex flex-col justify-center relative overflow-hidden">
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-48 h-48 rounded-full bg-teal-400 opacity-50"></div>
        <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-32 h-32 rounded-full bg-teal-600 opacity-50"></div>
        
        <div class="relative z-10">
          <h2 class="text-3xl font-bold mb-4">Bergabunglah Bersama SI BanTal</h2>
          <p class="text-teal-100 text-sm leading-relaxed mb-8">
            Jadilah bagian dari ekosistem bantuan sosial yang transparan, efisien, dan tepat sasaran. Setiap langkah Anda membawa perubahan besar bagi desa.
          </p>
          <div class="bg-teal-600/50 rounded-lg p-4 backdrop-blur-sm border border-teal-400/50">
            <p class="text-xs font-semibold">"Sinergi untuk kesejahteraan bersama."</p>
          </div>
        </div>
      </div>

      <div class="w-full md:w-7/12 p-8 md:p-12">
        <div class="mb-8 text-center md:text-left">
          <h3 class="text-2xl font-bold text-slate-900">Buat Akun Baru</h3>
          <p class="text-slate-500 text-sm mt-1">Lengkapi data diri Anda di bawah ini.</p>
        </div>

        <?php if($error): ?>
          <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-md">
            <p class="text-sm text-red-700 font-medium"><?= $error ?></p>
          </div>
        <?php endif; ?>
        <?php if($success): ?>
          <div class="mb-4 bg-green-50 border-l-4 border-green-500 p-4 rounded-r-md">
            <p class="text-sm text-green-700 font-medium"><?= $success ?> <a href="login.php" class="underline font-bold">Login di sini</a>.</p>
          </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-4">
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Lengkap (PJ)</label>
            <input type="text" name="nama_lengkap" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition bg-slate-50 focus:bg-white" placeholder="Masukkan nama lengkap">
          </div>
          
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Alamat Email</label>
            <input type="email" name="email" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition bg-slate-50 focus:bg-white" placeholder="email@contoh.com">
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1">Password</label>
              <input type="password" name="password" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none transition bg-slate-50 focus:bg-white" placeholder="••••••••">
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1">Konfirmasi Password</label>
              <input type="password" name="konfirmasi" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none transition bg-slate-50 focus:bg-white" placeholder="••••••••">
            </div>
          </div>

          <div class="pt-2">
            <label class="block text-sm font-semibold text-slate-700 mb-3">Daftar Sebagai (Role) *</label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              
              <label class="relative cursor-pointer group">
                <input type="radio" name="role" value="desa" class="peer sr-only" required onchange="toggleExtraFields()">
                <div class="h-full rounded-xl border-2 border-slate-200 p-4 text-center hover:bg-slate-50 peer-checked:border-amber-500 peer-checked:bg-amber-50 transition duration-200">
                  <div class="w-10 h-10 mx-auto bg-amber-100 text-amber-500 rounded-full flex items-center justify-center mb-2 peer-checked:bg-amber-500 peer-checked:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                  </div>
                  <span class="block font-bold text-slate-800">Pemohon Bantuan</span>
                  <span class="block text-xs text-slate-500 mt-1">Perangkat Desa</span>
                </div>
              </label>

              <label class="relative cursor-pointer group">
                <input type="radio" name="role" value="donatur" class="peer sr-only" required onchange="toggleExtraFields()">
                <div class="h-full rounded-xl border-2 border-slate-200 p-4 text-center hover:bg-slate-50 peer-checked:border-teal-500 peer-checked:bg-teal-50 transition duration-200">
                  <div class="w-10 h-10 mx-auto bg-teal-100 text-teal-500 rounded-full flex items-center justify-center mb-2 peer-checked:bg-teal-500 peer-checked:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                  </div>
                  <span class="block font-bold text-slate-800">Donatur</span>
                  <span class="block text-xs text-slate-500 mt-1">Instansi Pemerintah / Swasta</span>
                </div>
              </label>

            </div>
          </div>

          <div id="field_desa" class="hidden opacity-0 transition-opacity duration-500 mt-4">
            <label class="block text-sm font-semibold text-amber-700 mb-2">Asal Desa</label>
            <input type="text" name="asal_desa" id="input_desa" class="w-full px-4 py-3 border border-amber-300 rounded-xl focus:ring-2 focus:ring-amber-500 outline-none transition bg-amber-50 focus:bg-white" placeholder="Contoh: Desa Gununganyar">
            <p class="text-xs text-slate-400 mt-1">Sebutkan nama desa yang Anda wakili.</p>
          </div>

          <div id="field_organisasi" class="hidden opacity-0 transition-opacity duration-500 mt-4">
            <label class="block text-sm font-semibold text-teal-700 mb-2">Nama Instansi / Organisasi</label>
            <input type="text" name="nama_organisasi" id="input_organisasi" class="w-full px-4 py-3 border border-teal-300 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none transition bg-teal-50 focus:bg-white" placeholder="Contoh: PT. Samudera Indonesia / Pribadi">
            <p class="text-xs text-slate-400 mt-1">Sebutkan nama lembaga atau isi "Pribadi" jika Anda perorangan.</p>
          </div>

          <button type="submit" name="register" class="w-full bg-slate-900 text-white font-bold py-4 rounded-lg mt-8 hover:bg-teal-500 hover:shadow-lg transform transition duration-300">
            Daftar Sekarang
          </button>
        </form>

        <p class="mt-8 text-center text-sm text-slate-500 border-t border-slate-100 pt-6">
          Sudah punya akun? <a href="login.php" class="text-teal-600 font-bold hover:underline">Masuk di sini</a>
        </p>
      </div>
    </div>
  </div>
</main>

<script>
// Logika JavaScript untuk memunculkan kolom khusus berdasarkan pilihan Role
function toggleExtraFields() {
    // Cari radio button yang sedang di-check
    const selectedRole = document.querySelector('input[name="role"]:checked');
    if (!selectedRole) return;
    
    const role = selectedRole.value;
    const fieldDesa = document.getElementById('field_desa');
    const inputDesa = document.getElementById('input_desa');
    const fieldOrganisasi = document.getElementById('field_organisasi');
    const inputOrganisasi = document.getElementById('input_organisasi');

    // Reset semua tampilan dan status required
    fieldDesa.classList.add('hidden');
    fieldDesa.classList.remove('opacity-100');
    inputDesa.required = false;

    fieldOrganisasi.classList.add('hidden');
    fieldOrganisasi.classList.remove('opacity-100');
    inputOrganisasi.required = false;

    // Munculkan sesuai pilihan
    if (role === 'desa') {
        fieldDesa.classList.remove('hidden');
        // Tambahkan delay kecil agar efek transisi opacity terlihat
        setTimeout(() => fieldDesa.classList.add('opacity-100'), 50);
        inputDesa.required = true;
    } else if (role === 'donatur') {
        fieldOrganisasi.classList.remove('hidden');
        setTimeout(() => fieldOrganisasi.classList.add('opacity-100'), 50);
        inputOrganisasi.required = true;
    }
}
</script>

<?php include 'components/footer.php'; ?>