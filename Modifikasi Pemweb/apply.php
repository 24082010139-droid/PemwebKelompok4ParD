<?php
require_once 'auth_check.php';
wajib_login();
require_once 'koneksi.php';

if (isset($_GET['id']) && isset($_GET['tipe'])) {
    $program_id = mysqli_real_escape_string($conn, $_GET['id']);
    $tipe_program = mysqli_real_escape_string($conn, $_GET['tipe']); 
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];

    // Keamanan ekstra: Pastikan Desa hanya bisa apply Penawaran, dan Donatur hanya mendanai Permintaan
    if (($role === 'desa' && $tipe_program !== 'penawaran') || ($role === 'donatur' && $tipe_program !== 'permintaan')) {
        die("Akses ilegal! Peran Anda tidak sesuai dengan tindakan ini.");
    }

    // 1. Simpan riwayat siapa mengambil program apa
    $query_history = "INSERT INTO history_penyaluran (user_id, program_id, tipe_program) 
                      VALUES ('$user_id', '$program_id', '$tipe_program')";
    
    if (mysqli_query($conn, $query_history)) {
        // 2. Ubah is_funded menjadi 1 agar hilang dari daftar Program Aktif
        $tabel = ($tipe_program === 'permintaan') ? 'permintaan_bantuan' : 'penawaran_bantuan';
        $query_update = "UPDATE $tabel SET is_funded = 1 WHERE id = '$program_id'";
        mysqli_query($conn, $query_update);

        // 3. Tampilkan pesan sukses dan kembalikan ke halaman portofolio
        echo "<script>
                alert('Berhasil! Anda telah terhubung dengan program ini. Pihak Admin SI BanTal akan segera menghubungi Anda untuk tindak lanjut penyaluran.');
                window.location.href = 'portofolio.php';
              </script>";
    } else {
        echo "Gagal memproses pengajuan: " . mysqli_error($conn);
    }
} else {
    header("Location: portofolio.php");
}
?>