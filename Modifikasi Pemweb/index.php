<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Menyesuaikan Tombol Utama berdasarkan Role
$btn_link = 'login.php';
$btn_text = 'Mulai Sekarang';

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'desa') {
        $btn_link = 'contact.php';
        $btn_text = 'Mulai Pengajuan Bantuan';
    } elseif ($_SESSION['role'] === 'donatur') {
        $btn_link = 'contact.php';
        $btn_text = 'Tawarkan Bantuan';
    } elseif ($_SESSION['role'] === 'admin') {
        $btn_link = 'dashboard-admin.php';
        $btn_text = 'Buka Panel Admin';
    }
}
?>

<?php include 'components/header.php'; ?>

    <section id="home" class="pt-36 pb-20 bg-slate-50">
      <div class="container mx-auto px-4">
        <div class="flex flex-wrap items-center">
          <div class="w-full self-center px-4 lg:w-1/2">
            <h1 class="text-base font-semibold text-teal-500 md:text-xl">
              Selamat Datang di
              <span class="block font-bold text-slate-900 text-4xl mt-1 lg:text-5xl">Dashboard SI BanTal</span>
            </h1>
            <h2 class="font-medium text-slate-500 text-lg mb-5 mt-2 lg:text-2xl">
              Sistem Informasi Bantuan Sosial
            </h2>
            <p class="font-medium text-slate-400 mb-10 leading-relaxed max-w-md">
              Kelola dan pantau program bantuan sosial dengan mudah, transparan,
              dan tepat sasaran melalui layanan digital terpadu.
            </p>
            
            <a href="<?= $btn_link ?>" class="text-base font-semibold text-white bg-teal-500 py-3 px-8 rounded-full hover:shadow-lg hover:bg-teal-600 transition duration-300 ease-in-out inline-block">
              <?= $btn_text ?>
            </a>
            
          </div>
          <div class="w-full self-end px-4 lg:w-1/2 mt-10 lg:mt-0">
            <div class="relative right-0">
              <img src="bansos.jpg" alt="Bansos" class="max-w-full mx-auto rounded-xl shadow-xl hover:scale-105 transition duration-500 ease-in-out" />
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="statistik" class="pt-16 pb-16 bg-white">
      <div class="container mx-auto px-4">
        <div class="w-full px-4 mb-10 text-center">
          <h2 class="font-bold text-teal-500 text-2xl mb-4 sm:text-3xl lg:text-4xl">Ringkasan Penyaluran</h2>
          <p class="font-medium text-md text-slate-500">Data terkini realisasi program bantuan sosial SI BanTal.</p>
        </div>
        <div class="flex flex-wrap justify-center gap-6">
          <div class="w-full sm:w-1/2 lg:w-1/4 bg-slate-50 rounded-xl shadow-md p-6 text-center border-t-4 border-teal-500 hover:shadow-lg transition duration-300">
            <h3 class="font-bold text-3xl text-slate-900 mb-2">1,250</h3>
            <p class="text-slate-500 font-medium">Keluarga Penerima</p>
          </div>
          <div class="w-full sm:w-1/2 lg:w-1/4 bg-slate-50 rounded-xl shadow-md p-6 text-center border-t-4 border-teal-500 hover:shadow-lg transition duration-300">
            <h3 class="font-bold text-3xl text-slate-900 mb-2">3</h3>
            <p class="text-slate-500 font-medium">Program Aktif</p>
          </div>
          <div class="w-full sm:w-1/2 lg:w-1/4 bg-slate-50 rounded-xl shadow-md p-6 text-center border-t-4 border-teal-500 hover:shadow-lg transition duration-300">
            <h3 class="font-bold text-3xl text-slate-900 mb-2">15</h3>
            <p class="text-slate-500 font-medium">Desa Terjangkau</p>
          </div>
        </div>
      </div>
    </section>

<?php include 'components/footer.php'; ?>