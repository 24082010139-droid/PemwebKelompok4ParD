<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require 'koneksi.php';

// Jika sudah login, cek role dan arahkan ke dashboard masing-masing
if(isset($_SESSION['user_id'])){
    if ($_SESSION['role'] === 'admin') {
        header("Location: dashboard-admin.php");
    } else if ($_SESSION['role'] === 'desa') {
        header("Location: dashboard-desa.php");
    } else if ($_SESSION['role'] === 'donatur') {
        header("Location: dashboard-donatur.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

$error = '';

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    
    if (mysqli_num_rows($query) === 1) {
        $row = mysqli_fetch_assoc($query);
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['nama_lengkap'] = $row['nama_lengkap'];
            $_SESSION['role'] = $row['role'];
            
            // SIMPAN DATA DESA / ORGANISASI KE SESSION
            $_SESSION['asal_desa'] = $row['asal_desa'];
            $_SESSION['nama_organisasi'] = $row['nama_organisasi'];
            
            // PENGALIHAN BERDASARKAN ROLE (ROUTING)
            if ($row['role'] === 'admin') {
                header("Location: dashboard-admin.php");
            } else if ($row['role'] === 'desa') {
                header("Location: dashboard-desa.php");
            } else if ($row['role'] === 'donatur') {
                header("Location: dashboard-donatur.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $error = 'Password salah!';
        }
    } else {
        $error = 'Email tidak ditemukan!';
    }
}

// PENANDA HALAMAN AUTENTIKASI
$is_auth_page = true; 
?>

<?php include 'components/header.php'; ?>

<main class="relative py-10 bg-slate-50 min-h-screen flex items-center justify-center overflow-hidden">
  
  <div class="absolute inset-0 z-0 pointer-events-none">
    <div class="absolute top-[-15%] left-[-5%] w-[45rem] h-[45rem] bg-emerald-200/50 rounded-full mix-blend-multiply filter blur-[120px] opacity-70"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[35rem] h-[35rem] bg-sky-200/50 rounded-full mix-blend-multiply filter blur-[100px] opacity-60"></div>
    <div class="absolute top-[40%] right-[30%] w-[25rem] h-[25rem] bg-teal-200/40 rounded-full mix-blend-multiply filter blur-[80px] opacity-50"></div>
  </div>
  
  <div class="container mx-auto px-4 relative z-10">
    <div class="max-w-4xl mx-auto bg-white/90 backdrop-blur-sm rounded-2xl shadow-2xl overflow-hidden flex flex-col md:flex-row-reverse border border-white/50">
      
      <div class="w-full md:w-5/12 bg-slate-900 text-white p-10 flex flex-col justify-center relative overflow-hidden">
        <div class="absolute top-0 left-0 -ml-10 -mt-10 w-40 h-40 border-4 border-teal-500 rounded-full opacity-20"></div>
        <div class="absolute bottom-0 right-0 -mr-16 -mb-16 w-48 h-48 bg-teal-500 rounded-full opacity-30"></div>
        
        <div class="relative z-10 text-center md:text-left">
          <h2 class="text-3xl font-bold mb-4">Selamat Datang Kembali!</h2>
          <p class="text-slate-400 text-sm leading-relaxed mb-8">
            Masuk ke dashboard untuk memantau status pengajuan atau menambahkan program bantuan baru Anda.
          </p>
        </div>
      </div>

      <div class="w-full md:w-7/12 p-8 md:p-14 flex flex-col justify-center">
        <div class="mb-8">
          <h3 class="text-3xl font-extrabold text-slate-900 tracking-tight">Login Akun</h3>
          <p class="text-slate-500 text-sm mt-2">Silakan masukkan email dan password Anda.</p>
        </div>

        <?php if($error): ?>
          <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-md flex items-center">
             <p class="text-sm text-red-700 font-medium"><?= $error ?></p>
          </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-6">
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Alamat Email</label>
            <input type="email" name="email" required class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition bg-slate-50 focus:bg-white" placeholder="email@contoh.com">
          </div>
          
          <div>
            <div class="flex justify-between items-center mb-2">
              <label class="block text-sm font-semibold text-slate-700">Password</label>
              <a href="#" class="text-xs text-teal-600 hover:underline font-medium">Lupa Password?</a>
            </div>
            <input type="password" name="password" required class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition bg-slate-50 focus:bg-white" placeholder="••••••••">
          </div>

          <button type="submit" name="login" class="w-full bg-teal-500 text-white font-bold py-3.5 rounded-xl hover:bg-teal-600 focus:ring-4 focus:ring-teal-200 transform transition duration-300 shadow-lg shadow-teal-500/30">
            Masuk Sistem
          </button>
        </form>

        <p class="mt-8 text-center text-sm text-slate-500">
          Belum bergabung? <a href="register.php" class="text-teal-600 font-bold hover:underline transition">Daftar Akun Baru</a>
        </p>
      </div>
    </div>
  </div>
</main>

<?php include 'components/footer.php'; ?>