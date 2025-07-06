<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panel Mahasiswa - <?php echo $pageTitle ?? 'Dashboard'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">

<!-- Navbar -->
<nav class="bg-white shadow border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center">
                <span class="text-2xl font-bold text-red-600 tracking-wide">SIMPRAK</span>
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <?php 
                            $activeClass = 'border-b-2 border-red-600 text-gray-900 font-semibold';
                            $inactiveClass = 'text-gray-600 hover:text-red-600 hover:border-b-2 hover:border-red-600';
                        ?>
                        <a href="dashboard.php" class="px-3 py-2 text-sm <?php echo ($activePage == 'dashboard') ? $activeClass : $inactiveClass; ?>">Dashboard</a>
                        <a href="praktikum_saya.php" class="px-3 py-2 text-sm <?php echo ($activePage == 'praktikum_saya') ? $activeClass : $inactiveClass; ?>">Praktikum Saya</a>
                        <a href="katalog_praktikum.php" class="px-3 py-2 text-sm <?php echo ($activePage == 'katalog_praktikum') ? $activeClass : $inactiveClass; ?>">Katalog Praktikum</a>
                    </div>
                </div>
            </div>
            <div class="hidden md:block">
                <a href="../logout.php" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-md transition-colors duration-300">
                    Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="container mx-auto p-6 lg:p-8">