<?php
require 'auth_check.php';
wajib_login('donatur'); // Proteksi: Hanya role 'donatur'
?>

<?php include 'components/header.php'; ?>

<main class="pt-36 pb-20 min-h-screen">
  <div class="container mx-auto px-4">
    <div class="bg-white rounded-2xl shadow-xl p-8 border-t-4 border-teal-500">
      <div class="flex flex-wrap justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-slate-900">Dashboard Donatur</h1>
          <p class="text-slate-500">Selamat datang, <span class="font-bold text-teal-600"><?= $_SESSION['nama_lengkap'] ?></span></p>
        </div>
        <a href="logout.php" class="px-5 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">Logout</a>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="p-6 bg-teal-50 rounded-xl border border-teal-100">
          <h3 class="font-bold text-teal-800 mb-2">Tawarkan Bantuan</h3>
          <p class="text-sm text-teal-600 mb-4">Beri kontribusi bantuan sosial melalui sistem SI BanTal.</p>
          <a href="contact.php" class="inline-block px-4 py-2 bg-teal-500 text-white rounded-lg text-sm font-semibold">Buat Penawaran</a>
        </div>
        <div class="p-6 bg-slate-50 rounded-xl border border-slate-200">
          <h3 class="font-bold text-slate-800 mb-2">Laporan Distribusi</h3>
          <p class="text-sm text-slate-500 mb-4">Pantau laporan penyaluran bantuan yang Anda berikan.</p>
          <button class="text-slate-400 italic text-sm" disabled>(Fitur Laporan Segera Hadir)</button>
        </div>
      </div>
    </div>
  </div>
</main>

<?php include 'components/footer.php'; ?>