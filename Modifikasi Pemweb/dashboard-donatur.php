<?php
require_once 'auth_check.php';
wajib_login('donatur');
require_once 'koneksi.php';

$user_id = $_SESSION['user_id'];

// 1. Ambil data program desa yang di-DANAI oleh donatur ini
$query_danai = "SELECT hp.created_at as tgl_danai, mb.* FROM history_penyaluran hp
                JOIN permintaan_bantuan mb ON hp.program_id = mb.id
                WHERE hp.user_id = '$user_id' AND hp.tipe_program = 'permintaan'
                ORDER BY hp.created_at DESC";
$result_danai = mysqli_query($conn, $query_danai);

// 2. Ambil data penawaran bantuan yang DIBUAT SENDIRI oleh donatur ini
$query_my_offer = "SELECT * FROM penawaran_bantuan WHERE user_id = '$user_id' ORDER BY created_at DESC";
$result_my_offer = mysqli_query($conn, $query_my_offer);
?>

<?php include 'components/header.php'; ?>

<main class="pt-36 pb-20 min-h-screen bg-slate-50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex flex-wrap justify-between items-center mb-10 gap-4">
            <div>
                <h1 class="text-4xl font-bold text-slate-900 mb-2">Dashboard Donatur</h1>
                <p class="text-slate-500 text-lg">Selamat datang, <span class="font-bold text-teal-600"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Donatur') ?></span></p>
            </div>
            
        </div>

        <div class="flex flex-col lg:flex-row gap-8 w-full">

            <div class="w-full lg:w-1/3 p-8 bg-white rounded-2xl shadow-lg border-t-4 border-teal-500 h-fit hover:shadow-xl transition duration-300">
                <div class="w-12 h-12 bg-teal-50 rounded-lg flex items-center justify-center mb-6">
                    <svg class="w-6 h-6 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                </div>
                <h3 class="font-bold text-slate-800 text-2xl mb-3">Tawarkan Bantuan</h3>
                <p class="text-base text-slate-500 mb-8 leading-relaxed">Beri kontribusi bantuan sosial melalui sistem SI BanTal. Bantuan Anda sangat berarti bagi mereka yang membutuhkan.</p>
                <a href="contact.php" class="block w-full text-center px-6 py-3.5 bg-teal-500 text-white rounded-full text-base font-bold hover:bg-teal-600 hover:shadow-lg transition duration-300">
                    Buat Penawaran Baru
                </a>
            </div>

            <div class="w-full lg:w-2/3 flex flex-col gap-8">

                <div class="p-8 bg-white rounded-2xl shadow-lg border-t-4 border-slate-700 hover:shadow-xl transition duration-300">
                    <h3 class="font-bold text-slate-800 text-2xl mb-3">📝 Penawaran Saya</h3>
                    <p class="text-base text-slate-500 mb-6">Pantau status form penawaran bantuan yang Anda ajukan ke sistem SI BanTal.</p>

                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left border-collapse min-w-max">
                            <thead class="bg-slate-100 text-slate-600 text-sm border-b border-slate-200">
                                <tr>
                                    <th class="p-4 font-bold">Tanggal Input</th>
                                    <th class="p-4 font-bold">Jenis Bantuan</th>
                                    <th class="p-4 font-bold">Status Admin</th>
                                    <th class="p-4 font-bold">Status Klaim</th>
                                    <th class="p-4 font-bold text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm text-slate-700">
                                <?php if($result_my_offer && mysqli_num_rows($result_my_offer) > 0): ?>
                                    <?php while($row = mysqli_fetch_assoc($result_my_offer)): ?>
                                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                                        <td class="p-4"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                                        <td class="p-4 uppercase text-xs font-bold text-slate-500"><?= htmlspecialchars($row['jenis_penawaran']) ?></td>
                                        <td class="p-4">
                                            <?php 
                                                if($row['status'] == 'pending') echo '<span class="bg-slate-200 text-slate-700 px-3 py-1 rounded-full text-xs font-bold">Menunggu</span>';
                                                elseif($row['status'] == 'approved') echo '<span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold">Disetujui</span>';
                                                elseif($row['status'] == 'rejected') echo '<span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold">Ditolak</span>';
                                            ?>
                                        </td>
                                        <td class="p-4">
                                            <?= ($row['is_funded'] == 1) ? '<span class="text-teal-600 font-bold">✅ Diambil Desa</span>' : '<span class="text-amber-500 font-bold">⏳ Belum Diambil</span>' ?>
                                        </td>
                                        <td class="p-4 text-center">
                                            <a href="detail.php?id=<?= $row['id'] ?>&tipe=penawaran" class="text-teal-600 hover:text-teal-800 font-bold text-sm bg-teal-50 hover:bg-teal-100 px-3 py-1.5 rounded-lg transition">Detail</a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="p-8 text-center text-slate-500">Belum ada form penawaran yang Anda buat.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="p-8 bg-white rounded-2xl shadow-lg border-t-4 border-teal-500 hover:shadow-xl transition duration-300">
                    <h3 class="font-bold text-slate-800 text-2xl mb-3">🤝 Partisipasi Program Desa</h3>
                    <p class="text-base text-slate-500 mb-6">Daftar kebutuhan desa dari halaman Program Aktif yang bersedia Anda danai.</p>

                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left border-collapse min-w-max">
                            <thead class="bg-teal-50 text-teal-800 text-sm border-b border-teal-200">
                                <tr>
                                    <th class="p-4 font-bold">Tgl Danai</th>
                                    <th class="p-4 font-bold">Nama Desa</th>
                                    <th class="p-4 font-bold">Target</th>
                                    <th class="p-4 font-bold">Keterangan</th>
                                    <th class="p-4 font-bold text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm text-slate-700">
                                <?php if($result_danai && mysqli_num_rows($result_danai) > 0): ?>
                                    <?php while($row = mysqli_fetch_assoc($result_danai)): ?>
                                    <tr class="border-b border-slate-100 hover:bg-teal-50">
                                        <td class="p-4"><?= date('d M Y', strtotime($row['tgl_danai'])) ?></td>
                                        <td class="p-4 font-bold text-slate-900">Desa <?= htmlspecialchars($row['desa']) ?></td>
                                        <td class="p-4 uppercase text-xs font-bold text-slate-500"><?= htmlspecialchars($row['target_bantuan']) ?></td>
                                        <td class="p-4"><span class="bg-teal-100 text-teal-700 px-3 py-1 rounded-full text-xs font-bold">Diproses Admin</span></td>
                                        <td class="p-4 text-center">
                                            <a href="detail.php?id=<?= $row['id'] ?>&tipe=permintaan" class="text-teal-600 hover:text-teal-800 font-bold text-sm bg-teal-50 hover:bg-teal-100 px-3 py-1.5 rounded-lg transition">Detail</a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="p-8 text-center text-slate-500">Anda belum mendanai program desa manapun.</td></tr>
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