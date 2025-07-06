<?php
require_once '../config.php';

// 1. Definisi Variabel untuk Template
$pageTitle = 'Dashboard';
$activePage = 'dashboard';

// Query jumlah modul
$jml_modul = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM modul"))['jml'] ?? 0;
// Query jumlah laporan masuk
$jml_laporan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM laporan"))['jml'] ?? 0;
// Query jumlah laporan belum dinilai
$jml_belum_dinilai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM laporan WHERE status != 'dinilai'"))['jml'] ?? 0;
// Query aktivitas laporan terbaru (join ke user, modul, praktikum)
$aktivitas = mysqli_query($conn, "
    SELECT l.*, u.nama AS nama_mahasiswa, m.judul AS judul_modul, p.nama AS nama_praktikum, l.created_at
    FROM laporan l
    JOIN users u ON l.user_id = u.id
    JOIN modul m ON l.modul_id = m.id
    JOIN praktikum p ON m.praktikum_id = p.id
    ORDER BY l.created_at DESC
    LIMIT 5
");

// 2. Panggil Header
require_once 'templates/header.php'; 
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-blue-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Modul Diajarkan</p>
            <p class="text-2xl font-bold text-gray-800"><?= $jml_modul ?></p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-green-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Laporan Masuk</p>
            <p class="text-2xl font-bold text-gray-800"><?= $jml_laporan ?></p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-yellow-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Laporan Belum Dinilai</p>
            <p class="text-2xl font-bold text-gray-800"><?= $jml_belum_dinilai ?></p>
        </div>
    </div>
</div>

<div class="bg-white p-6 rounded-lg shadow-md mt-8">
    <h3 class="text-xl font-bold text-gray-800 mb-4">Aktivitas Laporan Terbaru</h3>
    <div class="space-y-4">
        <?php while($a = mysqli_fetch_assoc($aktivitas)): ?>
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-4">
                    <span class="font-bold text-gray-500">
                        <?= strtoupper(substr($a['nama_mahasiswa'],0,1)) . (isset(explode(' ', $a['nama_mahasiswa'])[1]) ? strtoupper(substr(explode(' ', $a['nama_mahasiswa'])[1],0,1)) : '') ?>
                    </span>
                </div>
                <div>
                    <p class="text-gray-800">
                        <strong><?= htmlspecialchars($a['nama_mahasiswa']) ?></strong>
                        telah mengumpulkan praktikum
                        <strong><?= htmlspecialchars($a['nama_praktikum']) ?></strong>
                        modul
                        <strong><?= htmlspecialchars($a['judul_modul']) ?></strong>
                    </p>
                    <p class="text-sm text-gray-500"><?= date('d M Y H:i', strtotime($a['created_at'])) ?></p>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>


<?php
// 3. Panggil Footer
require_once 'templates/footer.php';
?>