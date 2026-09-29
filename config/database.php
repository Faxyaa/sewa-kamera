<?php
/**
 * Database Connection Configuration
 * Sewa Kamera Malang
 */

// Host & Port Configuration
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'sewa_kamera_db');
define('DB_USER', 'root');
define('DB_PASS', ''); // Default XAMPP / Laragon password is empty

// Site Global Info
define('SITE_NAME', 'Sewa Kamera Malang');
define('SITE_EMAIL', 'info@sewakameramalang.com');
define('SITE_WA', '081358491224');
define('SITE_WA_INT', '6281358491224');
define('SITE_IG', 'rrppunnn');
define('SITE_ADDRESS', 'Jl. Soekarno Hatta No. 45, Lowokwaru, Kota Malang, Jawa Timur');

function getDBConnection() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Friendly error message for localhost setup
            die('<div style="font-family: sans-serif; padding: 2rem; background: #fee2e2; color: #991b1b; border-radius: 8px; max-width: 600px; margin: 2rem auto;">' .
                '<h3 style="margin-top:0;">Gagal Terhubung ke Database MySQL!</h3>' .
                '<p>Pastikan MySQL server di <strong>XAMPP / Laragon</strong> sudah di-START, dan database <code>' . DB_NAME . '</code> sudah dibuat dengan mengimpor file <code>schema.sql</code>.</p>' .
                '<p><strong>Pesan Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>' .
                '</div>');
        }
    }
    return $pdo;
}

// Global PDO instance
$pdo = getDBConnection();

// Format Rupiah Helper
if (!function_exists('formatRupiah')) {
    function formatRupiah($amount) {
        return 'Rp ' . number_format((float)$amount, 0, ',', '.');
    }
}

// Helper to format WhatsApp link
if (!function_exists('getWaLink')) {
    function getWaLink($productName = '', $duration = '24 Jam') {
        $waNum = SITE_WA_INT;
        if (!empty($productName)) {
            $text = "Halo Admin Sewa Kamera Malang, saya mau sewa " . $productName . " untuk durasi " . $duration;
        } else {
            $text = "Halo Admin Sewa Kamera Malang, saya ingin bertanya info sewa alat kamera.";
        }
        return "https://wa.me/" . $waNum . "?text=" . rawurlencode($text);
    }
}

// Helper to get Instagram link
if (!function_exists('getIgLink')) {
    function getIgLink() {
        return "https://instagram.com/" . SITE_IG;
    }
}

