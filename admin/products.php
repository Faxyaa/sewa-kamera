<?php
$page_title = "Kelola Katalog Produk & Harga";
require_once __DIR__ . '/includes/header.php';

$message = '';
$error = '';

// Handle Delete Request
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delete_id = (int)$_GET['id'];
    try {
        $stmtDel = $pdo->prepare("DELETE FROM products WHERE id = :id");
        $stmtDel->execute([':id' => $delete_id]);
        $message = "Produk berhasil dihapus dari database.";
    } catch (PDOException $e) {
        $error = "Gagal menghapus produk: " . $e->getMessage();
    }
}

// Search & Filter Query
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

$sql = "SELECT p.*, c.name as category_name,
        (SELECT price FROM rental_rates WHERE product_id = p.id AND duration_type = '6 Jam' LIMIT 1) as price_6h,
        (SELECT price FROM rental_rates WHERE product_id = p.id AND duration_type = '12 Jam' LIMIT 1) as price_12h,
        (SELECT price FROM rental_rates WHERE product_id = p.id AND duration_type = '24 Jam' LIMIT 1) as price_24h
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE 1=1";

$params = [];

if (!empty($search)) {
    $sql .= " AND (p.name LIKE :search OR p.brand LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

if ($category_id > 0) {
    $sql .= " AND p.category_id = :cid";
    $params[':cid'] = $category_id;
}

$sql .= " ORDER BY p.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Fetch categories for filter dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>

<!-- Header Toolbar & Add Button -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-xl font-extrabold text-navybrand-800">Daftar Produk & Tarif Sewa</h2>
        <p class="text-xs text-slate-500 mt-0.5">Kelola data kamera, lensa, aksesoris, dan harga durasi 6j/12j/24j.</p>
    </div>
    
    <a href="product-form.php" class="bg-sky-500 hover:bg-sky-400 text-slate-950 font-extrabold px-5 py-3 rounded-xl text-xs transition-all shadow-lg flex items-center justify-center space-x-2">
        <i class="fa-solid fa-plus text-sm"></i>
        <span>Tambah Produk Baru</span>
    </a>
</div>

<!-- Alert Messages -->
<?php if (!empty($message)): ?>
<div class="bg-emerald-50 border border-emerald-200 text-emerald-700 p-4 rounded-xl text-xs mb-6 flex items-center gap-2">
    <i class="fa-solid fa-circle-check text-base"></i>
    <span><?php echo htmlspecialchars($message); ?></span>
</div>
<?php endif; ?>

<?php if (!empty($error)): ?>
<div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl text-xs mb-6 flex items-center gap-2">
    <i class="fa-solid fa-triangle-exclamation text-base"></i>
    <span><?php echo htmlspecialchars($error); ?></span>
</div>
<?php endif; ?>

<!-- Filter & Search Bar -->
<div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm mb-6 flex flex-col md:flex-row gap-3">
    <form action="products.php" method="GET" class="flex-grow flex flex-col sm:flex-row gap-3">
        <div class="flex-grow relative">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari nama produk / brand..." class="w-full pl-9 pr-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-400">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400 text-xs"></i>
        </div>

        <select name="category_id" class="py-2.5 px-4 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-400 font-semibold text-slate-700">
            <option value="0">Semua Kategori</option>
            <?php foreach ($categories as $c): ?>
            <option value="<?php echo $c['id']; ?>" <?php echo ($category_id == $c['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($c['name']); ?>
            </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="bg-navybrand-800 hover:bg-navybrand-900 text-white font-bold px-5 py-2.5 rounded-xl text-xs transition-colors">
            Filter
        </button>
    </form>
    
    <?php if (!empty($search) || $category_id > 0): ?>
    <a href="products.php" class="bg-slate-100 text-slate-600 font-bold px-4 py-2.5 rounded-xl text-xs hover:bg-slate-200 flex items-center justify-center">
        Reset
    </a>
    <?php endif; ?>
</div>

<!-- Product Table -->
<div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-slate-50 text-slate-500 font-extrabold uppercase border-b border-slate-200">
                    <th class="py-3.5 px-6">Foto & Nama Produk</th>
                    <th class="py-3.5 px-4">Kategori</th>
                    <th class="py-3.5 px-4 text-center">Tarif 6 Jam</th>
                    <th class="py-3.5 px-4 text-center">Tarif 12 Jam</th>
                    <th class="py-3.5 px-4 text-center">Tarif 24 Jam</th>
                    <th class="py-3.5 px-4 text-center">Badges</th>
                    <th class="py-3.5 px-6 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-semibold text-slate-700">
                <?php if (!empty($products)): ?>
                <?php foreach ($products as $p): ?>
                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="py-4 px-6 flex items-center space-x-3">
                        <img src="<?php echo htmlspecialchars($p['image_url']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" class="w-12 h-12 object-contain bg-white border border-slate-200 rounded-xl p-0.5 shadow-sm">
                        <div>
                            <span class="font-extrabold text-navybrand-800 block text-sm"><?php echo htmlspecialchars($p['name']); ?></span>
                            <span class="text-[10px] text-slate-400 font-bold uppercase">Brand: <?php echo htmlspecialchars($p['brand']); ?></span>
                        </div>
                    </td>

                    <td class="py-4 px-4 text-slate-600">
                        <span class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-md text-[10px] font-bold">
                            <?php echo htmlspecialchars($p['category_name']); ?>
                        </span>
                    </td>

                    <td class="py-4 px-4 text-center font-bold text-slate-600">
                        <?php echo !empty($p['price_6h']) ? formatRupiah($p['price_6h']) : '-'; ?>
                    </td>

                    <td class="py-4 px-4 text-center font-bold text-slate-600">
                        <?php echo !empty($p['price_12h']) ? formatRupiah($p['price_12h']) : '-'; ?>
                    </td>

                    <td class="py-4 px-4 text-center font-extrabold text-sky-600 text-sm">
                        <?php echo !empty($p['price_24h']) ? formatRupiah($p['price_24h']) : '-'; ?>
                    </td>

                    <td class="py-4 px-4 text-center">
                        <div class="flex items-center justify-center space-x-1">
                            <?php if ($p['is_bestseller']): ?>
                            <span class="bg-amber-100 text-amber-800 text-[10px] font-extrabold px-2 py-0.5 rounded">Best</span>
                            <?php endif; ?>
                            <?php if ($p['is_new']): ?>
                            <span class="bg-emerald-100 text-emerald-800 text-[10px] font-extrabold px-2 py-0.5 rounded">New</span>
                            <?php endif; ?>
                        </div>
                    </td>

                    <td class="py-4 px-6 text-right">
                        <div class="flex items-center justify-end space-x-2">
                            <a href="product-form.php?id=<?php echo $p['id']; ?>" class="bg-sky-50 hover:bg-sky-500 hover:text-white text-sky-600 px-3 py-1.5 rounded-lg text-xs font-bold transition-colors" title="Edit Produk & Harga">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <a href="products.php?action=delete&id=<?php echo $p['id']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?');" class="bg-red-50 hover:bg-red-500 hover:text-white text-red-600 px-3 py-1.5 rounded-lg text-xs font-bold transition-colors" title="Hapus Produk">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center py-12 text-slate-400">
                        <i class="fa-solid fa-inbox text-3xl mb-2 block"></i>
                        Tidak ada data produk ditemukan.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
