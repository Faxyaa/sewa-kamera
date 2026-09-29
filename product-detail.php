<?php
require_once __DIR__ . '/config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch product details
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug 
                       FROM products p 
                       JOIN categories c ON p.category_id = c.id 
                       WHERE p.id = :id AND p.is_active = 1");
$stmt->execute([':id' => $id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: index.php");
    exit;
}

// Fetch rental rates for this product
$stmtRates = $pdo->prepare("SELECT * FROM rental_rates WHERE product_id = :id ORDER BY FIELD(duration_type, '6 Jam', '12 Jam', '24 Jam')");
$stmtRates->execute([':id' => $id]);
$rates = $stmtRates->fetchAll();

// Default initial duration rate selection
$initial_rate = !empty($rates) ? $rates[count($rates) - 1] : ['duration_type' => '24 Jam', 'price' => 0];

// Fetch Sidebar Categories
$stmtCat = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id AND is_active = 1) as total_items FROM categories c ORDER BY c.name ASC");
$side_categories = $stmtCat->fetchAll();

// Fetch Top Rated / Best Seller Products for Sidebar
$stmtTop = $pdo->prepare("SELECT p.*, (SELECT price FROM rental_rates WHERE product_id = p.id AND duration_type = '24 Jam' LIMIT 1) as price_24h 
                          FROM products p 
                          WHERE p.is_active = 1 AND p.id != :id 
                          ORDER BY p.is_bestseller DESC, p.id DESC LIMIT 4");
$stmtTop->execute([':id' => $id]);
$top_products = $stmtTop->fetchAll();

$page_title = $product['name'] . ' - Detail Sewa';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Breadcrumbs -->
<div class="bg-slate-100 border-b border-slate-200 py-3">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex text-xs font-semibold text-slate-500 space-x-2">
            <a href="index.php" class="hover:text-skybrand-500">Home</a>
            <span>/</span>
            <a href="index.php?category=<?php echo urlencode($product['category_slug']); ?>#katalog" class="hover:text-skybrand-500"><?php echo htmlspecialchars($product['category_name']); ?></a>
            <span>/</span>
            <span class="text-slate-800 font-bold truncate max-w-xs"><?php echo htmlspecialchars($product['name']); ?></span>
        </nav>
    </div>
</div>

<!-- MAIN DETAIL CONTENT -->
<section class="py-10 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- LEFT & CENTER: Product Details (Column 8 of 12) -->
            <div class="lg:col-span-8 space-y-8">
                
                <!-- Main Product Card Layout (2 Columns: Image Left, Info Right) -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 grid grid-cols-1 md:grid-cols-2 gap-8">
                    
                    <!-- KIRI: Image Gallery & Kelengkapan -->
                    <div class="space-y-6">
                        <div class="relative bg-white border border-slate-200 rounded-2xl p-6 h-72 flex items-center justify-center overflow-hidden shadow-inner">
                            <img id="mainProductImage" src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="max-h-60 object-contain transition-transform duration-300">
                            
                            <!-- Badges -->
                            <div class="absolute top-3 left-3 flex flex-col space-y-1">
                                <?php if ($product['is_bestseller']): ?>
                                <span class="bg-amber-500 text-white text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase shadow">
                                    Best Seller
                                </span>
                                <?php endif; ?>
                                <?php if ($product['is_new']): ?>
                                <span class="bg-emerald-500 text-white text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase shadow">
                                    NEW
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Thumbnail Previews (Demo Multi-angles) -->
                        <div class="flex items-center space-x-3">
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" data-src="<?php echo htmlspecialchars($product['image_url']); ?>" class="product-thumb w-16 h-16 object-contain bg-white border border-slate-200 rounded-xl p-1 cursor-pointer ring-2 ring-sky-500" alt="Main Thumb">
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" data-src="<?php echo htmlspecialchars($product['image_url']); ?>" class="product-thumb w-16 h-16 object-contain bg-white border border-slate-200 rounded-xl p-1 cursor-pointer hover:border-sky-400" alt="Angle 2">
                        </div>

                        <!-- Kelengkapan Paket Sewa Box -->
                        <div class="bg-skybrand-50/60 border border-skybrand-100 rounded-2xl p-5 space-y-3">
                            <h4 class="text-xs font-extrabold uppercase tracking-wider text-navybrand-800 flex items-center gap-2">
                                <i class="fa-solid fa-box-open text-skybrand-500"></i>
                                <span>Kelengkapan Paket Sewa:</span>
                            </h4>
                            <?php if (!empty($product['kelengkapan'])): ?>
                            <ul class="text-xs text-slate-700 space-y-1.5 list-disc list-inside">
                                <?php 
                                    $items = explode(',', $product['kelengkapan']);
                                    foreach ($items as $item): 
                                ?>
                                <li class="leading-relaxed"><?php echo htmlspecialchars(trim($item)); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <?php else: ?>
                            <p class="text-xs text-slate-500">Unit Utama, Baterai, Charger, Tas Kamera Anti Air.</p>
                            <?php endif; ?>
                        </div>

                    </div>

                    <!-- KANAN: Pricing Table, Selector & WA Action -->
                    <div class="flex flex-col justify-between space-y-6">
                        
                        <div>
                            <div class="flex items-center space-x-2 mb-2">
                                <span class="bg-skybrand-100 text-skybrand-600 font-extrabold text-[10px] px-2.5 py-0.5 rounded-md uppercase">
                                    <?php echo htmlspecialchars($product['category_name']); ?>
                                </span>
                                <span class="text-xs font-bold text-slate-400">
                                    Brand: <?php echo htmlspecialchars($product['brand']); ?>
                                </span>
                            </div>

                            <h1 class="text-xl sm:text-2xl font-extrabold text-navybrand-800 leading-snug">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </h1>

                            <p class="text-xs text-slate-500 mt-3 leading-relaxed">
                                <?php echo htmlspecialchars($product['description']); ?>
                            </p>
                        </div>

                        <!-- Duration Pricing Table & Interactive Pills -->
                        <div class="space-y-3">
                            <label class="block text-xs font-extrabold text-navybrand-800 uppercase tracking-wider">
                                Pilih Durasi Sewa:
                            </label>
                            
                            <div class="grid grid-cols-3 gap-2">
                                <?php foreach ($rates as $index => $rate): ?>
                                <?php $is_default = ($rate['duration_type'] === $initial_rate['duration_type']); ?>
                                <div class="rate-pill p-2.5 rounded-xl text-center <?php echo $is_default ? 'active' : ''; ?>"
                                     data-duration="<?php echo htmlspecialchars($rate['duration_type']); ?>"
                                     data-price="<?php echo $rate['price']; ?>"
                                     data-price-formatted="<?php echo formatRupiah($rate['price']); ?>"
                                     data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                                     data-wa-num="<?php echo SITE_WA_INT; ?>">
                                    <span class="block text-[11px] font-semibold"><?php echo htmlspecialchars($rate['duration_type']); ?></span>
                                    <span class="block text-xs font-extrabold mt-0.5"><?php echo formatRupiah($rate['price']); ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Price Highlight & WA Action Button -->
                        <div class="pt-4 border-t border-slate-100 space-y-4">
                            
                            <div class="bg-slate-50 p-3 rounded-xl flex items-center justify-between border border-slate-200">
                                <span class="text-xs text-slate-500 font-semibold">Total Biaya (<span id="selectedDurationText"><?php echo htmlspecialchars($initial_rate['duration_type']); ?></span>):</span>
                                <span id="selectedDurationPrice" class="text-xl font-extrabold text-navybrand-800">
                                    <?php echo formatRupiah($initial_rate['price']); ?>
                                </span>
                            </div>

                            <!-- WhatsApp Direct Booking Button -->
                            <?php 
                                $waText = "Halo Admin Sewa Kamera Malang, saya mau sewa " . $product['name'] . " untuk durasi " . $initial_rate['duration_type'];
                                $waUrl = "https://wa.me/" . SITE_WA_INT . "?text=" . rawurlencode($waText);
                            ?>
                            <a id="btnChatWa" href="<?php echo $waUrl; ?>" target="_blank" class="w-full bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white font-extrabold py-3.5 px-6 rounded-2xl shadow-lg transition-all flex items-center justify-center space-x-2 btn-wa-pulse">
                                <i class="fa-brands fa-whatsapp text-2xl"></i>
                                <span class="text-sm">Chat Admin via WhatsApp</span>
                            </a>
                            
                            <p class="text-[11px] text-slate-400 text-center flex items-center justify-center gap-1">
                                <i class="fa-solid fa-circle-info text-skybrand-400"></i>
                                <span>Pesan otomatis terformat. Admin siap melayani 24/7.</span>
                            </p>
                        </div>

                    </div>

                </div>

                <!-- Terms & Guarantees Note -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200 space-y-4 shadow-sm">
                    <h3 class="font-bold text-navybrand-800 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-file-contract text-skybrand-500"></i>
                        <span>Syarat Singkat Pengambilan Unit (Malang Store):</span>
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs text-slate-600">
                        <div class="flex items-start gap-2.5">
                            <i class="fa-solid fa-id-card text-skybrand-500 mt-0.5"></i>
                            <span>Wajib menjaminkan minimal 2 Identitas Asli (KTP / SIM / KTM / NPWP).</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="fa-solid fa-user-check text-skybrand-500 mt-0.5"></i>
                            <span>Pemeriksaan fisik & kelengkapan unit bersama sebelum dibawa.</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="fa-solid fa-handshake text-skybrand-500 mt-0.5"></i>
                            <span>Pengambilan & pengembalian sesuai jam sewa di Store Malang.</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- RIGHT SIDEBAR (Column 4 of 12) -->
            <div class="lg:col-span-4 space-y-6">
                
                <!-- Box 1: Daftar Kategori Lengkap -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm space-y-4">
                    <h3 class="font-extrabold text-navybrand-800 text-base border-b border-slate-100 pb-3 flex items-center justify-between">
                        <span>Kategori Alat</span>
                        <i class="fa-solid fa-layer-group text-skybrand-400 text-sm"></i>
                    </h3>
                    <ul class="space-y-1.5 text-xs font-semibold">
                        <?php foreach ($side_categories as $scat): ?>
                        <li>
                            <a href="index.php?category=<?php echo urlencode($scat['slug']); ?>#katalog" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-skybrand-50 hover:text-skybrand-600 transition-colors text-slate-700">
                                <span class="flex items-center gap-2">
                                    <i class="fa-solid <?php echo htmlspecialchars($scat['icon']); ?> text-skybrand-400 w-4 text-center"></i>
                                    <span><?php echo htmlspecialchars($scat['name']); ?></span>
                                </span>
                                <span class="bg-slate-100 text-slate-500 text-[10px] px-2 py-0.5 rounded-full font-bold">
                                    <?php echo $scat['total_items']; ?>
                                </span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Box 2: Top Rated & Best Seller Products -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm space-y-4">
                    <h3 class="font-extrabold text-navybrand-800 text-base border-b border-slate-100 pb-3 flex items-center justify-between">
                        <span>Best Seller Lainnya</span>
                        <i class="fa-solid fa-fire text-amber-500 text-sm"></i>
                    </h3>
                    <div class="space-y-4">
                        <?php foreach ($top_products as $titem): ?>
                        <a href="product-detail.php?id=<?php echo $titem['id']; ?>" class="flex items-center space-x-3 group">
                            <div class="w-16 h-16 bg-white border border-slate-200 rounded-xl p-1.5 flex-shrink-0 flex items-center justify-center">
                                <img src="<?php echo htmlspecialchars($titem['image_url']); ?>" alt="<?php echo htmlspecialchars($titem['name']); ?>" class="max-h-12 object-contain group-hover:scale-105 transition-transform">
                            </div>
                            <div class="flex-grow min-w-0">
                                <h4 class="font-bold text-xs text-navybrand-800 truncate group-hover:text-skybrand-500 transition-colors">
                                    <?php echo htmlspecialchars($titem['name']); ?>
                                </h4>
                                <span class="text-[10px] font-semibold text-slate-400 block mt-0.5">
                                    <?php echo htmlspecialchars($titem['brand']); ?>
                                </span>
                                <span class="text-xs font-extrabold text-skybrand-600 block mt-0.5">
                                    <?php echo formatRupiah($titem['price_24h']); ?> <span class="text-[9px] font-normal text-slate-400">/24j</span>
                                </span>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Box 3: Quick WA Help -->
                <div class="bg-gradient-to-br from-navybrand-900 to-navybrand-800 rounded-2xl p-6 text-white space-y-3 shadow-md">
                    <h4 class="font-bold text-base flex items-center gap-2">
                        <i class="fa-solid fa-headset text-skybrand-300"></i>
                        <span>Butuh Konsultasi?</span>
                    </h4>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Tim admin Sewa Kamera Malang siap membantu mengecek ketersediaan unit untuk tanggal acara kamu.
                    </p>
                    <a href="<?php echo getWaLink(); ?>" target="_blank" class="w-full inline-flex items-center justify-center space-x-2 bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-2.5 px-4 rounded-xl text-xs shadow transition-colors">
                        <i class="fa-brands fa-whatsapp text-lg"></i>
                        <span>Hubungi Admin Toko</span>
                    </a>
                </div>

            </div>

        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
