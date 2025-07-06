<?php
session_start();
require_once '../config.php';
$activePage = 'katalog_praktikum';

// Cek login mahasiswa
$is_mahasiswa = isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'mahasiswa';
$user_id = $is_mahasiswa ? $_SESSION['user_id'] : null;

// Ambil keyword pencarian jika ada
$keyword = isset($_GET['search']) ? trim($_GET['search']) : '';

// Query data praktikum dengan filter search
if ($keyword !== '') {
    $safe_keyword = mysqli_real_escape_string($conn, $keyword);
    $query = "SELECT * FROM praktikum WHERE nama LIKE '%$safe_keyword%' OR deskripsi LIKE '%$safe_keyword%' ORDER BY created_at DESC";
} else {
    $query = "SELECT * FROM praktikum ORDER BY created_at DESC";
}
$result = mysqli_query($conn, $query);

// Pilih header sesuai role
if ($is_mahasiswa) {
    require_once 'templates/header_mahasiswa.php';
} else {
    // Header publik custom
    echo '<!DOCTYPE html><html lang="id"><head>
    <meta charset="UTF-8">
    <title>Katalog Mata Praktikum</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-[#f5f7fa] min-h-screen font-sans">
    <!-- Header -->
    <nav class="bg-white shadow border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <span class="text-2xl font-bold text-red-600 tracking-wide">SIMPRAK</span>
                </div>
                <div>
                    <a href="../login.php" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-5 rounded-md transition-colors duration-300 shadow">Login</a>
                </div>
            </div>
        </div>
    </nav>
    <!-- Hero Section -->
    <section class="bg-white rounded-2xl shadow-lg max-w-5xl mx-auto mt-10 flex flex-col md:flex-row items-center p-8 md:p-12 gap-8">
        <div class="flex-1">
            <h1 class="text-3xl md:text-4xl font-extrabold text-blue-900 mb-4">Selamat Datang di <span class="text-red-600">SIMPRAK</span></h1>
            <p class="text-gray-700 text-lg mb-6">Ayo tingkatkan skillmu dengan mengikuti praktikum favoritmu! Temukan berbagai mata praktikum menarik di bawah ini.</p>
        </div>
        <div class="flex-1 flex justify-center">
            <img src="https://imgproc.heel.com/HEELPROD/media/library/karriere.heel.de/karriere-heel.de/content/3_studenten/3_praktikum-und-abschlussarbeiten/9_ingenieurstudenten_dscf5646_16zu9.jpg?f=WebP&w=1920&h=1000" alt="Hero Ilustrasi" class="rounded-xl w-64 h-40 object-cover shadow-md">
        </div>
    </section>';
}
?>

<?php if (!empty($_SESSION['success'])): ?>
    <div id="alert-success" class="mb-4 p-3 bg-green-100 text-green-700 rounded max-w-2xl mx-auto mt-6">
        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
    <script>
        setTimeout(() => {
            const alert = document.getElementById('alert-success');
            if (alert) alert.style.display = 'none';
        }, 4000);
    </script>
<?php endif; ?>
<?php if (!empty($_SESSION['error'])): ?>
    <div id="alert-error" class="mb-4 p-3 bg-red-100 text-red-700 rounded max-w-2xl mx-auto mt-6">
        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
    <script>
        setTimeout(() => {
            const alert = document.getElementById('alert-error');
            if (alert) alert.style.display = 'none';
        }, 4000);
    </script>
<?php endif; ?>

<!-- Form Search Praktikum -->
<div class="max-w-4xl mx-auto mt-10 mb-6 px-4">
    <form method="get" class="flex gap-2">
        <input type="text" name="search" value="<?= htmlspecialchars($keyword) ?>" placeholder="Cari nama atau deskripsi praktikum..." class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-200" />
        <button type="submit" class="bg-gray-900 hover:bg-gray-700 text-white px-5 py-2 rounded font-semibold transition">Cari</button>
    </form>
</div>

    <h2 class="text-4xl font-bold mb-8 text-gray-800 tracking-tight text-center">Katalog Praktikum</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-16">
        <?php if (mysqli_num_rows($result) == 0): ?>
            <div class="col-span-3 text-center text-gray-500 py-10">Belum ada data praktikum.</div>
        <?php endif; ?>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center border border-gray-200">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 20l9-5-9-5-9 5 9 5z"/></svg>
            </div>
            <div class="font-semibold text-lg text-gray-800 mb-1"><?= htmlspecialchars($row['nama']) ?></div>
            <div class="text-gray-500 text-sm mb-3"><?= mb_strimwidth(strip_tags($row['deskripsi']), 0, 60, "...") ?></div>
            <?php if (!empty($row['jumlah_pertemuan'])): ?>
                <div class="text-sm text-gray-400 mb-2">Jumlah Pertemuan: <span class="font-semibold"><?= $row['jumlah_pertemuan'] ?></span></div>
            <?php endif; ?>
            <div class="mt-2 w-full flex gap-2">
            <?php if ($is_mahasiswa): ?>
                <?php
                $praktikum_id = $row['id'];
                $cek = mysqli_query($conn, "SELECT 1 FROM pendaftaran_praktikum WHERE user_id=$user_id AND praktikum_id=$praktikum_id");
                if (mysqli_num_rows($cek) == 0):
                ?>
                    <form action="daftar_praktikum.php" method="post" class="flex-1">
                        <input type="hidden" name="praktikum_id" value="<?= $praktikum_id ?>">
                        <button type="submit" class="w-full bg-gray-900 hover:bg-gray-700 text-white font-bold px-4 py-2 rounded transition">Daftar</button>
                    </form>
                <?php else: ?>
                    <span class="flex-1 inline-block text-center bg-gray-100 text-gray-700 px-3 py-2 rounded font-semibold">Sudah Terdaftar</span>
                <?php endif; ?>
                <a href="detail_praktikum.php?id=<?= $row['id'] ?>" class="flex-1 bg-gray-900 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm transition text-center font-bold">Lihat Detail</a>
            <?php else: ?>
                <a href="../login.php" class="w-full bg-gray-900 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm transition text-center font-bold">Lihat Detail</a>
            <?php endif; ?>
            </div>
        </div>
        <?php endwhile; ?>
    </div>


<?php
if ($is_mahasiswa) {
    require_once 'templates/footer_mahasiswa.php';
} else {
    echo '</body></html>';
}
?>