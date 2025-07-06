<?php
session_start();
require_once '../config.php';
require_once 'templates/header_mahasiswa.php';

// Pastikan hanya mahasiswa yang bisa akses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$praktikum_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data praktikum
$praktikum = mysqli_query($conn, "SELECT * FROM praktikum WHERE id = $praktikum_id");
if (!$praktikum || mysqli_num_rows($praktikum) == 0) {
    echo "<div class='max-w-2xl mx-auto mt-8 bg-red-100 text-red-700 p-4 rounded'>Praktikum tidak ditemukan.</div>";
    require_once 'templates/footer_mahasiswa.php';
    exit();
}
$p = mysqli_fetch_assoc($praktikum);

// Ambil daftar modul dari database
$modul_query = mysqli_query($conn, "SELECT * FROM modul WHERE praktikum_id = $praktikum_id ORDER BY created_at ASC");
?>

    <div class="bg-white rounded-xl shadow p-8">
        <h1 class="text-3xl font-bold mb-4"><?= htmlspecialchars($p['nama']) ?></h1>
        <p class="mb-6 text-gray-700"><?= nl2br(htmlspecialchars($p['deskripsi'])) ?></p>

        <h2 class="text-2xl font-semibold mb-3">Daftar Modul / Materi</h2>
        <div class="space-y-4">
            <?php if (mysqli_num_rows($modul_query) > 0): ?>
                <?php while ($modul = mysqli_fetch_assoc($modul_query)): ?>
                    <?php
                    // Cek laporan mahasiswa untuk modul ini
                    $laporan_q = mysqli_query($conn, "SELECT * FROM laporan WHERE user_id = $user_id AND modul_id = {$modul['id']}");
                    $laporan = mysqli_fetch_assoc($laporan_q);
                    ?>
                    <div class="bg-gray-50 p-4 rounded shadow flex items-center justify-between">
                        <div>
                            <div class="font-semibold"><?= htmlspecialchars($modul['judul']) ?></div>
                            <?php if (!empty($modul['file_materi'])): ?>
                                <a href="../uploads/modul/<?= htmlspecialchars($modul['file_materi']) ?>" class="text-blue-600 hover:underline text-sm" target="_blank">Unduh Materi</a>
                            <?php else: ?>
                                <span class="text-gray-400 text-sm">Materi belum diunggah</span>
                            <?php endif; ?>
                        </div>
                        <div class="text-right">
                            <?php if ($laporan): ?>
                                <?php if ($laporan['status'] == 'dinilai'): ?>
                                    <span class="inline-block bg-green-100 text-green-700 px-3 py-1 rounded text-xs">Nilai: <?= $laporan['nilai'] ?></span>
                                    <div class="text-xs text-gray-500 mt-1">Feedback: <?= htmlspecialchars($laporan['feedback']) ?></div>
                                <?php else: ?>
                                    <span class="inline-block bg-yellow-100 text-yellow-700 px-3 py-1 rounded text-xs">Menunggu Penilaian</span>
                                <?php endif; ?>
                                <div class="mt-1">
                                    <a href="../uploads/laporan/<?= htmlspecialchars($laporan['file_laporan']) ?>" class="text-blue-500 underline text-xs" target="_blank">Lihat Laporan</a>
                                </div>
                            <?php else: ?>
                                <!-- Form upload laporan -->
                                <form action="upload_laporan.php" method="post" enctype="multipart/form-data" class="flex items-center space-x-2">
                                    <input type="hidden" name="modul_id" value="<?= $modul['id'] ?>">
                                    <input type="file" name="file_laporan" accept=".pdf,.doc,.docx" required class="text-xs">
                                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs">Upload Laporan</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="bg-yellow-100 text-yellow-800 p-4 rounded">Belum ada modul untuk praktikum ini.</div>
            <?php endif; ?>
        </div>
        <!-- Link kembali di bawah kotak -->
        <div class="mt-8 text-center">
            <a href="praktikum_saya.php" class="text-blue-600 hover:underline font-semibold">&larr; Kembali ke Praktikum Saya</a>
        </div>
    </div>


<?php require_once 'templates/footer_mahasiswa.php'; ?>