<?php
$page_title = "Kelola Kategori Alat";
require_once __DIR__ . '/includes/header.php';

$message = '';
$error = '';

// Handle Delete Category
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $del_id = (int)$_GET['id'];
    try {
        $stmtDel = $pdo->prepare("DELETE FROM categories WHERE id = :id");
        $stmtDel->execute([':id' => $del_id]);
        $message = "Kategori berhasil dihapus.";
    } catch (PDOException $e) {
        $error = "Gagal menghapus kategori (pastikan tidak ada produk yang menggunakan kategori ini).";
    }
}

// Handle Add / Edit Category
$edit_category = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $edit_id = (int)$_GET['id'];
    $stmtE = $pdo->prepare("SELECT * FROM categories WHERE id = :id");
    $stmtE->execute([':id' => $edit_id]);
    $edit_category = $stmtE->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cat_name = trim($_POST['name']);
    $cat_icon = trim($_POST['icon']);
    $cat_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if (!empty($cat_name)) {
        // Generate Slug
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $cat_name), '-'));
        
        if (empty($cat_icon)) {
            $cat_icon = 'fa-camera';
        }

        try {
            if ($cat_id > 0) {
                // Update
                $stmtUp = $pdo->prepare("UPDATE categories SET name = :name, slug = :slug, icon = :icon WHERE id = :id");
                $stmtUp->execute([':name' => $cat_name, ':slug' => $slug, ':icon' => $cat_icon, ':id' => $cat_id]);
                $message = "Kategori berhasil diperbarui!";
            } else {
                // Insert
                $stmtIns = $pdo->prepare("INSERT INTO categories (name, slug, icon) VALUES (:name, :slug, :icon)");
                $stmtIns->execute([':name' => $cat_name, ':slug' => $slug, ':icon' => $cat_icon]);
                $message = "Kategori baru berhasil ditambahkan!";
            }
            $edit_category = null;
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan database (Slug mungkin sudah digunakan): " . $e->getMessage();
        }
    } else {
        $error = "Nama kategori tidak boleh kosong.";
    }
}

// Fetch all categories
$stmtCats = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) as total_products FROM categories c ORDER BY c.name ASC");
$categories = $stmtCats->fetchAll();
?>

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

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    
    <!-- Left Column: Add / Edit Form (Column 5 of 12) -->
    <div class="lg:col-span-5">
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
            <h3 class="font-extrabold text-navybrand-800 text-base border-b border-slate-100 pb-3 flex items-center justify-between">
                <span><?php echo $edit_category ? 'Edit Kategori' : 'Tambah Kategori Baru'; ?></span>
                <i class="fa-solid fa-layer-group text-sky-500 text-sm"></i>
            </h3>

            <form action="categories.php" method="POST" class="space-y-4">
                <?php if ($edit_category): ?>
                <input type="hidden" name="id" value="<?php echo $edit_category['id']; ?>">
                <?php endif; ?>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Nama Kategori <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="<?php echo $edit_category ? htmlspecialchars($edit_category['name']) : ''; ?>" required placeholder="e.g. Mirrorless, Lensa, Drone..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-400">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">FontAwesome Class Icon</label>
                    <input type="text" name="icon" value="<?php echo $edit_category ? htmlspecialchars($edit_category['icon']) : 'fa-camera'; ?>" placeholder="e.g. fa-camera, fa-video, fa-paper-plane" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-400">
                    <span class="text-[10px] text-slate-400 block mt-1">Class icon FontAwesome 6 (e.g. <code>fa-camera</code>, <code>fa-lightbulb</code>).</span>
                </div>

                <div class="pt-2 flex items-center justify-end space-x-2">
                    <?php if ($edit_category): ?>
                    <a href="categories.php" class="bg-slate-100 text-slate-600 font-bold px-4 py-2 rounded-xl text-xs hover:bg-slate-200">Batal</a>
                    <?php endif; ?>
                    
                    <button type="submit" class="bg-sky-500 hover:bg-sky-400 text-slate-950 font-extrabold px-5 py-2.5 rounded-xl text-xs transition-all shadow-md">
                        <?php echo $edit_category ? 'Perbarui Kategori' : 'Simpan Kategori'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Column: Category List Table (Column 7 of 12) -->
    <div class="lg:col-span-7">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100">
                <h3 class="font-extrabold text-navybrand-800 text-base">Daftar Kategori Alat Foto</h3>
                <p class="text-slate-400 text-xs mt-0.5">Kategori menentukan pengelompokan produk di katalog depan.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 font-extrabold uppercase border-b border-slate-200">
                            <th class="py-3.5 px-6">Icon & Kategori</th>
                            <th class="py-3.5 px-4">Slug URL</th>
                            <th class="py-3.5 px-4 text-center">Jumlah Produk</th>
                            <th class="py-3.5 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-semibold text-slate-700">
                        <?php foreach ($categories as $cat): ?>
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-4 px-6 flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center text-sm font-bold">
                                    <i class="fa-solid <?php echo htmlspecialchars($cat['icon']); ?>"></i>
                                </div>
                                <span class="font-extrabold text-navybrand-800 text-sm"><?php echo htmlspecialchars($cat['name']); ?></span>
                            </td>

                            <td class="py-4 px-4 text-slate-500 font-mono text-[11px]"><?php echo htmlspecialchars($cat['slug']); ?></td>

                            <td class="py-4 px-4 text-center">
                                <span class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-full text-[10px] font-bold">
                                    <?php echo $cat['total_products']; ?> Unit
                                </span>
                            </td>

                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="categories.php?action=edit&id=<?php echo $cat['id']; ?>" class="bg-sky-50 hover:bg-sky-500 hover:text-white text-sky-600 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-colors">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <a href="categories.php?action=delete&id=<?php echo $cat['id']; ?>" onclick="return confirm('Hapus kategori ini?');" class="bg-red-50 hover:bg-red-500 hover:text-white text-red-600 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-colors">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
