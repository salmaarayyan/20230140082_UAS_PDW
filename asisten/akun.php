<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header('Location: ../login.php');
    exit();
}

$activePage = 'akun';

// Ambil data user yang akan diedit jika ada parameter id
$editUser = null;
if (isset($_GET['id'])) {
    $editId = intval($_GET['id']);
    $result = mysqli_query($conn, "SELECT * FROM users WHERE id=$editId");
    $editUser = mysqli_fetch_assoc($result);
}

// Tambah Akun
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    mysqli_query($conn, "INSERT INTO users (nama, email, password, role, created_at) VALUES ('$nama', '$email', '$password', '$role', NOW())");
    $_SESSION['success'] = "Akun berhasil ditambahkan!";
    header("Location: akun.php");
    exit();
}

// Edit Akun
if (isset($_POST['edit'])) {
    $id = intval($_POST['id']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $role = $_POST['role'];
    $update = "UPDATE users SET nama='$nama', email='$email', role='$role' WHERE id=$id";
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $update = "UPDATE users SET nama='$nama', email='$email', password='$password', role='$role' WHERE id=$id";
    }
    mysqli_query($conn, $update);
    $_SESSION['success'] = "Akun berhasil diupdate!";
    header("Location: akun.php");
    exit();
}

// Hapus Akun
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    mysqli_query($conn, "DELETE FROM users WHERE id=$id");
    $_SESSION['success'] = "Akun berhasil dihapus!";
    header("Location: akun.php");
    exit();
}

// Ambil data user
$data = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC");

$pageTitle = 'Manajemen Akun Pengguna';
require_once 'templates/header.php';
?>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded" id="alert-success">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
        <script>
            setTimeout(() => {
                const alert = document.getElementById('alert-success');
                if (alert) alert.style.display = 'none';
            }, 4000);
        </script>
    <?php endif; ?>

    <div class="w-full space-y-8">
        <!-- Form Tambah/Edit Akun -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8 w-full">
            <h2 class="text-xl font-bold mb-4"><?= $editUser ? 'Edit Akun' : 'Tambah Akun' ?></h2>
            <form method="post" class="space-y-4">
                <?php if ($editUser): ?>
                    <input type="hidden" name="id" value="<?= $editUser['id'] ?>">
                <?php endif; ?>
                <div>
                    <label class="block mb-1 font-semibold">Nama</label>
                    <input type="text" name="nama" required class="border p-2 rounded w-full"
                        value="<?= $editUser ? htmlspecialchars($editUser['nama']) : '' ?>">
                </div>
                <div>
                    <label class="block mb-1 font-semibold">Email</label>
                    <input type="email" name="email" required class="border p-2 rounded w-full"
                        value="<?= $editUser ? htmlspecialchars($editUser['email']) : '' ?>">
                </div>
                <div>
                    <label class="block mb-1 font-semibold">Password <?= $editUser ? '(Kosongkan jika tidak diubah)' : '' ?></label>
                    <input type="password" name="password" class="border p-2 rounded w-full" <?= $editUser ? '' : 'required' ?>>
                </div>
                <div>
                    <label class="block mb-1 font-semibold">Role</label>
                    <select name="role" required class="border p-2 rounded w-full">
                        <option value="mahasiswa" <?= $editUser && $editUser['role']=='mahasiswa' ? 'selected' : '' ?>>Mahasiswa</option>
                        <option value="asisten" <?= $editUser && $editUser['role']=='asisten' ? 'selected' : '' ?>>Asisten</option>
                    </select>
                </div>
                <button type="submit" name="<?= $editUser ? 'edit' : 'tambah' ?>" class="bg-blue-600 text-white px-4 py-2 rounded">
                    <?= $editUser ? 'Simpan Perubahan' : 'Tambah Akun' ?>
                </button>
                <?php if ($editUser): ?>
                    <a href="akun.php" class="ml-2 text-gray-600 hover:underline">Batal</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Daftar Akun -->
        <div class="bg-white p-6 rounded-lg shadow-md w-full">
            <h2 class="text-xl font-bold mb-4">Daftar Akun Pengguna</h2>
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 text-left">No</th>
                        <th class="px-4 py-2 text-left">Nama</th>
                        <th class="px-4 py-2 text-left">Email</th>
                        <th class="px-4 py-2 text-left">Role</th>
                        <th class="px-4 py-2 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no=1; while($row = mysqli_fetch_assoc($data)): ?>
                    <tr class="border-b">
                        <td class="px-4 py-2"><?= $no++ ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($row['nama']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($row['email']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($row['role']) ?></td>
                        <td class="px-4 py-2">
                            <a href="akun.php?id=<?= $row['id'] ?>" class="text-blue-600 hover:underline">Edit</a> | 
                            <a href="akun.php?hapus=<?= $row['id'] ?>" class="text-red-600 hover:underline" onclick="return confirm('Yakin hapus akun ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php require_once 'templates/footer.php'; ?>