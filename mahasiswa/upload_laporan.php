<?php
session_start();
require_once '../config.php';

// Pastikan hanya mahasiswa yang bisa akses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modul_id'])) {
    $modul_id = intval($_POST['modul_id']);

    // Validasi file
    if (isset($_FILES['file_laporan']) && $_FILES['file_laporan']['error'] === UPLOAD_ERR_OK) {
        $allowed_ext = ['pdf', 'doc', 'docx'];
        $file_name = $_FILES['file_laporan']['name'];
        $file_tmp = $_FILES['file_laporan']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_ext)) {
            $_SESSION['upload_error'] = "Format file tidak didukung!";
            header("Location: detail_praktikum.php?id=" . getPraktikumId($modul_id, $conn));
            exit();
        }

        // Buat folder jika belum ada
        $upload_dir = "../uploads/laporan/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        // Nama file unik
        $new_name = "laporan_{$user_id}_{$modul_id}_" . time() . "." . $file_ext;
        $dest = $upload_dir . $new_name;

        // Cek apakah sudah pernah upload
        $cek = mysqli_query($conn, "SELECT id FROM laporan WHERE user_id=$user_id AND modul_id=$modul_id");
        if (mysqli_num_rows($cek) > 0) {
            $_SESSION['upload_error'] = "Kamu sudah mengupload laporan untuk modul ini.";
            header("Location: detail_praktikum.php?id=" . getPraktikumId($modul_id, $conn));
            exit();
        }

        if (move_uploaded_file($file_tmp, $dest)) {
            // Simpan ke database
            $stmt = $conn->prepare("INSERT INTO laporan (user_id, modul_id, file_laporan) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $user_id, $modul_id, $new_name);
            $stmt->execute();

            $_SESSION['upload_success'] = "Laporan berhasil diupload!";
        } else {
            $_SESSION['upload_error'] = "Gagal mengupload file.";
        }
    } else {
        $_SESSION['upload_error'] = "File tidak ditemukan.";
    }

    header("Location: detail_praktikum.php?id=" . getPraktikumId($modul_id, $conn));
    exit();
}

// Fungsi untuk mendapatkan praktikum_id dari modul_id
function getPraktikumId($modul_id, $conn) {
    $q = mysqli_query($conn, "SELECT praktikum_id FROM modul WHERE id=$modul_id");
    $d = mysqli_fetch_assoc($q);
    return $d ? $d['praktikum_id'] : 0;
}
?>