<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'koneksi.php';

// Cek role (Bisa guest/belum login)
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';
$user_id_sekarang = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$tipe = isset($_GET['tipe']) ? $_GET['tipe'] : '';

if ($id === 0 || $tipe === '') {
    die("<div class='p-10 text-center text-xl text-slate-500'>Data tidak ditemukan atau URL tidak valid. <a href='index.php' class='text-teal-500 underline'>Kembali</a></div>");
}

$pesan_sukses = '';
$pesan_error = '';

// ========================================================
// LOGIKA AKSI: MENGAMBIL / MENDANAI PROGRAM
// ========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ambil_program'])) {
    if ($role === 'guest') {
        header("Location: login.php");
        exit;
    }

    $program_id = intval($_POST['program_id']);
    $tipe_program = mysqli_real_escape_string($conn, $_POST['tipe_program']);

    // Cek apakah sudah pernah diambil sebelumnya (mencegah double click)
    $cek_funded = mysqli_query($conn, "SELECT is_funded FROM " . ($tipe_program === 'permintaan' ? 'permintaan_bantuan' : 'penawaran_bantuan') . " WHERE id = $program_id");
    $status_funded = mysqli_fetch_assoc($cek_funded)['is_funded'];

    if ($status_funded == 1) {
        $pesan_error = "Mohon maaf, program ini baru saja diambil/didanai oleh pihak lain.";
    } else {
        // 1. Catat ke history_penyaluran
        $query_history = "INSERT INTO history_penyaluran (user_id, program_id, tipe_program) VALUES ('$user_id_sekarang', '$program_id', '$tipe_program')";
        
        // 2. Update is_funded menjadi 1
        $tabel_update = ($tipe_program === 'permintaan') ? 'permintaan_bantuan' : 'penawaran_bantuan';
        $query_update = "UPDATE $tabel_update SET is_funded = 1 WHERE id = '$program_id'";

        if (mysqli_query($conn, $query_history) && mysqli_query($conn, $query_update)) {
            $pesan_sukses = ($tipe_program === 'permintaan') ? "Terima kasih! Anda telah berhasil mendanai program desa ini." : "Berhasil! Anda telah mengklaim penawaran donatur ini untuk desa Anda.";
        } else {
            $pesan_error = "Terjadi kesalahan sistem: " . mysqli_error($conn);
        }
    }
}

// ========================================================
// AMBIL DATA DETAIL + DATA AKUN PEMBUAT (JOIN)
// ========================================================
$data = null;

if ($tipe === 'permintaan') {
    // Join dengan tabel users untuk mengambil asal_desa
    $query = "SELECT pb.*, u.nama_lengkap as pembuat_akun, u.asal_desa as akun_asal, u.email as email_pembuat 
              FROM permintaan_bantuan pb 
              JOIN users u ON pb.user_id = u.id 
              WHERE pb.id = $id";
    $result = mysqli_query($conn, $query);
    if($result) $data = mysqli_fetch_assoc($result);
} elseif ($tipe === 'penawaran') {
    // Join dengan tabel users untuk mengambil nama_organisasi
    $query = "SELECT pb.*, u.nama_lengkap as pembuat_akun, u.nama_organisasi as akun_asal, u.email as email_pembuat 
              FROM penawaran_bantuan pb 
              JOIN users u ON pb.user_id = u.id 
              WHERE pb.id = $id";
    $result = mysqli_query($conn, $query);
    if($result) $data = mysqli_fetch_assoc($result);
}

if (!$data) {
    die("<div class='p-10 text-center text-xl text-slate-500'>Data tidak ditemukan di database.</div>");
}

function getStatusBadge($status) {
    if($status == 'pending') return '<span class="bg-slate-200 text-slate-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider shadow-sm">Menunggu Admin</span>';
    if($status == 'approved') return '<span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider shadow-sm">Tervalidasi / Aktif</span>';
    if($status == 'rejected') return '<span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider shadow-sm">Ditolak</span>';
    return '-';
}
?>

<?php include 'components/header.php'; ?>

<main class="pt-36 pb-20 min-h-screen bg-transparent">
    <div class="container mx-auto px-4 max-w-5xl relative z-10">
        
        <a href="javascript:history.back()" class="inline-flex items-center text-slate-500 hover:text-teal-600 font-bold mb-6 transition bg-white/50 backdrop-blur-sm px-4 py-2 rounded-full border border-slate-200 shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>

        <?php if($pesan_sukses): ?>
            <div class="mb-8 bg-green-500/10 backdrop-blur-md border-l-4 border-green-500 p-6 rounded-r-xl shadow-lg flex items-start">
                <div class="text-green-500 mr-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-green-800 mb-1">Transaksi Berhasil!</h3>
                    <p class="text-green-700"><?= $pesan_sukses ?> Silakan cek menu Dashboard Anda untuk melihat riwayatnya.</p>
                </div>
            </div>
        <?php endif; ?>

        <?php if($pesan_error): ?>
            <div class="mb-8 bg-red-500/10 backdrop-blur-md border-l-4 border-red-500 p-6 rounded-r-xl shadow-lg flex items-start">
                <div class="text-red-500 mr-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-red-800 mb-1">Gagal!</h3>
                    <p class="text-red-700"><?= $pesan_error ?></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="bg-white/90 backdrop-blur-md rounded-3xl shadow-2xl border border-white overflow-hidden relative">
            
            <?php if ($tipe === 'permintaan'): ?>
                <div class="h-4 w-full bg-amber-500"></div>
                <div class="p-8 md:p-10 border-b border-slate-100">
                    <div class="flex justify-between items-start flex-wrap gap-4 mb-4">
                        <span class="bg-amber-100 text-amber-800 text-xs font-bold px-4 py-1.5 rounded-full uppercase tracking-widest shadow-sm border border-amber-200">Kebutuhan Desa</span>
                        <?= getStatusBadge($data['status']) ?>
                    </div>
                    <h1 class="text-4xl font-extrabold text-slate-900 mb-3"> <?= htmlspecialchars($data['desa']) ?></h1>
                    <p class="text-slate-500 text-lg flex items-center">
                        <svg class="w-5 h-5 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Kec. <?= htmlspecialchars($data['kecamatan']) ?>, <?= htmlspecialchars($data['kota']) ?>, <?= htmlspecialchars($data['provinsi']) ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="h-4 w-full bg-teal-500"></div>
                <div class="p-8 md:p-10 border-b border-slate-100">
                    <div class="flex justify-between items-start flex-wrap gap-4 mb-4">
                        <span class="bg-teal-100 text-teal-800 text-xs font-bold px-4 py-1.5 rounded-full uppercase tracking-widest shadow-sm border border-teal-200">Penawaran Donatur</span>
                        <?= getStatusBadge($data['status']) ?>
                    </div>
                    <h1 class="text-4xl font-extrabold text-slate-900 mb-3"><?= htmlspecialchars($data['nama_instansi']) ?></h1>
                    <p class="text-slate-500 text-lg font-medium">Jenis Bantuan: <span class="text-teal-600 font-bold px-2 py-1 bg-teal-50 rounded border border-teal-100 ml-2"><?= htmlspecialchars($data['jenis_penawaran']) ?></span></p>
                </div>
            <?php endif; ?>

            <div class="p-8 md:p-10 bg-slate-50/50">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                    
                    <div class="lg:col-span-2 space-y-8">
                        
                        <div class="flex items-center p-4 bg-white rounded-2xl border border-slate-200 shadow-sm">
                            
                            <div>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Dipublikasikan Oleh</p>
                                <p class="font-bold text-slate-800 text-lg"><?= htmlspecialchars($data['pembuat_akun']) ?></p>
                                <p class="text-sm text-teal-600 font-semibold">
                                    <?php if ($tipe === 'permintaan'): ?>
                                        Perwakilan dari <?= htmlspecialchars($data['akun_asal'] ?? 'Desa '.$data['desa']) ?>
                                    <?php else: ?>
                                        Mewakili <?= htmlspecialchars($data['akun_asal'] ?? $data['nama_instansi']) ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-3 border-b border-slate-200 pb-2">Rincian Lengkap</h3>
                            <p class="text-slate-700 leading-relaxed text-lg whitespace-pre-wrap"><?= htmlspecialchars($data['alasan'] ?? $data['detail_bantuan']) ?></p>
                        </div>

                        <?php if($tipe === 'permintaan' && $data['jumlah_kk']): ?>
                        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 flex items-center shadow-sm">
                            <div class="w-14 h-14 bg-white rounded-full flex items-center justify-center text-amber-500 mr-5 shadow-sm border border-amber-100">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                            <div>
                                <p class="text-sm text-amber-800 font-bold mb-1">Target Bantuan</p>
                                <p class="text-2xl font-extrabold text-amber-600"><?= $data['jumlah_kk'] ?> <span class="text-lg font-bold text-amber-700/70">Kepala Keluarga (KK)</span></p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="space-y-6">
                        
                        <div class="bg-white p-6 rounded-2xl shadow-xl border border-slate-200 relative overflow-hidden">
                            <?php if ($data['is_funded'] == 1): ?>
                                <div class="absolute top-0 left-0 w-full h-1 bg-slate-300"></div>
                                <div class="text-center py-4">
                                    <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    </div>
                                    <h3 class="font-bold text-slate-800 text-lg mb-1">Tidak Tersedia</h3>
                                    <p class="text-sm text-slate-500 mb-4">Program ini sudah diambil atau didanai.</p>
                                </div>
                            <?php elseif ($data['status'] !== 'approved'): ?>
                                <div class="absolute top-0 left-0 w-full h-1 bg-amber-400"></div>
                                <div class="text-center py-4">
                                    <h3 class="font-bold text-slate-800 text-lg mb-2">Sedang Diproses</h3>
                                    <p class="text-sm text-slate-500">Program ini belum diverifikasi oleh Admin dan belum bisa diambil.</p>
                                </div>
                            <?php else: ?>
                                <div class="absolute top-0 left-0 w-full h-1 <?= ($tipe==='permintaan') ? 'bg-amber-500' : 'bg-teal-500' ?>"></div>
                                <h3 class="font-bold text-slate-800 text-lg mb-4 text-center">Status: <span class="text-green-600">Tersedia ✅</span></h3>
                                
                                <?php if ($role === 'guest'): ?>
                                    <p class="text-sm text-slate-500 text-center mb-4">Anda harus login untuk mengambil atau mendanai program ini.</p>
                                    <a href="login.php" class="block w-full text-center bg-slate-800 hover:bg-slate-900 text-white font-bold py-3 rounded-xl transition shadow-md">Login Sekarang</a>
                                
                                <?php elseif ($role === 'admin'): ?>
                                    <p class="text-sm text-slate-500 text-center bg-slate-50 p-3 rounded-lg border border-slate-100">Tombol aksi disembunyikan. (Hanya untuk Desa dan Donatur).</p>
                                
                                <?php elseif ($tipe === 'permintaan' && $role === 'donatur'): ?>
                                    <p class="text-sm text-slate-600 text-center mb-4">Bantu desa ini dengan menyalurkan dana/bantuan Anda.</p>
                                    <form method="POST" action="" onsubmit="return confirm('Apakah Anda yakin ingin mendanai program ini? Aksi ini akan mengunci program untuk donatur lain.');">
                                        <input type="hidden" name="program_id" value="<?= $id ?>">
                                        <input type="hidden" name="tipe_program" value="permintaan">
                                        <button type="submit" name="ambil_program" class="w-full bg-teal-500 hover:bg-teal-600 text-white font-bold py-3.5 rounded-xl transition shadow-lg shadow-teal-500/30 transform hover:-translate-y-1">
                                            Danai Program Ini
                                        </button>
                                    </form>

                                <?php elseif ($tipe === 'penawaran' && $role === 'desa'): ?>
                                    <p class="text-sm text-slate-600 text-center mb-4">Klaim penawaran donatur ini untuk disalurkan ke desa Anda.</p>
                                    <form method="POST" action="" onsubmit="return confirm('Apakah Anda yakin ingin mengambil penawaran ini untuk desa Anda?');">
                                        <input type="hidden" name="program_id" value="<?= $id ?>">
                                        <input type="hidden" name="tipe_program" value="penawaran">
                                        <button type="submit" name="ambil_program" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-3.5 rounded-xl transition shadow-lg shadow-amber-500/30 transform hover:-translate-y-1">
                                            Klaim Untuk Desa
                                        </button>
                                    </form>

                                <?php else: ?>
                                    <p class="text-sm text-slate-500 text-center bg-slate-50 p-3 rounded-lg border border-slate-100">Program ini diperuntukkan untuk diambil oleh <?= ($role==='desa') ? 'Donatur' : 'Desa' ?>.</p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                            <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4">Penanggung Jawab</h3>
                            
                            <div class="mb-4">
                                <p class="text-xs text-slate-500 mb-1">Nama PIC</p>
                                <p class="font-bold text-slate-900"><?= htmlspecialchars($data['nama_pj'] ?? $data['pj_donatur']) ?></p>
                                <p class="text-sm text-slate-600"><?= htmlspecialchars($data['jabatan'] ?? $data['jabatan_donatur']) ?></p>
                            </div>

                            <?php if(isset($data['kontak_donatur'])): ?>
                            <div class="mb-4">
                                <p class="text-xs text-slate-500 mb-1">Kontak Instansi</p>
                                <p class="font-bold text-teal-600"><?= htmlspecialchars($data['kontak_donatur']) ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php 
                        $dokumen = $data['dokumen_desa'] ?? $data['dokumen_donatur'];
                        if (!empty($dokumen)): 
                        ?>
                        <a href="uploads/<?= $dokumen ?>" target="_blank" class="flex items-center justify-center w-full p-4 bg-white border border-slate-200 shadow-sm rounded-xl hover:bg-slate-50 hover:border-slate-400 transition text-slate-600 font-bold group">
                            <svg class="w-6 h-6 mr-2 text-teal-500 group-hover:scale-110 transition transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Unduh File Lampiran
                        </a>
                        <?php endif; ?>

                    </div>

                </div>
            </div>
        </div>

    </div>
</main>

<?php include 'components/footer.php'; ?>