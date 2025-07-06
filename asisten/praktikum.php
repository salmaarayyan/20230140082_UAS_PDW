<?php
session_start();
require_once '../config.php';

// Cek login asisten
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header('Location: ../login.php');
    exit();
}

// Tambah Praktikum
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $semester = mysqli_real_escape_string($conn, $_POST['semester']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    mysqli_query($conn, "INSERT INTO praktikum (nama, semester, deskripsi, created_at) VALUES ('$nama', '$semester', '$deskripsi', NOW())");
    $_SESSION['success'] = "Praktikum berhasil ditambahkan!";
    header("Location: praktikum.php");
    exit();
}

// Edit Praktikum
if (isset($_POST['edit'])) {
    $id = intval($_POST['id']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $semester = mysqli_real_escape_string($conn, $_POST['semester']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    mysqli_query($conn, "UPDATE praktikum SET nama='$nama', semester='$semester', deskripsi='$deskripsi' WHERE id=$id");
    $_SESSION['success'] = "Praktikum berhasil diupdate!";
    header("Location: praktikum.php");
    exit();
}

// Hapus Praktikum
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    mysqli_query($conn, "DELETE FROM praktikum WHERE id=$id");
    $_SESSION['success'] = "Praktikum berhasil dihapus!";
    header("Location: praktikum.php");
    exit();
}

// Ambil data praktikum
$data = mysqli_query($conn, "SELECT * FROM praktikum ORDER BY created_at DESC");

$pageTitle = 'Manajemen Praktikum';
// Panggil header.php agar sidebar konsisten
require_once 'templates/header.php';
?>

    <?php if (!empty($_SESSION['success'])): ?>
        <div id="alert-success" class="mb-4 p-3 bg-green-100 text-green-700 rounded">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
        <script>
            setTimeout(function() {
                var alert = document.getElementById('alert-success');
                if(alert) alert.style.display = 'none';
            }, 5000);
        </script>
    <?php endif; ?>

    <!-- Form Tambah Praktikum -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8 w-full max-screen-lg">
        <h2 class="text-xl font-bold mb-4">Tambah Praktikum</h2>
        <form method="post" class="space-y-4">
            <div>
                <label class="block mb-1 font-semibold">Nama Praktikum</label>
                <input type="text" name="nama" required class="border p-2 rounded w-full">
            </div>
            <div>
                <label class="block mb-1 font-semibold">Semester</label>
                <select name="semester" required class="border p-2 rounded w-full">
                    <option value="">--Pilih Semester--</option>
                    <?php for($i=1;$i<=8;$i++): ?>
                        <option value="<?= $i ?>"><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label class="block mb-1 font-semibold">Deskripsi</label>
                <textarea name="deskripsi" required class="border p-2 rounded w-full"></textarea>
            </div>
            <button type="submit" name="tambah" class="bg-blue-600 text-white px-4 py-2 rounded">Tambah Praktikum</button>
        </form>
    </div>

    <!-- Daftar Praktikum -->
    <div class="bg-white p-6 rounded-lg shadow-md w-full max-screen-lg">
        <h2 class="text-xl font-bold mb-4">Daftar Praktikum</h2>
        <table class="w-full border mt-2 table-fixed">
    <thead>
        <tr class="bg-gray-200">
            <th class="p-2 border w-1/4 text-left">Nama</th>
            <th class="p-2 border w-1/6 text-left">Semester</th>
            <th class="p-2 border w-2/5 text-left">Deskripsi</th>
            <th class="p-2 border w-1/5 text-left">Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = mysqli_fetch_assoc($data)): ?>
        <tr>
            <td class="p-2 border break-words"><?= htmlspecialchars($row['nama']) ?></td>
            <td class="p-2 border"><?= htmlspecialchars($row['semester']) ?></td>
            <td class="p-2 border break-words"><?= htmlspecialchars($row['deskripsi']) ?></td>
            <td class="p-2 border">
                <div class="gap-2">
                    <button 
                        class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 transition"
                        onclick="showEditModal(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['nama'])) ?>', '<?= htmlspecialchars(addslashes($row['semester'])) ?>', '<?= htmlspecialchars(addslashes($row['deskripsi'])) ?>')"
                        type="button"
                    >Edit</button>
                    <a href="?hapus=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus?')" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 transition">Hapus</a>
                </div>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
    </div>

    <!-- Modal Edit Praktikum -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">Edit Praktikum</h2>
            <form method="post">
                <input type="hidden" name="id" id="editId">
                <div class="mb-2">
                    <input type="text" name="nama" id="editNama" required class="border p-2 rounded w-full" placeholder="Nama Praktikum">
                </div>
                <div class="mb-2">
                    <select name="semester" id="editSemester" required class="border p-2 rounded w-full">
                        <option value="">--Pilih Semester--</option>
                        <?php for($i=1;$i<=8;$i++): ?>
                            <option value="<?= $i ?>"><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="mb-2">
                    <textarea name="deskripsi" id="editDeskripsi" required class="border p-2 rounded w-full" placeholder="Deskripsi"></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded border">Batal</button>
                    <button type="submit" name="edit" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 transition">Simpan</button>
                </div>
            </form>
        </div>
    </div>

<script>
function showEditModal(id, nama, semester, deskripsi) {
    document.getElementById('editId').value = id;
    document.getElementById('editNama').value = nama.replace(/\\'/g, "'");
    document.getElementById('editSemester').value = semester.replace(/\\'/g, "'");
    document.getElementById('editDeskripsi').value = deskripsi.replace(/\\'/g, "'");
    document.getElementById('editModal').classList.remove('hidden');
}
function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>

<?php require_once 'templates/footer.php'; ?>