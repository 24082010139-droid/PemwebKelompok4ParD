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

            // Insert ke Database
            $query = "INSERT INTO users (nama_lengkap, email, password, role) 
                      VALUES ('$nama_lengkap', '$email', '$password_hashed', '$role')";
            
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
            <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Lengkap / Instansi</label>
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

          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Daftar Sebagai (Role)</label>
            <select name="role" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none transition bg-slate-50 focus:bg-white cursor-pointer">
              <option value="">-- Pilih Peran Anda --</option>
              <option value="desa">Perwakilan Perangkat Desa (Pemohon)</option>
              <option value="donatur">Lembaga Swasta/Negeri (Donatur)</option>
            </select>
          </div>

          <button type="submit" name="register" class="w-full bg-slate-900 text-white font-bold py-3 rounded-lg mt-6 hover:bg-teal-500 hover:shadow-lg transform transition duration-300">
            Daftar Sekarang
          </button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-500">
          Sudah punya akun? <a href="login.php" class="text-teal-600 font-bold hover:underline">Masuk di sini</a>
        </p>
      </div>
    </div>
  </div>
</main>

<?php include 'components/footer.php'; ?>