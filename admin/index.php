<?php
$page_title = "Dashboard Overview";
require_once __DIR__ . '/includes/header.php';

// Stats metrics
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_categories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$total_promos = $pdo->query("SELECT COUNT(*) FROM promos WHERE is_active = 1")->fetchColumn();
$total_bestsellers = $pdo->query("SELECT COUNT(*) FROM products WHERE is_bestseller = 1 AND is_active = 1")->fetchColumn();

// Fetch recent products
$stmtRecent = $pdo->query("SELECT p.*, c.name as category_name,
                           (SELECT price FROM rental_rates WHERE product_id = p.id AND duration_type = '24 Jam' LIMIT 1) as price_24h
                           FROM products p 
                           JOIN categories c ON p.category_id = c.id 
                           ORDER BY p.id DESC LIMIT 5");
$recent_products = $stmtRecent->fetchAll();
?>

<!-- STATS OVERVIEW CARDS -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    
    <!-- Stat 1: Total Products -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex items-center justify-between">
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Produk</span>
            <span class="text-3xl font-extrabold text-navybrand-800 mt-1 block"><?php echo $total_products; ?></span>
            <span class="text-[11px] text-emerald-600 font-semibold flex items-center gap-1 mt-1">
                <i class="fa-solid fa-circle-check"></i> <?php echo $total_products; ?> Aktif disewakan
            </span>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-500 flex items-center justify-center text-xl font-bold">
            <i class="fa-solid fa-camera-retro"></i>
        </div>
    </div>

    <!-- Stat 2: Total Categories -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex items-center justify-between">
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Kategori Alat</span>
            <span class="text-3xl font-extrabold text-navybrand-800 mt-1 block"><?php echo $total_categories; ?></span>
            <span class="text-[11px] text-slate-500 font-semibold block mt-1">Mirrorless, Lensa, Drone, DLL</span>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-500 flex items-center justify-center text-xl font-bold">
            <i class="fa-solid fa-layer-group"></i>
        </div>
    </div>

    <!-- Stat 3: Best Sellers -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex items-center justify-between">
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Best Seller</span>
            <span class="text-3xl font-extrabold text-navybrand-800 mt-1 block"><?php echo $total_bestsellers; ?></span>
            <span class="text-[11px] text-amber-600 font-semibold flex items-center gap-1 mt-1">
                <i class="fa-solid fa-fire"></i> Highlight di Beranda
            </span>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-xl font-bold">
            <i class="fa-solid fa-star"></i>
        </div>
    </div>

    <!-- Stat 4: Active Promos -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex items-center justify-between">
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Promo Aktif</span>
            <span class="text-3xl font-extrabold text-navybrand-800 mt-1 block"><?php echo $total_promos; ?></span>
            <span class="text-[11px] text-sky-600 font-semibold block mt-1">Slider Banner Beranda</span>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-xl font-bold">
            <i class="fa-solid fa-ticket"></i>
        </div>
    </div>

</div>

<!-- QUICK ACTION SHORTCUTS -->
<div class="bg-gradient-to-r from-navybrand-900 to-navybrand-800 rounded-3xl p-6 sm:p-8 text-white mb-8 shadow-md">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h2 class="text-xl font-extrabold">Selamat Datang di Panel Admin Sewa Kamera Malang!</h2>
            <p class="text-xs text-slate-300 mt-1 max-w-xl">
                Kelola katalog produk, atur harga sewa per jam (6j / 12j / 24j), perbarui banner promo, dan tambah kategori alat fotografi dari sini.
            </p>
        </div>
        
        <div class="flex flex-wrap gap-3">
            <a href="product-form.php" class="bg-sky-400 hover:bg-sky-300 text-slate-950 font-extrabold px-5 py-3 rounded-xl text-xs transition-all flex items-center space-x-2 shadow-lg">
                <i class="fa-solid fa-plus text-sm"></i>
                <span>Tambah Produk Baru</span>
            </a>
            <a href="categories.php" class="bg-white/10 hover:bg-white/20 text-white font-bold px-4 py-3 rounded-xl text-xs border border-white/20 backdrop-blur transition-all flex items-center space-x-2">
                <i class="fa-solid fa-folder-plus text-sm"></i>
                <span>Kelola Kategori</span>
            </a>
            <a href="promos.php" class="bg-white/10 hover:bg-white/20 text-white font-bold px-4 py-3 rounded-xl text-xs border border-white/20 backdrop-blur transition-all flex items-center space-x-2">
                <i class="fa-solid fa-images text-sm"></i>
                <span>Ganti Banner Promo</span>
            </a>
        </div>
    </div>
</div>

<!-- RECENT PRODUCTS TABLE -->
<div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
        <div>
            <h3 class="font-extrabold text-navybrand-800 text-base">Produk Terbaru Ditambahkan</h3>
            <p class="text-slate-400 text-xs mt-0.5">Daftar 5 unit alat kamera paling baru di sistem</p>
        </div>
        <a href="products.php" class="text-xs font-bold text-sky-600 hover:text-sky-700">Lihat Semua Produk &rarr;</a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-slate-50 text-slate-500 font-extrabold uppercase border-b border-slate-100">
                    <th class="py-3.5 px-6">Produk</th>
                    <th class="py-3.5 px-4">Kategori</th>
                    <th class="py-3.5 px-4">Sewa / 24 Jam</th>
                    <th class="py-3.5 px-4 text-center">Status Badge</th>
                    <th class="py-3.5 px-6 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-semibold text-slate-700">
                <?php foreach ($recent_products as $rp): ?>
                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="py-4 px-6 flex items-center space-x-3">
                        <img src="<?php echo htmlspecialchars($rp['image_url']); ?>" alt="<?php echo htmlspecialchars($rp['name']); ?>" class="w-10 h-10 object-contain bg-white border border-slate-200 rounded-lg p-0.5">
                        <div>
                            <span class="font-extrabold text-navybrand-800 block text-sm"><?php echo htmlspecialchars($rp['name']); ?></span>
                            <span class="text-[10px] text-slate-400 uppercase font-bold"><?php echo htmlspecialchars($rp['brand']); ?></span>
                        </div>
                    </td>
                    <td class="py-4 px-4 text-slate-600"><?php echo htmlspecialchars($rp['category_name']); ?></td>
                    <td class="py-4 px-4 font-bold text-sky-600"><?php echo formatRupiah($rp['price_24h']); ?></td>
                    <td class="py-4 px-4 text-center">
                        <div class="flex items-center justify-center space-x-1">
                            <?php if ($rp['is_bestseller']): ?>
                            <span class="bg-amber-100 text-amber-700 text-[10px] font-bold px-2 py-0.5 rounded">Best Seller</span>
                            <?php endif; ?>
                            <?php if ($rp['is_new']): ?>
                            <span class="bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded">New</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="py-4 px-6 text-right">
                        <a href="product-form.php?id=<?php echo $rp['id']; ?>" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1.5 rounded-lg text-xs font-bold transition-colors">
                            <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
