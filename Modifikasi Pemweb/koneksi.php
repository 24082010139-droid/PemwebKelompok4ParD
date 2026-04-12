<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "si_bantal";

// Menggunakan @ untuk menyembunyikan warning bawaan dan kita handle sendiri
$conn = @mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi Database Gagal: " . mysqli_connect_error());
}

// ========================================================
// AUTO SEEDER (PEMBUAT AKUN ADMIN OTOMATIS)
// ========================================================
// Fitur ini sangat berguna untuk kerja tim/presentasi dosen.
// Sistem akan mengecek, jika akun admin belum ada, maka otomatis dibuatkan.

$email_admin_default = 'admin@sibantal.com';
$password_admin_default = 'admin123';

// 1. Cek apakah email admin ini sudah ada di tabel users
$cek_admin = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email_admin_default'");

// 2. Jika hasilnya 0 (belum ada), maka jalankan perintah INSERT
if ($cek_admin && mysqli_num_rows($cek_admin) == 0) {
    
    // Enkripsi password-nya
    $password_hashed = password_hash($password_admin_default, PASSWORD_DEFAULT);
    
    // Masukkan ke database
    $query_insert_admin = "INSERT INTO users (nama_lengkap, email, password, role) 
                           VALUES ('Admin SI BanTal', '$email_admin_default', '$password_hashed', 'admin')";
    
    mysqli_query($conn, $query_insert_admin);
}
// ========================================================

// ========================================================
// AUTO CREATE TABLE: HISTORY PENYALURAN (Untuk Fitur Apply/Danai)
// ========================================================
$create_table_history = "CREATE TABLE IF NOT EXISTS history_penyaluran (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    program_id INT(11) NOT NULL,
    tipe_program VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $create_table_history);
?>