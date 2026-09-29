<?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_edit = ($id > 0);
$page_title = $is_edit ? "Edit Produk & Harga" : "Tambah Produk Baru";

require_once __DIR__ . '/includes/header.php';

$error = '';
$success = '';

// Default values
$product = [
    'category_id' => '',
    'name' => '',
    'brand' => '',
    'image_url' => '',
    'description' => '',
    'kelengkapan' => '',
    'is_new' => 0,
    'is_bestseller' => 0,
    'is_active' => 1
];

$rates = [
    '6 Jam' => 0,
    '12 Jam' => 0,
    '24 Jam' => 0
];

// If editing, load product & rates
if ($is_edit) {
    $stmtP = $pdo->prepare("SELECT * FROM products WHERE id = :id");
    $stmtP->execute([':id' => $id]);
    $existing = $stmtP->fetch();

    if ($existing) {
        $product = $existing;
        
        // Fetch existing rates
        $stmtR = $pdo->prepare("SELECT duration_type, price FROM rental_rates WHERE product_id = :pid");
        $stmtR->execute([':pid' => $id]);
        $existingRates = $stmtR->fetchAll(PDO::FETCH_KEY_PAIR);

        if (!empty($existingRates)) {
            foreach ($existingRates as $dur => $pr) {
                $rates[$dur] = $pr;
            }
        }
    } else {
        header("Location: products.php");
        exit;
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = (int)$_POST['category_id'];
    $name = trim($_POST['name']);
    $brand = trim($_POST['brand']);
    $image_url_input = trim($_POST['image_url']);
    $description = trim($_POST['description']);
    $kelengkapan = trim($_POST['kelengkapan']);
    $is_new = isset($_POST['is_new']) ? 1 : 0;
    $is_bestseller = isset($_POST['is_bestseller']) ? 1 : 0;

    $price_6h = (float)$_POST['price_6h'];
    $price_12h = (float)$_POST['price_12h'];
    $price_24h = (float)$_POST['price_24h'];

    // Handle File Upload if provided
    $final_image_url = $product['image_url'];
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image_file']['tmp_name'];
        $fileName = $_FILES['image_file']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($fileExtension, $allowedExtensions)) {
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $newFileName = 'prod_' . time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $final_image_url = 'uploads/' . $newFileName;
            }
        }
    } elseif (!empty($image_url_input)) {
        $final_image_url = $image_url_input;
    }

    if (empty($name) || empty($brand) || $category_id <= 0 || empty($final_image_url)) {
        $error = 'Harap isi nama produk, brand, kategori, dan gambar produk.';
    } else {
        try {
            $pdo->beginTransaction();

            if ($is_edit) {
                // Update Product
                $stmtUp = $pdo->prepare("UPDATE products SET 
                    category_id = :cid, name = :name, brand = :brand, image_url = :img, 
                    description = :desc, kelengkapan = :kel, is_new = :is_new, is_bestseller = :is_bs
                    WHERE id = :id");
                $stmtUp->execute([
                    ':cid' => $category_id,
                    ':name' => $name,
                    ':brand' => $brand,
                    ':img' => $final_image_url,
                    ':desc' => $description,
                    ':kel' => $kelengkapan,
                    ':is_new' => $is_new,
                    ':is_bs' => $is_bestseller,
                    ':id' => $id
                ]);
                $product_id = $id;
            } else {
                // Insert Product
                $stmtIns = $pdo->prepare("INSERT INTO products 
                    (category_id, name, brand, image_url, description, kelengkapan, is_new, is_bestseller, is_active)
                    VALUES (:cid, :name, :brand, :img, :desc, :kel, :is_new, :is_bs, 1)");
                $stmtIns->execute([
                    ':cid' => $category_id,
                    ':name' => $name,
                    ':brand' => $brand,
                    ':img' => $final_image_url,
                    ':desc' => $description,
                    ':kel' => $kelengkapan,
                    ':is_new' => $is_new,
                    ':is_bs' => $is_bestseller
                ]);
                $product_id = $pdo->lastInsertId();
            }

            // Update/Insert Rental Rates
            $rates_to_save = [
                '6 Jam' => $price_6h,
                '12 Jam' => $price_12h,
                '24 Jam' => $price_24h
            ];

            // Delete old rates
            $pdo->prepare("DELETE FROM rental_rates WHERE product_id = :pid")->execute([':pid' => $product_id]);

            // Insert new rates
            $stmtRate = $pdo->prepare("INSERT INTO rental_rates (product_id, duration_type, price) VALUES (:pid, :dur, :price)");
            foreach ($rates_to_save as $durType => $durPrice) {
                if ($durPrice > 0) {
                    $stmtRate->execute([
                        ':pid' => $product_id,
                        ':dur' => $durType,
                        ':price' => $durPrice
                    ]);
                }
            }

            $pdo->commit();

            $_SESSION['flash_msg'] = "Data produk dan tarif harga berhasil disimpan!";
            header("Location: products.php");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Terjadi kesalahan database: " . $e->getMessage();
        }
    }
}

// Fetch categories for select dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>

<!-- Form Container -->
<div class="max-w-4xl mx-auto">
    
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-extrabold text-navybrand-800"><?php echo $page_title; ?></h2>
            <p class="text-xs text-slate-500 mt-0.5">Isi rincian informasi spesifikasi dan tarif sewa per durasi.</p>
        </div>
        <a href="products.php" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold px-4 py-2.5 rounded-xl text-xs transition-colors">
            &larr; Kembali
        </a>
    </div>

    <?php if (!empty($error)): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl text-xs mb-6 flex items-center gap-2">
        <i class="fa-solid fa-triangle-exclamation text-base"></i>
        <span><?php echo htmlspecialchars($error); ?></span>
    </div>
    <?php endif; ?>

    <form action="product-form.php<?php echo $is_edit ? '?id=' . $id : ''; ?>" method="POST" enctype="multipart/form-data" class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-8">
        
        <!-- SECTION 1: Informasi Utama -->
        <div class="space-y-4">
            <h3 class="font-extrabold text-navybrand-800 text-sm border-b border-slate-100 pb-2 uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-circle-info text-sky-500"></i>
                <span>1. Informasi Produk</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Nama Produk -->
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Nama Produk <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required placeholder="Contoh: Sony Alpha A7 III Body Only" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-400">
                </div>

                <!-- Brand / Merk -->
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Brand / Merk <span class="text-red-500">*</span></label>
                    <input type="text" name="brand" value="<?php echo htmlspecialchars($product['brand']); ?>" required placeholder="Contoh: Sony / Canon / Fujifilm / Godox" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-400">
                </div>
            </div>

            <!-- Kategori Select -->
            <div>
                <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Kategori Alat <span class="text-red-500">*</span></label>
                <select name="category_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-400">
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo ($product['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- SECTION 2: Gambar Produk (URL / Upload) -->
        <div class="space-y-4 pt-4 border-t border-slate-100">
            <h3 class="font-extrabold text-navybrand-800 text-sm border-b border-slate-100 pb-2 uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-image text-sky-500"></i>
                <span>2. Foto Produk</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">URL Foto (External Link Unsplash/CDN)</label>
                    <input type="url" name="image_url" value="<?php echo htmlspecialchars($product['image_url']); ?>" placeholder="https://images.unsplash.com/..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-400">
                    
                    <span class="text-[11px] text-slate-400 block mt-2 font-medium">Atau Upload Gambar dari Komputer:</span>
                    <input type="file" name="image_file" accept="image/*" class="mt-1 block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sky-50 file:text-sky-600 hover:file:bg-sky-100">
                </div>

                <!-- Image Preview Box -->
                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 h-36 flex items-center justify-center">
                    <?php if (!empty($product['image_url'])): ?>
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" class="max-h-28 object-contain">
                    <?php else: ?>
                    <span class="text-xs text-slate-400 font-semibold">Preview Gambar</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- SECTION 3: Tarif Sewa Per Durasi -->
        <div class="space-y-4 pt-4 border-t border-slate-100">
            <h3 class="font-extrabold text-navybrand-800 text-sm border-b border-slate-100 pb-2 uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-money-bill-wave text-emerald-500"></i>
                <span>3. Atur Tarif Sewa (Rupiah)</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Harga 6 Jam</label>
                    <input type="number" name="price_6h" value="<?php echo $rates['6 Jam']; ?>" placeholder="e.g. 120000" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-800 font-bold focus:outline-none focus:ring-2 focus:ring-sky-400">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Harga 12 Jam</label>
                    <input type="number" name="price_12h" value="<?php echo $rates['12 Jam']; ?>" placeholder="e.g. 170000" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-800 font-bold focus:outline-none focus:ring-2 focus:ring-sky-400">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Harga 24 Jam <span class="text-red-500">*</span></label>
                    <input type="number" name="price_24h" value="<?php echo $rates['24 Jam']; ?>" required placeholder="e.g. 230000" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-sky-600 font-extrabold focus:outline-none focus:ring-2 focus:ring-sky-400">
                </div>
            </div>
        </div>

        <!-- SECTION 4: Deskripsi & Kelengkapan -->
        <div class="space-y-4 pt-4 border-t border-slate-100">
            <h3 class="font-extrabold text-navybrand-800 text-sm border-b border-slate-100 pb-2 uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-list-check text-sky-500"></i>
                <span>4. Deskripsi & Kelengkapan Paket</span>
            </h3>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Kelengkapan Paket (Pisahkan dengan Koma)</label>
                <input type="text" name="kelengkapan" value="<?php echo htmlspecialchars($product['kelengkapan']); ?>" placeholder="e.g. Body Sony A7 III, 2x Baterai, Charger, SD Card 64GB, Tas Kamera" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-400">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Deskripsi Singkat Spesifikasi</label>
                <textarea name="description" rows="3" placeholder="Tuliskan deskripsi ringkas fitur kamera atau keunggulannya..." class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-400"><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
        </div>

        <!-- SECTION 5: Badges & Highlight -->
        <div class="space-y-3 pt-4 border-t border-slate-100">
            <h3 class="font-extrabold text-navybrand-800 text-sm pb-1 uppercase tracking-wider">5. Highlight Label Badges</h3>
            
            <div class="flex items-center space-x-6">
                <label class="inline-flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="is_bestseller" value="1" <?php echo ($product['is_bestseller'] == 1) ? 'checked' : ''; ?> class="rounded text-amber-500 focus:ring-amber-400 w-4 h-4">
                    <span class="text-xs font-bold text-slate-700">Tampilkan Badge <strong class="text-amber-600">BEST SELLER</strong></span>
                </label>

                <label class="inline-flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="is_new" value="1" <?php echo ($product['is_new'] == 1) ? 'checked' : ''; ?> class="rounded text-emerald-500 focus:ring-emerald-400 w-4 h-4">
                    <span class="text-xs font-bold text-slate-700">Tampilkan Badge <strong class="text-emerald-600">NEW ARRIVAL</strong></span>
                </label>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="pt-6 border-t border-slate-100 flex items-center justify-end space-x-3">
            <a href="products.php" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold px-5 py-3 rounded-xl text-xs transition-colors">
                Batal
            </a>
            <button type="submit" class="bg-sky-500 hover:bg-sky-400 text-slate-950 font-extrabold px-6 py-3 rounded-xl text-xs transition-all shadow-lg shadow-sky-500/20 flex items-center space-x-2">
                <i class="fa-solid fa-floppy-disk text-sm"></i>
                <span>Simpan Produk</span>
            </button>
        </div>

    </form>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
