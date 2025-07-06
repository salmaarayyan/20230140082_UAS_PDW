<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header('Location: ../login.php');
    exit();
}

$id = intval($_GET['id']);
$q = mysqli_query($conn, "
    SELECT l.*, u.nama AS mahasiswa_nama, u.email, md.judul AS modul_judul, p.nama AS praktikum_nama
    FROM laporan l
    JOIN users u ON l.user_id = u.id
    JOIN modul md ON l.modul_id = md.id
    JOIN praktikum p ON md.praktikum_id = p.id
    WHERE l.id = $id
");
$laporan = mysqli_fetch_assoc($q);

if (!$laporan) {
    echo "Laporan tidak ditemukan.";
    exit();
}

// Proses simpan nilai & feedback
if (isset($_POST['simpan'])) {
    $nilai = intval($_POST['nilai']);
    $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);
    mysqli_query($conn, "UPDATE laporan SET nilai=$nilai, feedback='$feedback', status='dinilai' WHERE id=$id");
    $_SESSION['success'] = "Praktikum {$laporan['mahasiswa_nama']} sudah dinilai!";
    header("Location: laporan_masuk.php");
    exit();
}
$pageTitle = 'Detail Laporan Mahasiswa';
require_once 'templates/header.php';
?>
    <div class="bg-white p-6 rounded-lg shadow-md max-w-xl">
        <div class="mb-4">
            <strong>Nama Mahasiswa:</strong> <?= htmlspecialchars($laporan['mahasiswa_nama']) ?><br>
            <strong>Email:</strong> <?= htmlspecialchars($laporan['email']) ?><br>
            <strong>Praktikum:</strong> <?= htmlspecialchars($laporan['praktikum_nama']) ?><br>
            <strong>Modul:</strong> <?= htmlspecialchars($laporan['modul_judul']) ?><br>
            <strong>File Laporan:</strong>
            <?php if ($laporan['file_laporan']): ?>
                <a href="../uploads/laporan/<?= htmlspecialchars($laporan['file_laporan']) ?>" target="_blank" class="text-blue-600 underline">Download</a>
            <?php else: ?>
                <span class="text-gray-400">-</span>
            <?php endif; ?>
        </div>
        <form method="post" class="space-y-4">
            <div>
                <label class="block font-semibold mb-1">Nilai (0-100)</label>
                <input type="number" name="nilai" min="0" max="100" value="<?= htmlspecialchars($laporan['nilai']) ?>" class="border p-2 rounded w-full" required>
            </div>
            <div>
                <label class="block font-semibold mb-1">Feedback</label>
                <textarea name="feedback" class="border p-2 rounded w-full" required><?= htmlspecialchars($laporan['feedback']) ?></textarea>
            </div>
            <button type="submit" name="simpan" class="bg-blue-600 text-white px-4 py-2 rounded">Simpan</button>
            <a href="laporan_masuk.php" class="ml-2 text-gray-600 hover:underline">Kembali</a>
        </form>
    </div>
<?php require_once 'templates/footer.php'; ?>