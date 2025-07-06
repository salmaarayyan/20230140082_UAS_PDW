<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header('Location: ../login.php');
    exit();
}

$pageTitle = 'Manajemen Modul';
$activePage = 'modul';

// Ambil data praktikum untuk dropdown
$praktikumList = mysqli_query($conn, "SELECT id, nama FROM praktikum ORDER BY nama ASC");

// Tambah Modul
if (isset($_POST['tambah'])) {
    $praktikum_id = intval($_POST['praktikum_id']);
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $file_materi = null;

    if (isset($_FILES['file_materi']) && $_FILES['file_materi']['error'] === UPLOAD_ERR_OK) {
        $allowed_extensions = ['pdf', 'doc', 'docx'];
        $file_extension = strtolower(pathinfo($_FILES['file_materi']['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, $allowed_extensions)) {
            echo "<script>alert('Hanya file PDF, DOC, atau DOCX yang diperbolehkan.'); window.location='modul.php';</script>";
            exit();
        }

        $uploadDir = '../uploads/modul/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $fileName = time() . '_' . basename($_FILES['file_materi']['name']);
        $targetFile = $uploadDir . $fileName;
        move_uploaded_file($_FILES['file_materi']['tmp_name'], $targetFile);
        $file_materi = $fileName;
    }

    mysqli_query($conn, "INSERT INTO modul (praktikum_id, judul, file_materi, created_at) VALUES ($praktikum_id, '$judul', '$file_materi', NOW())");
    $_SESSION['success'] = "Modul berhasil ditambahkan!";
    header("Location: modul.php");
    exit();
}

// Edit Modul
$edit = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $q = mysqli_query($conn, "SELECT * FROM modul WHERE id=$id");
    $edit = mysqli_fetch_assoc($q);
}

// Update Modul
if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $praktikum_id = intval($_POST['praktikum_id']);
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $file_materi = $_POST['file_lama'];

    if (!empty($_FILES['file']['name'])) {
        $allowed_extensions = ['pdf', 'doc', 'docx'];
        $file_extension = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, $allowed_extensions)) {
            echo "<script>alert('Hanya file PDF, DOC, atau DOCX yang diperbolehkan.'); window.location='modul.php';</script>";
            exit();
        }
        $uploadDir = '../uploads/modul/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $fileName = time() . '_' . basename($_FILES['file']['name']);
        $targetFile = $uploadDir . $fileName;
        move_uploaded_file($_FILES['file']['tmp_name'], $targetFile);
        $file_materi = $fileName;
    }

    mysqli_query($conn, "UPDATE modul SET praktikum_id=$praktikum_id, judul='$judul', file_materi='$file_materi' WHERE id=$id");
    $_SESSION['success'] = "Modul berhasil diupdate!";
    header("Location: modul.php");
    exit();
}

// Hapus Modul
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    // Hapus file dari server jika ada
    $q = mysqli_query($conn, "SELECT file_materi FROM modul WHERE id=$id");
    $data = mysqli_fetch_assoc($q);
    if ($data && $data['file_materi'] && file_exists("../uploads/modul/" . $data['file_materi'])) {
        unlink("../uploads/modul/" . $data['file_materi']);
    }
    mysqli_query($conn, "DELETE FROM modul WHERE id=$id");
    $_SESSION['success'] = "Modul berhasil dihapus!";
    header("Location: modul.php");
    exit();
}

// Ambil data modul join praktikum
$modul = mysqli_query($conn, "SELECT m.*, p.nama AS praktikum_nama FROM modul m JOIN praktikum p ON m.praktikum_id = p.id ORDER BY m.created_at DESC");

// Panggil header.php agar sidebar konsisten
require_once 'templates/header.php';
?>
        <?php if (!empty($_SESSION['success'])): ?>
            <div id="alert-success" class="mb-4 p-3 bg-green-100 text-green-700 rounded">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
            <script>
                setTimeout(() => {
                    const alert = document.getElementById('alert-success');
                    if (alert) alert.style.display = 'none';
                }, 4000);
            </script>
        <?php endif; ?>

        <!-- Card Form Tambah/Edit Modul -->
        <div class="bg-white p-6 rounded-xl shadow mb-8 w-full max-w-none">
            <h2 class="text-2xl font-bold mb-4"><?= $edit ? 'Edit Modul' : 'Tambah Modul' ?></h2>
            <form method="post" enctype="multipart/form-data" class="space-y-4">
                <?php if ($edit): ?>
                    <input type="hidden" name="id" value="<?= $edit['id'] ?>">
                    <input type="hidden" name="file_lama" value="<?= $edit['file_materi'] ?>">
                <?php endif; ?>
                <div>
                    <label class="block mb-1 font-semibold">Praktikum</label>
                    <select name="praktikum_id" required class="border p-2 rounded w-full">
                        <option value="">-- Pilih Praktikum --</option>
                        <?php
                        mysqli_data_seek($praktikumList, 0);
                        while($p = mysqli_fetch_assoc($praktikumList)): ?>
                            <option value="<?= $p['id'] ?>" <?= ($edit && $edit['praktikum_id'] == $p['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['nama']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label class="block mb-1 font-semibold">Judul Modul</label>
                    <input type="text" name="judul" required class="w-full border rounded px-3 py-2" value="<?= htmlspecialchars($edit['judul'] ?? '') ?>">
                </div>
                <div>
                    <label class="block mb-1 font-semibold">File Materi (PDF/DOC/DOCX)</label>
                    <input type="file" name="<?= $edit ? 'file' : 'file_materi' ?>" accept=".pdf,.doc,.docx" class="block" <?= $edit ? '' : 'required' ?>>
                    <?php if ($edit && $edit['file_materi']): ?>
                        <div class="mt-2 text-sm">File saat ini: <a href="../uploads/modul/<?= htmlspecialchars($edit['file_materi']) ?>" target="_blank" class="text-blue-600 underline"><?= htmlspecialchars($edit['file_materi']) ?></a></div>
                    <?php endif; ?>
                </div>
                <div>
                    <?php if ($edit): ?>
                        <button type="submit" name="update" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">Update</button>
                        <a href="modul.php" class="ml-2 text-gray-600 hover:underline">Batal</a>
                    <?php else: ?>
                        <button type="submit" name="tambah" class="bg-gray-900 text-white px-4 py-2 rounded hover:bg-gray-700">Tambah Modul</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Card Tabel Daftar Modul -->
        <div class="bg-white p-6 rounded-xl shadow w-full max-w-none">
            <h2 class="text-2xl font-bold mb-4">Daftar Modul</h2>
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 text-left">Praktikum</th>
                        <th class="px-4 py-2 text-left">Judul</th>
                        <th class="px-4 py-2 text-left">File</th>
                        <th class="px-4 py-2 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($modul)): ?>
                    <tr class="border-b">
                        <td class="px-4 py-2 align-top text-left"><?= htmlspecialchars($row['praktikum_nama']) ?></td>
                        <td class="px-4 py-2 align-top text-left"><?= htmlspecialchars($row['judul']) ?></td>
                        <td class="px-4 py-2 align-top text-left">
                            <?php if ($row['file_materi']): ?>
                                <a href="../uploads/modul/<?= htmlspecialchars($row['file_materi']) ?>" target="_blank" class="text-blue-600 underline">Lihat File</a>
                            <?php else: ?>
                                <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-2 align-top text-left">
                            <a href="modul.php?edit=<?= $row['id'] ?>" class="text-blue-600 hover:underline">Edit</a> | 
                            <a href="modul.php?hapus=<?= $row['id'] ?>" class="text-red-600 hover:underline" onclick="return confirm('Yakin hapus?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>


<?php require_once 'templates/footer.php'; ?>