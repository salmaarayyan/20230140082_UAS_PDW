<?php
session_start();
require_once '../config.php';

// Pastikan user sudah login dan role mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['praktikum_id'])) {
    $praktikum_id = intval($_POST['praktikum_id']);

    // Cek apakah sudah pernah daftar
    $cek = mysqli_query($conn, "SELECT 1 FROM pendaftaran_praktikum WHERE user_id=$user_id AND praktikum_id=$praktikum_id");
    if (mysqli_num_rows($cek) == 0) {
        // Daftarkan
        mysqli_query($conn, "INSERT INTO pendaftaran_praktikum (user_id, praktikum_id) VALUES ($user_id, $praktikum_id)");
        $_SESSION['success'] = "Berhasil mendaftar praktikum!";
    } else {
        $_SESSION['error'] = "Kamu sudah terdaftar di praktikum ini.";
    }
    header("Location: katalog_praktikum.php");
    exit();
} else {
    // Jika akses langsung tanpa POST
    header("Location: katalog_praktikum.php");
    exit();
}
?>