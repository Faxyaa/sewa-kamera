<?php
require_once __DIR__ . '/auth.php';
checkAdminAuth();
require_once __DIR__ . '/../../config/database.php';

$current_admin_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' | Admin Sewa Kamera Malang' : 'Admin Panel - Sewa Kamera Malang'; ?></title>
    
    <!-- Google Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navybrand: {
                            800: '#1B365D',
                            900: '#122440',
                            950: '#0B1728',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen flex flex-col md:flex-row">

    <!-- Sidebar Component -->
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <!-- Main Admin Container -->
    <div class="flex-grow flex flex-col min-w-0">
        
        <!-- Admin Top Navigation Bar -->
        <header class="bg-white border-b border-slate-200 py-3.5 px-6 flex items-center justify-between shadow-sm">
            <div class="flex items-center space-x-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Store Admin</span>
                <span class="text-slate-300">/</span>
                <h1 class="text-sm md:text-base font-extrabold text-navybrand-800">
                    <?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard Overview'; ?>
                </h1>
            </div>

            <!-- Profile & Quick Link -->
            <div class="flex items-center space-x-4 text-xs font-semibold">
                <a href="../index.php" target="_blank" class="hidden sm:inline-flex items-center space-x-1.5 text-sky-600 hover:text-sky-700 bg-sky-50 px-3 py-1.5 rounded-lg">
                    <i class="fa-solid fa-globe"></i>
                    <span>Lihat Web Publik</span>
                </a>
                
                <div class="flex items-center space-x-2 pl-3 border-l border-slate-200">
                    <div class="w-8 h-8 rounded-full bg-navybrand-800 text-white flex items-center justify-center font-bold">
                        A
                    </div>
                    <span class="hidden sm:inline text-slate-700 font-bold"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                    <a href="logout.php" class="text-red-500 hover:text-red-700 p-1 ml-2" title="Logout">
                        <i class="fa-solid fa-power-off text-sm"></i>
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="p-6 md:p-8 flex-grow">
