<?php
$page_title = "Daftar Harga Sewa Kamera Malang Lengkap";
require_once __DIR__ . '/includes/header.php';

// Fetch all products grouped by category with their rates
$stmtCat = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmtCat->fetchAll();
?>

<!-- Subpage Header -->
<section class="bg-navybrand-900 text-white py-12 border-b border-navybrand-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-3">
        <span class="text-skybrand-300 font-extrabold text-xs uppercase tracking-widest bg-white/10 px-3 py-1 rounded-full border border-white/20">
            Official Pricelist
        </span>
        <h1 class="text-3xl md:text-4xl font-extrabold text-white tracking-tight">
            Daftar Harga Sewa Alat Terbaru
        </h1>
        <p class="text-slate-300 text-sm max-w-2xl mx-auto">
            Harga transparan tanpa biaya tersembunyi. Tersedia pilihan durasi 6 Jam, 12 Jam, dan 24 Jam.
        </p>
    </div>
</section>

<!-- Content Table -->
<section class="py-14 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        
        <?php foreach ($categories as $cat): ?>
        <?php
            // Fetch products for this category
            $stmtP = $pdo->prepare("SELECT p.* FROM products p WHERE p.category_id = :cid AND p.is_active = 1 ORDER BY p.name ASC");
            $stmtP->execute([':cid' => $cat['id']]);
            $prods = $stmtP->fetchAll();

            if (empty($prods)) continue;
        ?>
        <div class="bg-white rounded-3xl overflow-hidden shadow-sm border border-slate-200">
            <!-- Category Header -->
            <div class="bg-gradient-to-r from-navybrand-900 to-navybrand-800 text-white px-6 py-4 flex items-center justify-between">
                <h2 class="text-lg font-bold flex items-center gap-2">
                    <i class="fa-solid <?php echo htmlspecialchars($cat['icon']); ?> text-skybrand-300"></i>
                    <span>Kategori: <?php echo htmlspecialchars($cat['name']); ?></span>
                </h2>
                <span class="text-xs bg-white/10 px-3 py-1 rounded-full font-medium">
                    <?php echo count($prods); ?> Item
                </span>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-100 text-slate-700 text-xs uppercase font-extrabold border-b border-slate-200">
                            <th class="py-3.5 px-6">Nama Peralatan</th>
                            <th class="py-3.5 px-4">Brand</th>
                            <th class="py-3.5 px-4 text-center">Harga 6 Jam</th>
                            <th class="py-3.5 px-4 text-center">Harga 12 Jam</th>
                            <th class="py-3.5 px-4 text-center">Harga 24 Jam</th>
                            <th class="py-3.5 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs font-semibold text-slate-700">
                        <?php foreach ($prods as $p): ?>
                        <?php
                            // Fetch rates
                            $stmtR = $pdo->prepare("SELECT duration_type, price FROM rental_rates WHERE product_id = :pid");
                            $stmtR->execute([':pid' => $p['id']]);
                            $rateRows = $stmtR->fetchAll(PDO::FETCH_KEY_PAIR);

                            $p6  = isset($rateRows['6 Jam'])  ? formatRupiah($rateRows['6 Jam'])  : '-';
                            $p12 = isset($rateRows['12 Jam']) ? formatRupiah($rateRows['12 Jam']) : '-';
                            $p24 = isset($rateRows['24 Jam']) ? formatRupiah($rateRows['24 Jam']) : '-';
                        ?>
                        <tr class="hover:bg-skybrand-50/40 transition-colors">
                            <td class="py-4 px-6 font-bold text-navybrand-800 flex items-center space-x-3">
                                <img src="<?php echo htmlspecialchars($p['image_url']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" class="w-10 h-10 object-contain bg-white border border-slate-200 rounded-lg p-0.5">
                                <a href="product-detail.php?id=<?php echo $p['id']; ?>" class="hover:text-skybrand-500">
                                    <?php echo htmlspecialchars($p['name']); ?>
                                </a>
                            </td>
                            <td class="py-4 px-4 text-slate-500 font-semibold"><?php echo htmlspecialchars($p['brand']); ?></td>
                            <td class="py-4 px-4 text-center text-slate-600 font-bold"><?php echo $p6; ?></td>
                            <td class="py-4 px-4 text-center text-slate-600 font-bold"><?php echo $p12; ?></td>
                            <td class="py-4 px-4 text-center text-skybrand-600 font-extrabold text-sm"><?php echo $p24; ?></td>
                            <td class="py-4 px-6 text-right">
                                <a href="product-detail.php?id=<?php echo $p['id']; ?>" class="inline-flex items-center space-x-1 bg-skybrand-50 hover:bg-skybrand-500 hover:text-white text-skybrand-600 font-bold px-3 py-1.5 rounded-lg transition-colors">
                                    <span>Pesan WA</span>
                                    <i class="fa-brands fa-whatsapp text-sm"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endforeach; ?>

    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
