<?php
session_start();
require_once '../config.php';
$activePage = 'praktikum_saya';
require_once 'templates/header_mahasiswa.php';

// Pastikan hanya mahasiswa yang bisa akses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil daftar praktikum yang diikuti mahasiswa ini
$query = "
    SELECT p.id, p.nama, p.deskripsi
    FROM pendaftaran_praktikum pp
    JOIN praktikum p ON pp.praktikum_id = p.id
    WHERE pp.user_id = $user_id
    ORDER BY p.created_at DESC
";
$result = mysqli_query($conn, $query);
?>

    <h1 class="text-4xl font-bold mb-8 text-gray-800 tracking-tight text-center">Praktikum Saya</h1>
    <?php if (mysqli_num_rows($result) > 0): ?>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php while($row = mysqli_fetch_assoc($result)): ?>
            <div class="bg-white p-6 rounded-xl shadow-md">
                <h2 class="text-xl font-semibold text-gray-800 mb-2"><?= htmlspecialchars($row['nama']) ?></h2>
                <p class="text-gray-600 mb-4"><?= nl2br(htmlspecialchars($row['deskripsi'])) ?></p>
                <a href="detail_praktikum.php?id=<?= $row['id'] ?>" class="bg-gray-900 hover:bg-gray-700 text-white px-4 py-2 rounded transition font-semibold">Lihat Detail & Tugas</a>
            </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>      
        <div class="bg-yellow-100 text-yellow-800 p-4 rounded">Kamu belum mendaftar ke praktikum manapun.</div>
    <?php endif; ?>

<?php require_once 'templates/footer_mahasiswa.php'; ?>