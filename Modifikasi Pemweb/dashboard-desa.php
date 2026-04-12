<?php
require_once 'auth_check.php';
wajib_login('desa');
require_once 'koneksi.php';

$user_id = $_SESSION['user_id'];

// 1. Ambil data program donatur yang di-APPLY oleh desa ini
$query_apply = "SELECT hp.created_at as tgl_apply, pb.* FROM history_penyaluran hp
                JOIN penawaran_bantuan pb ON hp.program_id = pb.id
                WHERE hp.user_id = '$user_id' AND hp.tipe_program = 'penawaran'
                ORDER BY hp.created_at DESC";
$result_apply = mysqli_query($conn, $query_apply);

// 2. Ambil data pengajuan bantuan yang DIBUAT SENDIRI oleh desa ini
$query_my_req = "SELECT * FROM permintaan_bantuan WHERE user_id = '$user_id' ORDER BY created_at DESC";
$result_my_req = mysqli_query($conn, $query_my_req);
?>

<?php include 'components/header.php'; ?>

<main class="pt-36 pb-20 min-h-screen bg-slate-50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex flex-wrap justify-between items-center mb-10 gap-4">
            <div>
                <h1 class="text-4xl font-bold text-slate-900 mb-2">Dashboard Desa</h1>
                <p class="text-slate-500 text-lg">Selamat datang, <span class="font-bold text-amber-600"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Perangkat Desa') ?></span></p>
            </div>
           
        </div>

        <div class="flex flex-col lg:flex-row gap-8 w-full">

            <div class="w-full lg:w-1/3 p-8 bg-white rounded-2xl shadow-lg border-t-4 border-amber-500 h-fit hover:shadow-xl transition duration-300">
                <div class="w-12 h-12 bg-amber-50 rounded-lg flex items-center justify-center mb-6">
                    <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                </div>
                <h3 class="font-bold text-slate-800 text-2xl mb-3">Ajukan Kebutuhan</h3>
                <p class="text-base text-slate-500 mb-8 leading-relaxed">Sampaikan kebutuhan bantuan sosial (Warga/Fasilitas) desa Anda ke dalam sistem SI BanTal agar dapat terhubung dengan donatur.</p>
                <a href="contact.php" class="block w-full text-center px-6 py-3.5 bg-amber-500 text-white rounded-full text-base font-bold hover:bg-amber-600 hover:shadow-lg transition duration-300">
                    Buat Pengajuan Baru
                </a>
            </div>

            <div class="w-full lg:w-2/3 flex flex-col gap-8">
                
                <div class="p-8 bg-white rounded-2xl shadow-lg border-t-4 border-slate-700 hover:shadow-xl transition duration-300">
                    <h3 class="font-bold text-slate-800 text-2xl mb-3">📝 Kebutuhan Desa Saya</h3>
                    <p class="text-base text-slate-500 mb-6">Pantau status validasi form kebutuhan bantuan yang telah Anda ajukan ke Admin.</p>

                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left border-collapse min-w-max">
                            <thead class="bg-slate-100 text-slate-600 text-sm border-b border-slate-200">
                                <tr>
                                    <th class="p-4 font-bold">Tanggal Input</th>
                                    <th class="p-4 font-bold">Target</th>
                                    <th class="p-4 font-bold">Status Admin</th>
                                    <th class="p-4 font-bold">Status Dana</th>
                                    <th class="p-4 font-bold text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm text-slate-700">
                                <?php if($result_my_req && mysqli_num_rows($result_my_req) > 0): ?>
                                    <?php while($row = mysqli_fetch_assoc($result_my_req)): ?>
                                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                                        <td class="p-4"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                                        <td class="p-4 uppercase text-xs font-bold text-slate-500"><?= htmlspecialchars($row['target_bantuan']) ?></td>
                                        <td class="p-4">
                                            <?php 
                                                if($row['status'] == 'pending') echo '<span class="bg-slate-200 text-slate-700 px-3 py-1 rounded-full text-xs font-bold">Menunggu</span>';
                                                elseif($row['status'] == 'approved') echo '<span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold">Disetujui</span>';
                                                elseif($row['status'] == 'rejected') echo '<span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold">Ditolak</span>';
                                            ?>
                                        </td>
                                        <td class="p-4">
                                            <?= ($row['is_funded'] == 1) ? '<span class="text-teal-600 font-bold">✅ Didanai</span>' : '<span class="text-amber-500 font-bold">⏳ Belum Didanai</span>' ?>
                                        </td>
                                        <td class="p-4 text-center">
                                            <a href="detail.php?id=<?= $row['id'] ?>&tipe=permintaan" class="text-teal-600 hover:text-teal-800 font-bold text-sm bg-teal-50 hover:bg-teal-100 px-3 py-1.5 rounded-lg transition">Detail</a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="p-8 text-center text-slate-500">Belum ada form pengajuan yang Anda buat.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="p-8 bg-white rounded-2xl shadow-lg border-t-4 border-amber-500 hover:shadow-xl transition duration-300">
                    <h3 class="font-bold text-slate-800 text-2xl mb-3">🤝 Partisipasi Program Donatur</h3>
                    <p class="text-base text-slate-500 mb-6">Daftar penawaran donatur dari halaman Program Aktif yang telah Anda klaim.</p>

                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left border-collapse min-w-max">
                            <thead class="bg-amber-50 text-amber-800 text-sm border-b border-amber-200">
                                <tr>
                                    <th class="p-4 font-bold">Tgl Ambil</th>
                                    <th class="p-4 font-bold">Instansi Donatur</th>
                                    <th class="p-4 font-bold">Jenis Bantuan</th>
                                    <th class="p-4 font-bold">Keterangan</th>
                                    <th class="p-4 font-bold text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm text-slate-700">
                                <?php if($result_apply && mysqli_num_rows($result_apply) > 0): ?>
                                    <?php while($row = mysqli_fetch_assoc($result_apply)): ?>
                                    <tr class="border-b border-slate-100 hover:bg-amber-50">
                                        <td class="p-4"><?= date('d M Y', strtotime($row['tgl_apply'])) ?></td>
                                        <td class="p-4 font-bold text-slate-900"><?= htmlspecialchars($row['nama_instansi']) ?></td>
                                        <td class="p-4 uppercase text-xs font-bold text-slate-500"><?= htmlspecialchars($row['jenis_penawaran']) ?></td>
                                        <td class="p-4"><span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-xs font-bold">Diproses Admin</span></td>
                                        <td class="p-4 text-center">
                                            <a href="detail.php?id=<?= $row['id'] ?>&tipe=penawaran" class="text-amber-600 hover:text-amber-800 font-bold text-sm bg-amber-50 hover:bg-amber-100 px-3 py-1.5 rounded-lg transition">Detail</a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="p-8 text-center text-slate-500">Anda belum mengambil program donatur manapun.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>

<?php include 'components/footer.php'; ?>