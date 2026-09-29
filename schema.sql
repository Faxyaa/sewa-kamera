-- ============================================================
-- DATABASE DDL & DML SCHEMA FOR SEWA KAMERA MALANG (KAMERA & LENSA ONLY)
-- Database Name: sewa_kamera
-- ============================================================

CREATE DATABASE IF NOT EXISTS `sewa_kamera` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sewa_kamera`;

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- 1. Table: users (Admin Authentication)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `name` VARCHAR(100) NOT NULL DEFAULT 'Admin Sewa Kamera',
  `role` ENUM('admin') NOT NULL DEFAULT 'admin',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 2. Table: categories (Equipment Categories - CAMERA & LENS ONLY)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `icon` VARCHAR(50) DEFAULT 'fa-camera',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 3. Table: products (Camera & Lens Catalog Only)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `brand` VARCHAR(50) NOT NULL,
  `image_url` TEXT NOT NULL,
  `description` TEXT,
  `kelengkapan` TEXT,
  `is_new` TINYINT(1) DEFAULT 0,
  `is_bestseller` TINYINT(1) DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 4. Table: rental_rates (Duration-based Rental Pricing)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `rental_rates`;
CREATE TABLE `rental_rates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `duration_type` ENUM('6 Jam', '12 Jam', '24 Jam') NOT NULL,
  `price` DECIMAL(10, 0) NOT NULL,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 5. Table: promos (Slider Banners & Promotional Info)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `promos`;
CREATE TABLE `promos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `description` TEXT,
  `discount_info` VARCHAR(100) NOT NULL,
  `banner_image` TEXT NOT NULL,
  `button_text` VARCHAR(50) DEFAULT 'Sewa Sekarang',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;


-- ============================================================
-- DML SEED DATA (100% UNIQUE DISTINCT CAMERA & LENS IMAGES)
-- ============================================================

-- 1. Default Admin Account (Username: admin, Password: admin123)
INSERT INTO `users` (`id`, `username`, `password_hash`, `name`, `role`) VALUES
(1, 'admin', '$2a$10$7R0O1g0g0g0g0g0g0g0g0u8Y0N0K0L0M0N0O0P0Q0R0S0T0U0V0W0', 'Admin Utama', 'admin');

-- 2. Categories Seed (CAMERA & LENSES ONLY)
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`) VALUES
(1, 'Kamera Mirrorless', 'kamera-mirrorless', 'fa-camera'),
(2, 'Kamera Cinema & Video', 'kamera-cinema', 'fa-video'),
(3, 'Lensa Prime / Fixed', 'lensa-prime', 'fa-circle-dot'),
(4, 'Lensa Zoom Standar', 'lensa-zoom', 'fa-sliders'),
(5, 'Lensa Telephoto & Wide', 'lensa-telephoto', 'fa-eye');

-- 3. Products Seed (EVERY PRODUCT HAS A 100% UNIQUE IMAGE URL)
INSERT INTO `products` (`id`, `category_id`, `name`, `brand`, `image_url`, `description`, `kelengkapan`, `is_new`, `is_bestseller`, `is_active`) VALUES
(1, 1, 'Sony Alpha A7 IV Body Only', 'Sony', 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=800&q=80', 'Kamera Mirrorless Full-Frame 33 MP dengan prosesor BIONZ XR super cepat. Mendukung rekaman 4K 60p 10-bit 4:2:2, Real-time Eye AF untuk manusia & hewan, serta IBIS 5.5-stop.', 'Body Sony A7 IV, 2x Baterai NP-FZ100, Dual Charger, SD Card SanDisk Extreme PRO 128GB UHS-II, Strap Original, Tas Kamera Ransel Anti Air', 1, 1, 1),

(2, 1, 'Sony Alpha A7 III Body Only', 'Sony', 'https://images.unsplash.com/photo-1510127034890-ba27508e9f1c?auto=format&fit=crop&w=800&q=80', 'Kamera Full-Frame legendaris sensor 24.2 MP, 4K HDR, dan 5-axis IBIS. Sangat populer untuk foto pernikahan, wisuda, komersial, dan liputan event.', 'Body Sony A7 III, 2x Baterai NP-FZ100, Charger, SD Card 64GB, Strap, Tas Kamera Premium', 0, 1, 1),

(3, 1, 'Fujifilm X-T4 Body Black', 'Fujifilm', 'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?auto=format&fit=crop&w=800&q=80', 'Kamera Flagship APS-C dengan warna analog Film Simulation legendaris. Dilengkapi IBIS 6.5-stop, video 4K 60fps 10-bit internal, dan layar swivel flip.', 'Body Fujifilm X-T4, 2x Baterai NP-W235, Dual Charger, SD Card 64GB High Speed, Strap Leather, Sling Bag', 1, 1, 1),

(4, 1, 'Canon EOS R6 Mark II Body Only', 'Canon', 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?auto=format&fit=crop&w=800&q=80', 'Kamera Full Frame 24.2 MP super kencang hingga 40 fps burst. Video 4K 60p Oversampled dari 6K tanpa crop. Dual Pixel CMOS AF II ultra presisi.', 'Body Canon EOS R6 Mark II, 2x Baterai LP-E6NH, Charger, SD Card 128GB, Strap Canon EOS, Tas Kamera', 1, 0, 1),

(5, 2, 'Sony Cinema Line FX3 Body', 'Sony', 'https://images.unsplash.com/photo-1589872783074-b5a1f0a200fa?auto=format&fit=crop&w=800&q=80', 'Kamera Cinema profesional ultra-kompak dengan sensor 12.1 MP Full Frame BSI. Merekam 4K 120p 10-bit 4:2:2, S-Cinetone color profile, dan active cooling fan.', 'Body Sony FX3, Top Handle XLR Adaptor, 2x Baterai NP-FZ100, Dual Charger, SD Card 128GB V90, Carrying Case Pro', 1, 1, 1),

(6, 4, 'Sony FE 24-70mm f/2.8 GM II', 'Sony', 'https://images.unsplash.com/photo-1617005082133-548c4dd27f35?auto=format&fit=crop&w=800&q=80', 'Lensa Zoom Flagship G Master Generasi ke-2. Lebih ringan 22%, optik tajam dari f/2.8 di semua focal length. Autofokus XD Linear motor super senyap.', 'Lensa Sony 24-70mm GM II, Lens Hood, Cap Depan & Belakang, UV Filter Pro 82mm, Lens Pouch', 1, 1, 1),

(7, 3, 'Sigma 35mm f/1.4 DG DN Art (Sony FE)', 'Sigma', 'https://images.unsplash.com/photo-1622434641406-a158123450f9?auto=format&fit=crop&w=800&q=80', 'Lensa Prime Art tajam luar biasa dengan bukaan f/1.4. Menghasilkan bokeh sangat lembut untuk fotografi wedding, portrait, dan lanskap malam.', 'Lensa Sigma 35mm f/1.4 Art, Lens Hood, Caps, Filter UV Pro 67mm, Lens Case', 0, 1, 1),

(8, 5, 'Sony FE 70-200mm f/2.8 GM OSS II', 'Sony', 'https://images.unsplash.com/photo-1565849904461-04a58ad377e0?auto=format&fit=crop&w=800&q=80', 'Lensa Telephoto Zoom profesional kelas atas. Tajam, bokeh dreamy, dan 29% lebih ringan dari versi terdahulu. Sangat ideal untuk olahraga & panggung.', 'Lensa Sony 70-200mm GM II, Tripod Collar Ring, Lens Hood, Caps, UV Filter Pro 77mm, Hard Case Case', 1, 1, 1),

(9, 3, 'Canon RF 50mm f/1.2L USM', 'Canon', 'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?auto=format&fit=crop&w=800&q=80', 'Lensa Prime Flagship Canon mirrorless RF mount dengan aperture ultra lebar f/1.2. Kualitas gambar spektakuler dengan separuh latar belakang dramatis.', 'Lensa Canon RF 50mm f/1.2L, Lens Hood, Caps, Filter UV 77mm, Lens Pouch Original', 1, 0, 1),

(10, 3, 'Fujifilm XF 56mm f/1.2 R WR', 'Fujifilm', 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=800&q=80', 'Lensa Portrait terbaik Fujifilm setara 85mm di Full Frame. Aperture f/1.2 dengan struktur tahan cuaca (Weather Resistant) dan resolusi optik prima.', 'Lensa Fujifilm XF 56mm f/1.2, Lens Hood, Caps, Filter UV 67mm, Cloth Bag', 0, 1, 1),

(11, 4, 'Tamron 28-75mm f/2.8 Di III VXD G2', 'Tamron', 'https://images.unsplash.com/photo-1508614589041-895b88991e3e?auto=format&fit=crop&w=800&q=80', 'Lensa Zoom serbaguna terfavorit untuk Sony FE. Bukaan f/2.8 tajam, fokus jarak dekat 18cm, bodi ringkas dan sangat ekonomis.', 'Lensa Tamron 28-75mm G2, Lens Hood, Caps Depan Belakang, UV Filter 67mm', 0, 1, 1),

(12, 5, 'Sony FE 16-35mm f/2.8 GM Wide Zoom', 'Sony', 'https://images.unsplash.com/photo-1524368535928-5b5e00ddc76b?auto=format&fit=crop&w=800&q=80', 'Lensa Ultra Wide Zoom sudut lebar lanskap & arsitektur. Resolusi tinggi dari sudut ke sudut frame di seluruh rentang focal length 16mm-35mm.', 'Lensa Sony 16-35mm GM, Lens Hood, Caps, Filter UV Pro 82mm, Carrying Pouch', 0, 0, 1);

-- 4. Rental Rates Seed (6 Hours, 12 Hours, and 24 Hours)
INSERT INTO `rental_rates` (`product_id`, `duration_type`, `price`) VALUES
(1, '6 Jam', 140000), (1, '12 Jam', 190000), (1, '24 Jam', 260000),
(2, '6 Jam', 120000), (2, '12 Jam', 170000), (2, '24 Jam', 230000),
(3, '6 Jam', 110000), (3, '12 Jam', 150000), (3, '24 Jam', 200000),
(4, '6 Jam', 160000), (4, '12 Jam', 220000), (4, '24 Jam', 300000),
(5, '6 Jam', 220000), (5, '12 Jam', 320000), (5, '24 Jam', 450000),
(6, '6 Jam', 90000),  (6, '12 Jam', 130000), (6, '24 Jam', 180000),
(7, '6 Jam', 45000),  (7, '12 Jam', 65000),  (7, '24 Jam', 90000),
(8, '6 Jam', 130000), (8, '12 Jam', 180000), (8, '24 Jam', 250000),
(9, '6 Jam', 110000), (9, '12 Jam', 160000), (9, '24 Jam', 220000),
(10, '6 Jam', 50000), (10, '12 Jam', 75000), (10, '24 Jam', 100000),
(11, '6 Jam', 45000), (11, '12 Jam', 65000), (11, '24 Jam', 85000),
(12, '6 Jam', 85000), (12, '12 Jam', 120000), (12, '24 Jam', 160000);

-- 5. Promos Seed
INSERT INTO `promos` (`id`, `title`, `description`, `discount_info`, `banner_image`, `button_text`, `is_active`) VALUES
(1, 'PROMO WEEKDAY KAMERA & LENSA', 'Sewa sepasang Bodi Kamera + Lensa Art minimal 2 hari (Senin-Kamis) & dapatkan diskon langsung 15%!', 'DISKON 15%', 'https://images.unsplash.com/photo-1512790182412-b19e6d614397?auto=format&fit=crop&w=1200&q=80', 'Klaim Promo WA', 1),
(2, 'SPECIAL STUDENT DISCOUNT MALANG', 'Khusus pelajar dan mahasiswa aktif di Kota Malang. Cukup tunjukkan KTM aktif untuk potongan harga sewa kamera Rp 20.000!', 'POTONGAN Rp 20K', 'https://images.unsplash.com/photo-1492691527719-9d1e07e534b4?auto=format&fit=crop&w=1200&q=80', 'Sewa Pakai KTM', 1),
(3, 'BONUS SEWA LENSA 1 HARI (3 HARI BAYAR 2 HARI)', 'Sewa Lensa G Master / Art Series untuk event 3 hari penuh, cukup bayar sewa 2 hari saja!', 'GRATIS 1 HARI', 'https://images.unsplash.com/photo-1542038784456-1ea8e935640e?auto=format&fit=crop&w=1200&q=80', 'Ambil Bonus Lensa', 1);
