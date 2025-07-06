<?php
session_start();
$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require_once 'templates/header_mahasiswa.php'; 
require_once '../config.php';

$user_id = $_SESSION['user_id'];
$jml_praktikum = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM pendaftaran_praktikum WHERE user_id=$user_id"))['jml'] ?? 0;
$jml_selesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM laporan WHERE user_id=$user_id AND status='dinilai'"))['jml'] ?? 0;

// Hitung jumlah tugas (modul) yang belum dikumpulkan mahasiswa
$sql_menunggu = "
    SELECT COUNT(*) as jml FROM modul m
    JOIN pendaftaran_praktikum pp ON m.praktikum_id = pp.praktikum_id
    WHERE pp.user_id = $user_id
    AND m.id NOT IN (
        SELECT laporan.modul_id FROM laporan WHERE laporan.user_id = $user_id
    )
";
$jml_menunggu = mysqli_fetch_assoc(mysqli_query($conn, $sql_menunggu))['jml'] ?? 0;

// Ambil 3 praktikum terbaru untuk "Praktikum Populer"
$populer = mysqli_query($conn, "SELECT * FROM praktikum ORDER BY created_at DESC LIMIT 3");

// Ambil notifikasi terbaru (misal: upload modul baru)
$notifikasi = [];
$q_notif = mysqli_query($conn, "
    SELECT m.judul, m.created_at, p.nama as praktikum
    FROM modul m
    JOIN praktikum p ON m.praktikum_id = p.id
    ORDER BY m.created_at DESC
    LIMIT 5
");
while ($row = mysqli_fetch_assoc($q_notif)) {
    $notifikasi[] = [
        'icon' => '<svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>',
        'text' => "Modul baru <b>" . htmlspecialchars($row['judul']) . "</b> telah diupload pada praktikum <b>" . htmlspecialchars($row['praktikum']) . "</b>.",
        'waktu' => $row['created_at']
    ];
}
?>

<!-- Hero Section with Background Image from URL -->
<div class="relative rounded-xl shadow mb-10 overflow-hidden" style="height:320px;">
    <img src="https://i.pinimg.com/736x/ec/9b/d7/ec9bd71d2d8fbf0a08f3a1cd173c0091.jpg" 
        alt="Hero" class="absolute inset-0 w-full h-full object-cover opacity-70">
    <div class="absolute inset-0 bg-gray-900 bg-opacity-60 flex flex-col items-center justify-center">
        <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-2 text-center drop-shadow">Selamat Datang, <?= htmlspecialchars($_SESSION['nama']) ?>!</h1>
        <p class="text-lg md:text-xl text-white font-medium opacity-90 mb-4 text-center drop-shadow">Ayo tingkatkan skillmu dengan mengikuti praktikum favoritmu!</p>
        <a href="katalog_praktikum.php" class="bg-white text-gray-900 font-semibold px-6 py-2 rounded shadow hover:bg-gray-100 transition">Lihat Katalog Praktikum</a>
    </div>
</div>

<!-- Statistik -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
    <div class="bg-white p-8 rounded-xl shadow flex flex-col items-center border border-gray-200">
        <svg class="w-10 h-10 text-gray-400 mb-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 20l9-5-9-5-9 5 9 5z"/><path d="M12 12V4l9 5-9 5-9-5 9-5z"/></svg>
        <div class="text-4xl font-bold text-gray-800"><?= $jml_praktikum ?></div>
        <div class="mt-2 text-base text-gray-500">Praktikum Diikuti</div>
    </div>
    <div class="bg-white p-8 rounded-xl shadow flex flex-col items-center border border-gray-200">
        <svg class="w-10 h-10 text-gray-400 mb-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
        <div class="text-4xl font-bold text-gray-800"><?= $jml_selesai ?></div>
        <div class="mt-2 text-base text-gray-500">Tugas Selesai</div>
    </div>
    <div class="bg-white p-8 rounded-xl shadow flex flex-col items-center border border-gray-200">
        <svg class="w-10 h-10 text-gray-400 mb-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
        <div class="text-4xl font-bold text-gray-800"><?= $jml_menunggu ?></div>
        <div class="mt-2 text-base text-gray-500">Tugas Menunggu</div>
    </div>
</div>

<!-- Notifikasi Terbaru -->
<h3 class="text-3xl font-bold mb-8 text-gray-800 tracking-tight text-center">Notifikasi Terbaru</h3>
<div class="bg-white p-6 rounded-xl shadow mb-10">
    <ul class="space-y-4">
        <?php if (count($notifikasi) > 0): ?>
            <?php foreach ($notifikasi as $notif): ?>
                <li class="flex items-start p-3 border-b border-gray-100 last:border-b-0">
                    <span class="mr-4"><?php echo $notif['icon']; ?></span>
                    <div>
                        <span><?php echo $notif['text']; ?></span>
                        <div class="text-xs text-gray-400"><?php echo date('d M Y H:i', strtotime($notif['waktu'])); ?></div>
                    </div>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li class="text-gray-500">Belum ada notifikasi.</li>
        <?php endif; ?>
    </ul>
</div>

<!-- Praktikum Populer -->
<div class="mb-10">
    <h2 class="text-3xl font-bold mb-8 text-gray-800 tracking-tight text-center">Praktikum Terbaru</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php while($p = mysqli_fetch_assoc($populer)): ?>
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center border border-gray-200">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 20l9-5-9-5-9 5 9 5z"/></svg>
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1"><?= htmlspecialchars($p['nama']) ?></div>
            <div class="text-gray-500 text-sm mb-3"><?= mb_strimwidth(strip_tags($p['deskripsi']), 0, 60, "...") ?></div>
            <a href="detail_praktikum.php?id=<?= $p['id'] ?>" class="bg-gray-900 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm transition">Lihat Detail</a>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Call to Action -->
<div class="bg-white border border-gray-200 text-gray-900 p-8 rounded-xl shadow flex flex-col items-center">
    <h3 class="text-2xl font-bold mb-2">Belum daftar praktikum?</h3>
    <p class="mb-4 text-gray-600">Gabung sekarang dan dapatkan pengalaman belajar terbaik!</p>
    <a href="katalog_praktikum.php" class="bg-gray-900 text-white font-bold px-6 py-2 rounded shadow hover:bg-gray-700 transition">Daftar Praktikum</a>
</div>

<?php require_once 'templates/footer_mahasiswa.php'; ?>