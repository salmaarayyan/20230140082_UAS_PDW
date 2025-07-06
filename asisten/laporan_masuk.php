<?php
session_start();
require_once '../config.php';

// Cek login asisten
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header('Location: ../login.php');
    exit();
}

// Ambil data filter
$filter_modul = isset($_GET['modul']) ? intval($_GET['modul']) : '';
$filter_mahasiswa = isset($_GET['mahasiswa']) ? intval($_GET['mahasiswa']) : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

// Ambil data modul & mahasiswa untuk filter
$modulList = mysqli_query($conn, "SELECT m.id, m.judul, p.nama AS praktikum FROM modul m JOIN praktikum p ON m.praktikum_id=p.id ORDER BY p.nama, m.judul");
$mahasiswaList = mysqli_query($conn, "SELECT id, nama FROM users WHERE role='mahasiswa' ORDER BY nama");

// Query laporan
$where = [];
if ($filter_modul) $where[] = "l.modul_id = $filter_modul";
if ($filter_mahasiswa) $where[] = "l.user_id = $filter_mahasiswa";
if ($filter_status !== '' && $filter_status !== 'all') {
    if ($filter_status === 'belum') {
        $where[] = "l.status = 'dikirim'";
    } else if ($filter_status === 'sudah') {
        $where[] = "l.status = 'dinilai'";
    }
}
$where_sql = $where ? 'WHERE '.implode(' AND ', $where) : '';

$laporan = mysqli_query($conn, "
    SELECT l.*, u.nama AS mahasiswa_nama, u.email, u.id AS mahasiswa_id, md.judul AS modul_judul, p.nama AS praktikum_nama
    FROM laporan l
    JOIN users u ON l.user_id = u.id
    JOIN modul md ON l.modul_id = md.id
    JOIN praktikum p ON md.praktikum_id = p.id
    $where_sql
    ORDER BY l.created_at DESC
");

$pageTitle = 'Laporan Masuk';
require_once 'templates/header.php';
?>

<?php if (!empty($_SESSION['success'])): ?>
    <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
    <script>
            setTimeout(() => {
                const alert = document.getElementById('alert-success');
                if (alert) alert.style.display = 'none';
            }, 4000);
        </script>
<?php endif; ?>

    <!-- Filter -->
    <form method="get" class="flex flex-wrap gap-4 mb-6 items-end">
        <div>
            <label class="block mb-1 font-semibold">Modul</label>
            <select name="modul" class="border p-2 rounded w-48">
                <option value="">Semua Modul</option>
                <?php while($m = mysqli_fetch_assoc($modulList)): ?>
                    <option value="<?= $m['id'] ?>" <?= $filter_modul == $m['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($m['praktikum']) ?> - <?= htmlspecialchars($m['judul']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div>
            <label class="block mb-1 font-semibold">Mahasiswa</label>
            <select name="mahasiswa" class="border p-2 rounded w-48">
                <option value="">Semua Mahasiswa</option>
                <?php while($mhs = mysqli_fetch_assoc($mahasiswaList)): ?>
                    <option value="<?= $mhs['id'] ?>" <?= $filter_mahasiswa == $mhs['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($mhs['nama']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div>
            <label class="block mb-1 font-semibold">Status</label>
            <select name="status" class="border p-2 rounded w-40">
                <option value="all" <?= $filter_status === 'all' ? 'selected' : '' ?>>Semua Status</option>
                <option value="belum" <?= $filter_status === 'belum' ? 'selected' : '' ?>>Belum Dinilai</option>
                <option value="sudah" <?= $filter_status === 'sudah' ? 'selected' : '' ?>>Sudah Dinilai</option>
            </select>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
    </form>

    <!-- Tabel Laporan -->
    <div class="bg-white p-6 rounded-lg shadow-md w-full">
        <h2 class="text-xl font-bold mb-4">Daftar Laporan Masuk</h2>
        <table class="min-w-full table-auto">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-4 py-2 text-left">No</th>
                    <th class="px-4 py-2 text-left">Mahasiswa</th>
                    <th class="px-4 py-2 text-left">Praktikum</th>
                    <th class="px-4 py-2 text-left">Modul</th>
                    <th class="px-4 py-2 text-left">File</th>
                    <th class="px-4 py-2 text-left">Status</th>
                    <th class="px-4 py-2 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no=1; while($row = mysqli_fetch_assoc($laporan)): ?>
                <tr class="border-b">
                    <td class="px-4 py-2"><?= $no++ ?></td>
                    <td class="px-4 py-2"><?= htmlspecialchars($row['mahasiswa_nama']) ?><br><span class="text-xs text-gray-500"><?= htmlspecialchars($row['email']) ?></span></td>
                    <td class="px-4 py-2"><?= htmlspecialchars($row['praktikum_nama']) ?></td>
                    <td class="px-4 py-2"><?= htmlspecialchars($row['modul_judul']) ?></td>
                    <td class="px-4 py-2">
                        <?php if ($row['file_laporan']): ?>
                            <a href="../uploads/laporan/<?= htmlspecialchars($row['file_laporan']) ?>" target="_blank" class="text-blue-600 underline">Lihat File</a>
                        <?php else: ?>
                            <span class="text-gray-400">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-2">
                        <?php if ($row['status'] == 'dinilai'): ?>
                            <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">Sudah Dinilai</span>
                        <?php else: ?>
                            <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs">Belum Dinilai</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-2">
                        <a href="detail_laporan.php?id=<?= $row['id'] ?>" class="text-blue-600 hover:underline">Detail</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php require_once 'templates/footer.php'; ?>