<?php
if (!isset($pdo)) {
    require_once __DIR__ . '/../config/database.php';
}

// Active page menu detector helper
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' | ' . SITE_NAME : SITE_NAME . ' - Rental Kamera & Alat Fotografi Malang'; ?></title>
    <meta name="description" content="Sewa Kamera Malang - Tempat rental kamera mirrorless, lensa, lighting, gimbal, drone, & alat fotografi terlengkap di Malang. Harga terjangkau, syarat mudah, respon cepat.">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS (CDN with Custom Config) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        skybrand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            300: '#72C1EC',
                            400: '#5BB0E0',
                            500: '#3B92C4',
                            600: '#2574A9',
                        },
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
    
    <!-- Custom Style -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-slate-50 text-slate-800 flex flex-col min-h-screen">

    <!-- 1. TOP BAR INFO (Delta Camera Style) -->
    <div class="bg-navybrand-900 text-white text-xs py-2 px-4 border-b border-navybrand-800">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-2">
            <!-- Left Info -->
            <div class="flex items-center space-x-4">
                <a href="mailto:<?php echo SITE_EMAIL; ?>" class="hover:text-skybrand-300 transition-colors flex items-center gap-1.5">
                    <i class="fa-regular fa-envelope text-skybrand-300"></i>
                    <span><?php echo SITE_EMAIL; ?></span>
                </a>
                <span class="text-slate-600">|</span>
                <a href="<?php echo getWaLink(); ?>" target="_blank" class="hover:text-green-400 transition-colors flex items-center gap-1.5">
                    <i class="fa-brands fa-whatsapp text-green-400"></i>
                    <span><?php echo SITE_WA; ?> (Fast Response)</span>
                </a>
            </div>
            
            <!-- Right Info & Socials -->
            <div class="flex items-center space-x-4 text-slate-300">
                <span class="hidden md:inline"><i class="fa-solid fa-location-dot text-skybrand-300 mr-1"></i> Malang, Jawa Timur</span>
                <div class="flex items-center space-x-3">
                    <a href="<?php echo getIgLink(); ?>" target="_blank" class="hover:text-skybrand-300 transition-colors" title="Instagram"><i class="fa-brands fa-instagram"></i></a>
                    <a href="https://facebook.com" target="_blank" class="hover:text-skybrand-300 transition-colors" title="Facebook"><i class="fa-brands fa-facebook"></i></a>
                    <a href="https://tiktok.com" target="_blank" class="hover:text-skybrand-300 transition-colors" title="TikTok"><i class="fa-brands fa-tiktok"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. MAIN HEADER & NAVBAR -->
    <header class="sticky top-0 z-50 header-glass shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                
                <!-- Circular Logo & Brand Name -->
                <a href="index.php" class="flex items-center space-x-3 group">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-navybrand-800 to-skybrand-400 p-0.5 shadow-md group-hover:scale-105 transition-transform">
                        <div class="w-full h-full bg-white rounded-full flex items-center justify-center">
                            <i class="fa-solid fa-camera text-navybrand-800 text-xl group-hover:text-skybrand-500 transition-colors"></i>
                        </div>
                    </div>
                    <div>
                        <span class="block text-xl font-extrabold text-navybrand-800 tracking-tight leading-none">
                            SEWA KAMERA <span class="text-skybrand-500">MALANG</span>
                        </span>
                        <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 block mt-0.5">
                            Rental Kamera & Gear Fotografi
                        </span>
                    </div>
                </a>

                <!-- Search Input Bar -->
                <div class="hidden lg:flex items-center flex-1 max-w-xs mx-8">
                    <form action="index.php" method="GET" class="w-full relative">
                        <input type="text" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" placeholder="Cari Kamera, Lensa, Drone..." class="w-full pl-10 pr-4 py-2 text-sm bg-slate-100 border border-slate-200 rounded-full focus:outline-none focus:ring-2 focus:ring-skybrand-400 focus:bg-white transition-all">
                        <button type="submit" class="absolute left-3 top-2.5 text-slate-400 hover:text-navybrand-800">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </form>
                </div>

                <!-- Desktop Navigation Menu -->
                <nav class="hidden md:flex items-center space-x-1 lg:space-x-2 text-sm font-semibold">
                    <a href="index.php" class="px-3 py-2 rounded-lg transition-colors <?php echo ($current_page == 'index.php') ? 'text-skybrand-500 bg-skybrand-50 font-bold' : 'text-slate-700 hover:text-skybrand-500 hover:bg-slate-100'; ?>">
                        Home
                    </a>
                    <a href="booking-info.php" class="px-3 py-2 rounded-lg transition-colors <?php echo ($current_page == 'booking-info.php') ? 'text-skybrand-500 bg-skybrand-50 font-bold' : 'text-slate-700 hover:text-skybrand-500 hover:bg-slate-100'; ?>">
                        Info Booking
                    </a>
                    <a href="pricelist.php" class="px-3 py-2 rounded-lg transition-colors <?php echo ($current_page == 'pricelist.php') ? 'text-skybrand-500 bg-skybrand-50 font-bold' : 'text-slate-700 hover:text-skybrand-500 hover:bg-slate-100'; ?>">
                        Pricelist
                    </a>
                    <a href="promo.php" class="px-3 py-2 rounded-lg transition-colors <?php echo ($current_page == 'promo.php') ? 'text-skybrand-500 bg-skybrand-50 font-bold' : 'text-slate-700 hover:text-skybrand-500 hover:bg-slate-100'; ?>">
                        Promo <span class="bg-red-500 text-white text-[10px] px-1.5 py-0.5 rounded-full uppercase ml-1 animate-pulse">HOT</span>
                    </a>
                    <a href="faq.php" class="px-3 py-2 rounded-lg transition-colors <?php echo ($current_page == 'faq.php') ? 'text-skybrand-500 bg-skybrand-50 font-bold' : 'text-slate-700 hover:text-skybrand-500 hover:bg-slate-100'; ?>">
                        FAQ
                    </a>
                    <a href="contact.php" class="px-3 py-2 rounded-lg transition-colors <?php echo ($current_page == 'contact.php') ? 'text-skybrand-500 bg-skybrand-50 font-bold' : 'text-slate-700 hover:text-skybrand-500 hover:bg-slate-100'; ?>">
                        Hubungi Kami
                    </a>
                </nav>

                <!-- Action Button & Mobile Toggle -->
                <div class="flex items-center space-x-3">
                    <a href="<?php echo getWaLink(); ?>" target="_blank" class="hidden sm:inline-flex items-center space-x-2 bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white px-4 py-2.5 rounded-full text-xs font-bold shadow-md transition-all btn-wa-pulse">
                        <i class="fa-brands fa-whatsapp text-lg"></i>
                        <span>Chat Admin</span>
                    </a>
                    
                    <!-- Mobile Hamburger Button -->
                    <button idmobileMenuBtn" id="mobileMenuBtn" class="md:hidden text-slate-700 hover:text-navybrand-800 focus:outline-none p-2 rounded-lg border border-slate-200">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        <div id="mobileMenu" class="hidden md:hidden border-t border-slate-200 bg-white px-4 pt-3 pb-4 space-y-2">
            <form action="index.php" method="GET" class="mb-3 relative">
                <input type="text" name="search" placeholder="Cari Kamera, Lensa..." class="w-full pl-9 pr-4 py-2 text-sm bg-slate-100 border border-slate-200 rounded-lg">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400"></i>
            </form>
            <a href="index.php" class="block px-3 py-2 rounded-lg text-slate-700 font-semibold hover:bg-slate-100">Home</a>
            <a href="booking-info.php" class="block px-3 py-2 rounded-lg text-slate-700 font-semibold hover:bg-slate-100">Info Booking</a>
            <a href="pricelist.php" class="block px-3 py-2 rounded-lg text-slate-700 font-semibold hover:bg-slate-100">Pricelist</a>
            <a href="promo.php" class="block px-3 py-2 rounded-lg text-slate-700 font-semibold hover:bg-slate-100">Promo Diskon</a>
            <a href="faq.php" class="block px-3 py-2 rounded-lg text-slate-700 font-semibold hover:bg-slate-100">FAQ</a>
            <a href="contact.php" class="block px-3 py-2 rounded-lg text-slate-700 font-semibold hover:bg-slate-100">Hubungi Kami</a>
            <div class="pt-2 border-t border-slate-100">
                <a href="<?php echo getWaLink(); ?>" target="_blank" class="w-full flex items-center justify-center space-x-2 bg-green-600 text-white py-2.5 rounded-lg text-sm font-bold shadow">
                    <i class="fa-brands fa-whatsapp text-lg"></i>
                    <span>Chat Admin WA</span>
                </a>
            </div>
        </div>
    </header>

    <main class="flex-grow">
