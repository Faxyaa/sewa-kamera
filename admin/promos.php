<?php
$page_title = "Kelola Banner & Promo Diskon";
require_once __DIR__ . '/includes/header.php';

$message = '';
$error = '';

// Handle Delete Promo
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $del_id = (int)$_GET['id'];
    try {
        $stmtDel = $pdo->prepare("DELETE FROM promos WHERE id = :id");
        $stmtDel->execute([':id' => $del_id]);
        $message = "Banner promo berhasil dihapus.";
    } catch (PDOException $e) {
        $error = "Gagal menghapus promo: " . $e->getMessage();
    }
}

// Handle Add / Edit Promo
$edit_promo = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $edit_id = (int)$_GET['id'];
    $stmtE = $pdo->prepare("SELECT * FROM promos WHERE id = :id");
    $stmtE->execute([':id' => $edit_id]);
    $edit_promo = $stmtE->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $discount_info = trim($_POST['discount_info']);
    $banner_image_input = trim($_POST['banner_image']);
    $button_text = trim($_POST['button_text']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $promo_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    $final_banner_image = $edit_promo ? $edit_promo['banner_image'] : '';

    // Upload file if provided
    if (isset($_FILES['banner_file']) && $_FILES['banner_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['banner_file']['tmp_name'];
        $fileName = $_FILES['banner_file']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($fileExtension, $allowedExtensions)) {
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $newFileName = 'promo_' . time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $final_banner_image = 'uploads/' . $newFileName;
            }
        }
    } elseif (!empty($banner_image_input)) {
        $final_banner_image = $banner_image_input;
    }

    if (empty($title) || empty($discount_info) || empty($final_banner_image)) {
        $error = "Harap isi judul promo, info diskon, dan gambar banner.";
    } else {
        if (empty($button_text)) $button_text = "Sewa Sekarang";

        try {
            if ($promo_id > 0) {
                // Update
                $stmtUp = $pdo->prepare("UPDATE promos SET title = :t, description = :d, discount_info = :di, banner_image = :img, button_text = :bt, is_active = :ia WHERE id = :id");
                $stmtUp->execute([
                    ':t' => $title, ':d' => $description, ':di' => $discount_info,
                    ':img' => $final_banner_image, ':bt' => $button_text, ':ia' => $is_active, ':id' => $promo_id
                ]);
                $message = "Banner promo berhasil diperbarui!";
            } else {
                // Insert
                $stmtIns = $pdo->prepare("INSERT INTO promos (title, description, discount_info, banner_image, button_text, is_active) VALUES (:t, :d, :di, :img, :bt, :ia)");
                $stmtIns->execute([
                    ':t' => $title, ':d' => $description, ':di' => $discount_info,
                    ':img' => $final_banner_image, ':bt' => $button_text, ':ia' => $is_active
                ]);
                $message = "Banner promo baru berhasil ditambahkan!";
            }
            $edit_promo = null;
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan database: " . $e->getMessage();
        }
    }
}

// Fetch all promos
$promos = $pdo->query("SELECT * FROM promos ORDER BY id DESC")->fetchAll();
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
    
    <!-- Left Form (Column 5 of 12) -->
    <div class="lg:col-span-5">
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
            <h3 class="font-extrabold text-navybrand-800 text-base border-b border-slate-100 pb-3 flex items-center justify-between">
                <span><?php echo $edit_promo ? 'Edit Banner Promo' : 'Tambah Promo Baru'; ?></span>
                <i class="fa-solid fa-images text-sky-500 text-sm"></i>
            </h3>

            <form action="promos.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <?php if ($edit_promo): ?>
                <input type="hidden" name="id" value="<?php echo $edit_promo['id']; ?>">
                <?php endif; ?>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Judul Promo <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="<?php echo $edit_promo ? htmlspecialchars($edit_promo['title']) : ''; ?>" required placeholder="e.g. PROMO WEEKDAY DISKON 15%" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-400">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Badge Info Diskon <span class="text-red-500">*</span></label>
                    <input type="text" name="discount_info" value="<?php echo $edit_promo ? htmlspecialchars($edit_promo['discount_info']) : ''; ?>" required placeholder="e.g. DISKON 15% / GRATIS 1 HARI" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-400">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Gambar Banner (URL / Upload)</label>
                    <input type="url" name="banner_image" value="<?php echo $edit_promo ? htmlspecialchars($edit_promo['banner_image']) : ''; ?>" placeholder="https://images.unsplash.com/..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-400">
                    
                    <input type="file" name="banner_file" accept="image/*" class="mt-2 block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sky-50 file:text-sky-600">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Deskripsi Syarat Promo</label>
                    <textarea name="description" rows="3" placeholder="Tuliskan keterangan promo..." class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-400"><?php echo $edit_promo ? htmlspecialchars($edit_promo['description']) : ''; ?></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Teks Tombol WA</label>
                    <input type="text" name="button_text" value="<?php echo $edit_promo ? htmlspecialchars($edit_promo['button_text']) : 'Klaim Promo WA'; ?>" placeholder="Klaim Promo WA" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-400">
                </div>

                <div class="pt-1">
                    <label class="inline-flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" <?php echo (!$edit_promo || $edit_promo['is_active'] == 1) ? 'checked' : ''; ?> class="rounded text-emerald-500 focus:ring-emerald-400 w-4 h-4">
                        <span class="text-xs font-bold text-slate-700">Tampilkan di Slider Beranda (Aktif)</span>
                    </label>
                </div>

                <div class="pt-2 flex items-center justify-end space-x-2">
                    <?php if ($edit_promo): ?>
                    <a href="promos.php" class="bg-slate-100 text-slate-600 font-bold px-4 py-2 rounded-xl text-xs hover:bg-slate-200">Batal</a>
                    <?php endif; ?>
                    
                    <button type="submit" class="bg-sky-500 hover:bg-sky-400 text-slate-950 font-extrabold px-5 py-2.5 rounded-xl text-xs transition-all shadow-md">
                        <?php echo $edit_promo ? 'Perbarui Promo' : 'Simpan Promo'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Right List (Column 7 of 12) -->
    <div class="lg:col-span-7 space-y-4">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
            <h3 class="font-extrabold text-navybrand-800 text-base mb-4">Daftar Banner Promo Slider</h3>

            <div class="space-y-4">
                <?php foreach ($promos as $pr): ?>
                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center space-x-4">
                        <img src="<?php echo htmlspecialchars($pr['banner_image']); ?>" alt="<?php echo htmlspecialchars($pr['title']); ?>" class="w-20 h-14 object-cover rounded-xl border border-slate-200 flex-shrink-0">
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="bg-red-100 text-red-700 text-[10px] font-extrabold px-2 py-0.5 rounded">
                                    <?php echo htmlspecialchars($pr['discount_info']); ?>
                                </span>
                                <?php if ($pr['is_active']): ?>
                                <span class="bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded">Aktif</span>
                                <?php else: ?>
                                <span class="bg-slate-200 text-slate-600 text-[10px] font-bold px-2 py-0.5 rounded">Nonaktif</span>
                                <?php endif; ?>
                            </div>
                            <h4 class="font-extrabold text-navybrand-800 text-sm mt-1"><?php echo htmlspecialchars($pr['title']); ?></h4>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <a href="promos.php?action=edit&id=<?php echo $pr['id']; ?>" class="bg-sky-50 hover:bg-sky-500 hover:text-white text-sky-600 px-3 py-1.5 rounded-lg text-xs font-bold transition-colors">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <a href="promos.php?action=delete&id=<?php echo $pr['id']; ?>" onclick="return confirm('Hapus promo ini?');" class="bg-red-50 hover:bg-red-500 hover:text-white text-red-600 px-3 py-1.5 rounded-lg text-xs font-bold transition-colors">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
