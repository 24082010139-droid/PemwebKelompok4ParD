<?php
require_once 'auth_check.php';
// Halaman ini bebas diakses oleh user yang sudah login (Desa/Donatur/Admin)
wajib_login(); 
require_once 'koneksi.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$tipe = isset($_GET['tipe']) ? $_GET['tipe'] : '';

if ($id === 0 || $tipe === '') {
    die("<div class='p-10 text-center text-xl text-slate-500'>Data tidak ditemukan atau URL tidak valid. <a href='index.php' class='text-teal-500 underline'>Kembali</a></div>");
}

$data = null;

// Ambil data berdasarkan tipe yang diklik
if ($tipe === 'permintaan') {
    $query = "SELECT * FROM permintaan_bantuan WHERE id = $id";
    $result = mysqli_query($conn, $query);
    if($result) $data = mysqli_fetch_assoc($result);
} elseif ($tipe === 'penawaran') {
    $query = "SELECT * FROM penawaran_bantuan WHERE id = $id";
    $result = mysqli_query($conn, $query);
    if($result) $data = mysqli_fetch_assoc($result);
}

if (!$data) {
    die("<div class='p-10 text-center text-xl text-slate-500'>Data tidak ditemukan di database.</div>");
}

// Fungsi pembantu untuk mewarnai status
function getStatusBadge($status) {
    if($status == 'pending') return '<span class="bg-slate-200 text-slate-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">Menunggu Admin</span>';
    if($status == 'approved') return '<span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">Disetujui</span>';
    if($status == 'rejected') return '<span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">Ditolak</span>';
    return '-';
}
?>

<?php include 'components/header.php'; ?>

<main class="pt-36 pb-20 min-h-screen bg-slate-50">
    <div class="container mx-auto px-4">
        
        <a href="javascript:history.back()" class="inline-flex items-center text-slate-500 hover:text-teal-600 font-bold mb-6 transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>

        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden relative">
            
            <?php if ($tipe === 'permintaan'): ?>
                <div class="h-4 w-full bg-amber-500"></div>
                <div class="p-8 md:p-10 border-b border-slate-100">
                    <div class="flex justify-between items-start flex-wrap gap-4 mb-4">
                        <span class="bg-amber-100 text-amber-700 text-xs font-bold px-4 py-1.5 rounded-full uppercase tracking-widest shadow-sm">Kebutuhan Desa</span>
                        <?= getStatusBadge($data['status']) ?>
                    </div>
                    <h1 class="text-4xl font-extrabold text-slate-900 mb-2">Desa <?= htmlspecialchars($data['desa']) ?></h1>
                    <p class="text-slate-500 text-lg flex items-center">
                        <svg class="w-5 h-5 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Kec. <?= htmlspecialchars($data['kecamatan']) ?>, <?= htmlspecialchars($data['kota']) ?>, <?= htmlspecialchars($data['provinsi']) ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="h-4 w-full bg-teal-500"></div>
                <div class="p-8 md:p-10 border-b border-slate-100">
                    <div class="flex justify-between items-start flex-wrap gap-4 mb-4">
                        <span class="bg-teal-100 text-teal-700 text-xs font-bold px-4 py-1.5 rounded-full uppercase tracking-widest shadow-sm">Penawaran Donatur</span>
                        <?= getStatusBadge($data['status']) ?>
                    </div>
                    <h1 class="text-4xl font-extrabold text-slate-900 mb-2"><?= htmlspecialchars($data['nama_instansi']) ?></h1>
                    <p class="text-slate-500 text-lg font-medium">Bantuan: <span class="text-teal-600 font-bold"><?= htmlspecialchars($data['jenis_penawaran']) ?></span></p>
                </div>
            <?php endif; ?>

            <div class="p-8 md:p-10 bg-slate-50/50">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                    
                    <div class="md:col-span-2 space-y-8">
                        <div>
                            <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-3">Deskripsi Lengkap</h3>
                            <p class="text-slate-700 leading-relaxed text-lg whitespace-pre-wrap"><?= htmlspecialchars($data['alasan'] ?? $data['detail_bantuan']) ?></p>
                        </div>

                        <?php if($tipe === 'permintaan' && $data['jumlah_kk']): ?>
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-center">
                            <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center text-amber-500 mr-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                            <div>
                                <p class="text-sm text-amber-800 font-bold">Dibutuhkan Untuk</p>
                                <p class="text-xl font-extrabold text-amber-600"><?= $data['jumlah_kk'] ?> Kepala Keluarga (KK)</p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="space-y-6">
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                            <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4">Informasi Kontak</h3>
                            
                            <div class="mb-4">
                                <p class="text-xs text-slate-500 mb-1">Penanggung Jawab (PJ)</p>
                                <p class="font-bold text-slate-900"><?= htmlspecialchars($data['nama_pj'] ?? $data['pj_donatur']) ?></p>
                                <p class="text-sm text-slate-600"><?= htmlspecialchars($data['jabatan'] ?? $data['jabatan_donatur']) ?></p>
                            </div>

                            <?php if(isset($data['kontak_donatur'])): ?>
                            <div class="mb-4">
                                <p class="text-xs text-slate-500 mb-1">Kontak Instansi</p>
                                <p class="font-bold text-slate-900"><?= htmlspecialchars($data['kontak_donatur']) ?></p>
                            </div>
                            <?php endif; ?>

                            <div class="pt-4 border-t border-slate-100">
                                <p class="text-xs text-slate-500 mb-1">Status Pendanaan</p>
                                <?php if($data['is_funded'] == 1): ?>
                                    <span class="inline-block bg-teal-100 text-teal-700 px-3 py-1 rounded-full text-xs font-bold">✅ Sudah Diambil / Didanai</span>
                                <?php else: ?>
                                    <span class="inline-block bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-xs font-bold">⏳ Masih Tersedia</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php 
                        $dokumen = $data['dokumen_desa'] ?? $data['dokumen_donatur'];
                        if (!empty($dokumen)): 
                        ?>
                        <a href="uploads/<?= $dokumen ?>" target="_blank" class="flex items-center justify-center w-full p-4 border-2 border-dashed border-slate-300 rounded-xl hover:bg-slate-100 hover:border-slate-400 transition text-slate-600 font-bold group">
                            <svg class="w-6 h-6 mr-2 text-slate-400 group-hover:text-slate-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Lihat Dokumen
                        </a>
                        <?php endif; ?>

                    </div>

                </div>
            </div>
        </div>

    </div>
</main>

<?php include 'components/footer.php'; ?>