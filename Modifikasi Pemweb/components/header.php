<?php
// Menentukan link dashboard dan status login berdasarkan session
$link_dashboard = 'index.php'; // Default jika belum login
$is_logged_in = isset($_SESSION['role']); // Cek apakah user sudah login

if ($is_logged_in) {
    if ($_SESSION['role'] === 'desa') {
        $link_dashboard = 'dashboard-desa.php';
    } elseif ($_SESSION['role'] === 'donatur') {
        $link_dashboard = 'dashboard-donatur.php';
    } elseif ($_SESSION['role'] === 'admin') {
        $link_dashboard = 'dashboard-admin.php';
    }
}

// ========================================================
// LOGIKA ACTIVE NAVBAR DINAMIS
// ========================================================
$current_page = basename($_SERVER['PHP_SELF']); // Ambil nama file saat ini (misal: contact.php)

$class_active = "font-bold text-teal-500";
$class_inactive = "font-medium text-slate-800";

// Cek masing-masing menu. (Jika index.php, maka tidak ada satupun yang true, jadi tidak ada yang menyala)
$nav_dashboard = in_array($current_page, ['dashboard-admin.php', 'dashboard-desa.php', 'dashboard-donatur.php']) ? $class_active : $class_inactive;
$nav_about = ($current_page == 'about.php') ? $class_active : $class_inactive;
$nav_portofolio = ($current_page == 'portofolio.php') ? $class_active : $class_inactive;
$nav_contact = ($current_page == 'contact.php') ? $class_active : $class_inactive;
?>
<!doctype html>
<html lang="id" class="scroll-smooth">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Dashboard Sistem Informasi Bantuan Sosial Desa" />
    <title>SI BanTal</title>
    
    <link rel="stylesheet" href="../dist/output.css" />

    <style type="text/tailwindcss">
      .form-input {
        @apply w-full bg-white text-slate-800 border-2 border-slate-300 rounded-lg p-3 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition duration-300;
      }
      .form-label {
        @apply block text-sm font-semibold text-slate-700 mb-2;
      }
      /* Tambahan agar saat di-scroll navbar punya background */
      .navbar-fixed {
        @apply fixed z-[9999] bg-white/80 backdrop-blur-sm shadow-md transition duration-300;
      }
    </style>
  </head>
  
  <body class="font-sans text-slate-800 bg-slate-50 flex flex-col min-h-screen">
    
    <?php if (!isset($is_auth_page) || !$is_auth_page): ?>
    
    <header id="header" class="bg-transparent absolute top-0 left-0 w-full flex items-center z-10 transition duration-300">
      <div class="container mx-auto">
        <div class="flex items-center justify-between relative px-4">
          <div class="px-4">
            <a href="index.php" class="font-bold text-xl text-teal-500 block py-6 tracking-wide">SI BanTal</a>
          </div>
          <div class="flex items-center px-4">
            <button id="hamburger" name="hamburger" type="button" class="block absolute right-4 lg:hidden">
              <span class="hamburger-line transition duration-300 ease-in-out origin-top-left"></span>
              <span class="hamburger-line transition duration-300 ease-in-out"></span>
              <span class="hamburger-line transition duration-300 ease-in-out origin-bottom-left"></span>
            </button>
            <nav id="nav-menu" class="hidden absolute py-5 bg-white shadow-lg rounded-lg max-w-[250px] w-full right-4 top-full lg:block lg:static lg:bg-transparent lg:max-w-full lg:shadow-none lg:rounded-none">
              
              <ul class="block lg:flex lg:items-center">
                <li class="group"><a href="<?= $link_dashboard ?>" class="text-base py-2 mx-8 flex group-hover:text-teal-500 transition <?= $nav_dashboard ?>">Dashboard</a></li>
                <li class="group"><a href="about.php" class="text-base py-2 mx-8 flex group-hover:text-teal-500 transition <?= $nav_about ?>">Tentang Sistem</a></li>
                <li class="group"><a href="portofolio.php" class="text-base py-2 mx-8 flex group-hover:text-teal-500 transition <?= $nav_portofolio ?>">Program Bantuan</a></li>
                <li class="group"><a href="contact.php" class="text-base py-2 mx-8 flex group-hover:text-teal-500 transition <?= $nav_contact ?>">Pengajuan Bantuan</a></li>
                
                <li class="group my-4 lg:my-0 px-8 lg:px-0 lg:ml-2">
                    <?php if($is_logged_in): ?>
                        <a href="logout.php" class="text-base font-bold text-white bg-red-500 py-2.5 px-6 rounded-full hover:shadow-lg hover:shadow-red-500/30 hover:bg-red-600 transition duration-300 ease-in-out block text-center lg:inline-block">
                            Logout
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="text-base font-bold text-white bg-teal-500 py-2.5 px-6 rounded-full hover:shadow-lg hover:shadow-teal-500/30 hover:bg-teal-600 transition duration-300 ease-in-out block text-center lg:inline-block">
                            Login
                        </a>
                    <?php endif; ?>
                </li>
                
              </ul>

            </nav>
          </div>
        </div>
      </div>
    </header>
    
    <?php endif; ?>