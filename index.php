<?php
$page_title = "Katalog & Rental Kamera Terpercaya Malang";
require_once __DIR__ . '/includes/header.php';

// Fetch Categories
$stmtCat = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmtCat->fetchAll();

// Fetch Active Promos
$stmtPromo = $pdo->query("SELECT * FROM promos WHERE is_active = 1 ORDER BY id DESC");
$promos = $stmtPromo->fetchAll();

// Filtering logic
$selected_category = isset($_GET['category']) ? trim($_GET['category']) : '';
$selected_filter = isset($_GET['filter']) ? trim($_GET['filter']) : '';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "SELECT p.*, c.name as category_name, c.slug as category_slug,
        (SELECT price FROM rental_rates WHERE product_id = p.id AND duration_type = '24 Jam' LIMIT 1) as price_24h,
        (SELECT price FROM rental_rates WHERE product_id = p.id ORDER BY price ASC LIMIT 1) as price_min
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.is_active = 1";

$params = [];

if (!empty($selected_category)) {
    $sql .= " AND c.slug = :category_slug";
    $params[':category_slug'] = $selected_category;
}

if (!empty($selected_filter)) {
    if ($selected_filter === 'bestseller') {
        $sql .= " AND p.is_bestseller = 1";
    } elseif ($selected_filter === 'new') {
        $sql .= " AND p.is_new = 1";
    }
}

if (!empty($search_query)) {
    $sql .= " AND (p.name LIKE :search OR p.brand LIKE :search OR p.description LIKE :search)";
    $params[':search'] = '%' . $search_query . '%';
}

$sql .= " ORDER BY p.is_bestseller DESC, p.id DESC";

$stmtProd = $pdo->prepare($sql);
$stmtProd->execute($params);
$products = $stmtProd->fetchAll();
?>

<!-- 1. HERO SLIDER BANNER (Delta Camera Style) -->
<section class="relative bg-navybrand-900 overflow-hidden py-8 md:py-12 border-b border-navybrand-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <?php if (!empty($promos)): ?>
        <div class="relative rounded-2xl overflow-hidden shadow-2xl bg-navybrand-950 border border-navybrand-800 min-h-[320px] md:min-h-[400px]">
            <?php foreach ($promos as $index => $promo): ?>
            <div class="hero-slide <?php echo ($index === 0) ? 'active' : ''; ?> p-6 md:p-12 flex flex-col justify-center">
                <div class="absolute inset-0 z-0">
                    <img src="<?php echo htmlspecialchars($promo['banner_image']); ?>" alt="<?php echo htmlspecialchars($promo['title']); ?>" class="w-full h-full object-cover opacity-25 filter blur-[1px]">
                    <div class="absolute inset-0 bg-gradient-to-r from-navybrand-950 via-navybrand-950/90 to-transparent"></div>
                </div>

                <div class="relative z-10 max-w-2xl text-white space-y-4">
                    <div class="inline-flex items-center space-x-2 bg-gradient-to-r from-skybrand-400 to-skybrand-500 text-navybrand-950 font-extrabold text-xs px-3.5 py-1.5 rounded-full uppercase tracking-wider shadow">
                        <i class="fa-solid fa-fire text-amber-900"></i>
                        <span><?php echo htmlspecialchars($promo['discount_info']); ?></span>
                    </div>

                    <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold tracking-tight leading-tight text-white">
                        <?php echo htmlspecialchars($promo['title']); ?>
                    </h2>

                    <p class="text-slate-300 text-sm md:text-base leading-relaxed line-clamp-3">
                        <?php echo htmlspecialchars($promo['description']); ?>
                    </p>

                    <div class="pt-2 flex flex-wrap gap-4 items-center">
                        <a href="<?php echo getWaLink('', 'Promo ' . $promo['title']); ?>" target="_blank" class="inline-flex items-center space-x-2 bg-skybrand-400 hover:bg-skybrand-300 text-navybrand-950 font-bold px-6 py-3 rounded-xl transition-all shadow-lg hover:scale-105">
                            <i class="fa-brands fa-whatsapp text-lg"></i>
                            <span><?php echo htmlspecialchars($promo['button_text']); ?></span>
                        </a>
                        <a href="promo.php" class="inline-flex items-center space-x-2 bg-white/10 hover:bg-white/20 text-white font-semibold px-5 py-3 rounded-xl border border-white/20 backdrop-blur transition-all">
                            <span>Lihat Semua Promo</span>
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Slider Dots -->
            <?php if (count($promos) > 1): ?>
            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex space-x-2">
                <?php foreach ($promos as $idx => $pr): ?>
                <button class="hero-dot h-3 rounded-full transition-all duration-300 <?php echo ($idx === 0) ? 'bg-sky-400 w-8' : 'bg-white/50 w-3'; ?>" aria-label="Slide <?php echo $idx + 1; ?>"></button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div>
</section>


<!-- 2. KEUNTUNGAN / ALASAN SEWA SECTION (8 Key Advantages Grid) -->
<section class="py-14 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto mb-12">
            <span class="text-skybrand-500 font-extrabold text-xs uppercase tracking-widest bg-skybrand-50 px-3 py-1 rounded-full border border-skybrand-100">
                Why Choose Us
            </span>
            <h2 class="text-2xl md:text-3xl font-extrabold text-navybrand-800 mt-2 tracking-tight">
                Keunggulan Rental di <span class="text-skybrand-500">Sewa Kamera Malang</span>
            </h2>
            <p class="text-slate-500 text-sm mt-2">
                Komitmen kami memberikan layanan rental kamera dan peralatan fotografi terbaik untuk mahasiswa, videografer, dan profesional di Malang.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <!-- 1 -->
            <div class="advantage-card p-6 rounded-2xl">
                <div class="w-12 h-12 rounded-xl bg-skybrand-50 text-skybrand-500 flex items-center justify-center text-xl font-bold mb-4 icon-box shadow-sm">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <h3 class="font-bold text-navybrand-800 text-base mb-1">Gear Terawat & Clean</h3>
                <p class="text-slate-500 text-xs leading-relaxed">
                    Setiap unit kamera & lensa selalu dibersihkan, dicek sensornya, dan dikalibrasi berkala sebelum disewakan.
                </p>
            </div>

            <!-- 2 -->
            <div class="advantage-card p-6 rounded-2xl">
                <div class="w-12 h-12 rounded-xl bg-skybrand-50 text-skybrand-500 flex items-center justify-center text-xl font-bold mb-4 icon-box shadow-sm">
                    <i class="fa-solid fa-camera-retro"></i>
                </div>
                <h3 class="font-bold text-navybrand-800 text-base mb-1">Pilihan Alat Lengkap</h3>
                <p class="text-slate-500 text-xs leading-relaxed">
                    Mulai dari Mirrorless Sony/Fuji/Canon, Lensa Art, Lighting Studio, Action Cam, Gimbal RS3, hingga Drone 4K.
                </p>
            </div>

            <!-- 3 -->
            <div class="advantage-card p-6 rounded-2xl">
                <div class="w-12 h-12 rounded-xl bg-skybrand-50 text-skybrand-500 flex items-center justify-center text-xl font-bold mb-4 icon-box shadow-sm">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <h3 class="font-bold text-navybrand-800 text-base mb-1">Durasi Bervariatif</h3>
                <p class="text-slate-500 text-xs leading-relaxed">
                    Fleksibilitas opsi sewa mulai dari paket cepat 6 Jam, 12 Jam, hingga 24 Jam harian sesuai kebutuhan acara.
                </p>
            </div>

            <!-- 4 -->
            <div class="advantage-card p-6 rounded-2xl">
                <div class="w-12 h-12 rounded-xl bg-skybrand-50 text-skybrand-500 flex items-center justify-center text-xl font-bold mb-4 icon-box shadow-sm">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <h3 class="font-bold text-navybrand-800 text-base mb-1">Garansi Sewa 100%</h3>
                <p class="text-slate-500 text-xs leading-relaxed">
                    Jaminan penggantian unit atau spare baterai cadangan jika terjadi kendala teknis saat pengambilan.
                </p>
            </div>

            <!-- 5 -->
            <div class="advantage-card p-6 rounded-2xl">
                <div class="w-12 h-12 rounded-xl bg-skybrand-50 text-skybrand-500 flex items-center justify-center text-xl font-bold mb-4 icon-box shadow-sm">
                    <i class="fa-solid fa-comments"></i>
                </div>
                <h3 class="font-bold text-navybrand-800 text-base mb-1">Free Consultation</h3>
                <p class="text-slate-500 text-xs leading-relaxed">
                    Bingung pilih lensa atau lighting? Admin berpengalaman siap memberi saran rekomendasi gratis.
                </p>
            </div>

            <!-- 6 -->
            <div class="advantage-card p-6 rounded-2xl">
                <div class="w-12 h-12 rounded-xl bg-skybrand-50 text-skybrand-500 flex items-center justify-center text-xl font-bold mb-4 icon-box shadow-sm">
                    <i class="fa-solid fa-bolt"></i>
                </div>
                <h3 class="font-bold text-navybrand-800 text-base mb-1">Fast Response Admin</h3>
                <p class="text-slate-500 text-xs leading-relaxed">
                    Respon kilat via WhatsApp untuk cek ketersediaan stok & booking jadwal pengambilan barang.
                </p>
            </div>

            <!-- 7 -->
            <div class="advantage-card p-6 rounded-2xl">
                <div class="w-12 h-12 rounded-xl bg-skybrand-50 text-skybrand-500 flex items-center justify-center text-xl font-bold mb-4 icon-box shadow-sm">
                    <i class="fa-solid fa-tags"></i>
                </div>
                <h3 class="font-bold text-navybrand-800 text-base mb-1">Promo Bulanan Hemat</h3>
                <p class="text-slate-500 text-xs leading-relaxed">
                    Nikmati cashback, potongan harga sewa paket bundling, dan promo gratis sewa 1 hari khusus durasi panjang.
                </p>
            </div>

            <!-- 8 -->
            <div class="advantage-card p-6 rounded-2xl">
                <div class="w-12 h-12 rounded-xl bg-skybrand-50 text-skybrand-500 flex items-center justify-center text-xl font-bold mb-4 icon-box shadow-sm">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <h3 class="font-bold text-navybrand-800 text-base mb-1">Diskon Pelajar / Mahasiswa</h3>
                <p class="text-slate-500 text-xs leading-relaxed">
                    Potongan khusus untuk mahasiswa Malang (UB, UM, UMM, UIN, Polinema, dll) cukup tunjukkan KTM aktif.
                </p>
            </div>

        </div>

    </div>
</section>


<!-- 3. PRODUCT CATALOG GRID SECTION -->
<section id="katalog" class="py-14 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header Title & Filters -->
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
            <div>
                <span class="text-skybrand-500 font-extrabold text-xs uppercase tracking-widest">Katalog Peralatan</span>
                <h2 class="text-2xl md:text-3xl font-extrabold text-navybrand-800 mt-1">
                    Pilihan Alat Kamera & Gear
                </h2>
            </div>

            <!-- Filter Buttons: Best Seller / New Arrival -->
            <div class="flex items-center space-x-2">
                <a href="index.php?filter=all#katalog" class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all <?php echo (empty($selected_filter)) ? 'bg-navybrand-800 text-white shadow' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100'; ?>">
                    Semua
                </a>
                <a href="index.php?filter=bestseller#katalog" class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all <?php echo ($selected_filter === 'bestseller') ? 'bg-amber-500 text-white shadow' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100'; ?>">
                    <i class="fa-solid fa-fire mr-1 text-amber-300"></i> Best Seller
                </a>
                <a href="index.php?filter=new#katalog" class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all <?php echo ($selected_filter === 'new') ? 'bg-skybrand-500 text-white shadow' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100'; ?>">
                    <i class="fa-solid fa-sparkles mr-1 text-yellow-300"></i> New Arrival
                </a>
            </div>
        </div>

        <!-- Category Filter Pills Bar -->
        <div class="flex items-center space-x-2 overflow-x-auto pb-4 mb-8 no-scrollbar">
            <a href="index.php#katalog" class="px-4 py-2 rounded-full text-xs font-bold whitespace-nowrap transition-all <?php echo (empty($selected_category)) ? 'bg-skybrand-500 text-white shadow-md' : 'bg-white text-slate-700 hover:bg-slate-200 border border-slate-200'; ?>">
                <i class="fa-solid fa-border-all mr-1.5"></i> Semua Kategori
            </a>
            <?php foreach ($categories as $cat): ?>
            <a href="index.php?category=<?php echo urlencode($cat['slug']); ?>#katalog" class="px-4 py-2 rounded-full text-xs font-bold whitespace-nowrap transition-all <?php echo ($selected_category === $cat['slug']) ? 'bg-skybrand-500 text-white shadow-md' : 'bg-white text-slate-700 hover:bg-slate-200 border border-slate-200'; ?>">
                <i class="fa-solid <?php echo htmlspecialchars($cat['icon']); ?> mr-1.5"></i> <?php echo htmlspecialchars($cat['name']); ?>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Search Notice if query present -->
        <?php if (!empty($search_query)): ?>
        <div class="bg-blue-50 border border-blue-200 text-blue-800 p-4 rounded-xl mb-6 flex items-center justify-between">
            <p class="text-sm">
                Menampilkan hasil pencarian untuk: <strong>"<?php echo htmlspecialchars($search_query); ?>"</strong>
            </p>
            <a href="index.php#katalog" class="text-xs font-bold text-blue-600 hover:underline">Hapus Pencarian</a>
        </div>
        <?php endif; ?>

        <!-- Products Grid -->
        <?php if (!empty($products)): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($products as $prod): ?>
            <?php 
                $price_display = !empty($prod['price_24h']) ? $prod['price_24h'] : $prod['price_min'];
            ?>
            <div class="product-card bg-white rounded-2xl overflow-hidden shadow-sm flex flex-col justify-between">
                
                <!-- Image Container with White Background & Badges -->
                <div class="relative bg-white p-4 h-52 flex items-center justify-center overflow-hidden border-b border-slate-100 group">
                    <img src="<?php echo htmlspecialchars($prod['image_url']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>" class="max-h-44 object-contain img-zoom">
                    
                    <!-- Badges -->
                    <div class="absolute top-3 left-3 flex flex-col space-y-1 z-10">
                        <?php if ($prod['is_bestseller']): ?>
                        <span class="bg-amber-500 text-white text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase shadow">
                            Best Seller
                        </span>
                        <?php endif; ?>
                        <?php if ($prod['is_new']): ?>
                        <span class="bg-emerald-500 text-white text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase shadow">
                            NEW
                        </span>
                        <?php endif; ?>
                    </div>

                    <!-- Category Badge Right -->
                    <span class="absolute top-3 right-3 bg-slate-100 text-slate-600 text-[10px] font-semibold px-2 py-0.5 rounded-md border border-slate-200">
                        <?php echo htmlspecialchars($prod['category_name']); ?>
                    </span>
                </div>

                <!-- Product Details -->
                <div class="p-5 flex-grow flex flex-col justify-between space-y-3">
                    <div>
                        <span class="text-[11px] font-bold text-skybrand-600 uppercase tracking-wider block">
                            <?php echo htmlspecialchars($prod['brand']); ?>
                        </span>
                        <h3 class="font-bold text-navybrand-800 text-sm md:text-base line-clamp-2 hover:text-skybrand-500 transition-colors mt-0.5">
                            <a href="product-detail.php?id=<?php echo $prod['id']; ?>">
                                <?php echo htmlspecialchars($prod['name']); ?>
                            </a>
                        </h3>
                    </div>

                    <!-- Price & Action Button -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] text-slate-400 block uppercase font-semibold">Harga Sewa / 24 Jam</span>
                            <span class="text-base font-extrabold text-navybrand-800">
                                <?php echo formatRupiah($price_display); ?>
                            </span>
                        </div>
                        
                        <a href="product-detail.php?id=<?php echo $prod['id']; ?>" class="bg-skybrand-50 hover:bg-skybrand-500 hover:text-white text-skybrand-600 font-bold px-3.5 py-2 rounded-xl text-xs transition-colors flex items-center gap-1">
                            <span>Detail</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>

            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-2xl p-12 text-center border border-slate-200 max-w-lg mx-auto my-8">
            <i class="fa-solid fa-camera-slash text-4xl text-slate-300 mb-3 block"></i>
            <h3 class="font-bold text-slate-700 text-lg">Alat tidak ditemukan!</h3>
            <p class="text-slate-500 text-xs mt-1">Coba kata kunci pencarian lain atau pilih kategori yang berbeda.</p>
            <a href="index.php#katalog" class="mt-4 inline-block bg-skybrand-500 text-white text-xs font-bold px-4 py-2 rounded-lg">
                Reset Filter Katalog
            </a>
        </div>
        <?php endif; ?>

    </div>
</section>

<!-- 4. CTA BANNER BOOKING DIRECT -->
<section class="py-12 bg-gradient-to-r from-navybrand-900 to-navybrand-800 text-white relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="space-y-2 text-center md:text-left">
            <h3 class="text-2xl md:text-3xl font-extrabold text-white">
                Siap Untuk Mengabadikan Momen Istimewa?
            </h3>
            <p class="text-slate-300 text-sm max-w-xl">
                Booking alat kamera impianmu sekarang di <strong>Sewa Kamera Malang</strong>. Proses cepat tanpa ribet, jaminan unit prima!
            </p>
        </div>
        <div class="flex items-center space-x-4">
            <a href="<?php echo getWaLink(); ?>" target="_blank" class="bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold px-6 py-3.5 rounded-xl shadow-lg transition-all flex items-center space-x-2 btn-wa-pulse">
                <i class="fa-brands fa-whatsapp text-xl"></i>
                <span>Chat Admin Malang</span>
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
