<!-- ADMIN SIDEBAR -->
<aside class="w-full md:w-64 bg-navybrand-950 text-slate-300 flex-shrink-0 border-r border-navybrand-900 flex flex-col justify-between">
    
    <div>
        <!-- Logo Header -->
        <div class="p-6 border-b border-navybrand-900 flex items-center space-x-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-sky-400 to-blue-500 p-0.5 shadow">
                <div class="w-full h-full bg-navybrand-950 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-camera text-sky-400"></i>
                </div>
            </div>
            <div>
                <span class="block text-sm font-extrabold text-white tracking-tight leading-none">
                    SEWA KAMERA <span class="text-sky-400">MALANG</span>
                </span>
                <span class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider block mt-1">Admin Control Panel</span>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="p-4 space-y-1 text-xs font-semibold">
            
            <span class="text-[10px] font-extrabold uppercase text-slate-500 tracking-wider px-3 py-2 block">Menu Utama</span>
            
            <a href="index.php" class="flex items-center space-x-3 px-3.5 py-3 rounded-xl transition-all <?php echo ($current_admin_page == 'index.php') ? 'bg-sky-500 text-navybrand-950 font-bold shadow-lg shadow-sky-500/20' : 'hover:bg-navybrand-900 hover:text-white'; ?>">
                <i class="fa-solid fa-chart-line text-sm w-4 text-center"></i>
                <span>Dashboard Stats</span>
            </a>

            <a href="products.php" class="flex items-center space-x-3 px-3.5 py-3 rounded-xl transition-all <?php echo ($current_admin_page == 'products.php' || $current_admin_page == 'product-form.php') ? 'bg-sky-500 text-navybrand-950 font-bold shadow-lg shadow-sky-500/20' : 'hover:bg-navybrand-900 hover:text-white'; ?>">
                <i class="fa-solid fa-camera-retro text-sm w-4 text-center"></i>
                <span>Kelola Produk & Harga</span>
            </a>

            <a href="categories.php" class="flex items-center space-x-3 px-3.5 py-3 rounded-xl transition-all <?php echo ($current_admin_page == 'categories.php') ? 'bg-sky-500 text-navybrand-950 font-bold shadow-lg shadow-sky-500/20' : 'hover:bg-navybrand-900 hover:text-white'; ?>">
                <i class="fa-solid fa-layer-group text-sm w-4 text-center"></i>
                <span>Kelola Kategori</span>
            </a>

            <a href="promos.php" class="flex items-center space-x-3 px-3.5 py-3 rounded-xl transition-all <?php echo ($current_admin_page == 'promos.php') ? 'bg-sky-500 text-navybrand-950 font-bold shadow-lg shadow-sky-500/20' : 'hover:bg-navybrand-900 hover:text-white'; ?>">
                <i class="fa-solid fa-ticket text-sm w-4 text-center"></i>
                <span>Promo & Slider Banner</span>
            </a>

            <span class="text-[10px] font-extrabold uppercase text-slate-500 tracking-wider px-3 py-2 block pt-4">Aksi Sistem</span>

            <a href="../index.php" target="_blank" class="flex items-center space-x-3 px-3.5 py-3 rounded-xl hover:bg-navybrand-900 hover:text-white transition-all text-slate-400">
                <i class="fa-solid fa-arrow-up-right-from-square text-sm w-4 text-center"></i>
                <span>Buka Website Publik</span>
            </a>

            <a href="logout.php" class="flex items-center space-x-3 px-3.5 py-3 rounded-xl hover:bg-red-500/20 hover:text-red-400 text-red-400 transition-all">
                <i class="fa-solid fa-right-from-bracket text-sm w-4 text-center"></i>
                <span>Keluar (Logout)</span>
            </a>

        </nav>
    </div>

    <!-- Admin Footer Info -->
    <div class="p-4 border-t border-navybrand-900 text-[11px] text-slate-500 text-center">
        <p>Local Store System v1.0</p>
    </div>

</aside>
