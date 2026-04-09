<?php
// auth_check.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Fungsi untuk memproteksi halaman
 * @param string $role_required (admin/desa/donatur)
 */
function wajib_login($role_required = null) {
    // 1. Cek apakah user sudah login (apakah ada session user_id?)
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    // 2. Cek apakah role user sesuai dengan yang diminta halaman tersebut
    if ($role_required !== null && $_SESSION['role'] !== $role_required) {
        // Jika nekat masuk ke dashboard yang bukan haknya, lempar ke index
        header("Location: index.php");
        exit;
    }
}
?>