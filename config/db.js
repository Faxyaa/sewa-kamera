const mysql = require('mysql2/promise');
require('dotenv').config();

const pool = mysql.createPool({
  host: process.env.DB_HOST || 'localhost',
  port: process.env.DB_PORT || 3306,
  user: process.env.DB_USER || 'root',
  password: process.env.DB_PASSWORD || '',
  database: process.env.DB_NAME || 'sewa_kamera',
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0,
  ssl: process.env.DB_SSL === 'true' ? { rejectUnauthorized: false } : undefined
});

// Format Rupiah Helper
function formatRupiah(amount) {
  return 'Rp ' + Number(amount || 0).toLocaleString('id-ID');
}

// WhatsApp Link Generator
function getWaLink(productName = '', duration = '24 Jam') {
  const waNum = process.env.SITE_WA_INT || '6281358491224';
  let text = '';
  if (productName) {
    text = `Halo Admin Sewa Kamera Malang, saya mau sewa ${productName} untuk durasi ${duration}`;
  } else {
    text = 'Halo Admin Sewa Kamera Malang, saya ingin bertanya info sewa alat kamera.';
  }
  return `https://wa.me/${waNum}?text=${encodeURIComponent(text)}`;
}

// Instagram Link Generator
function getIgLink(productName = '', duration = '24 Jam') {
  const igHandle = process.env.SITE_IG || 'rrppunnn';
  return process.env.SITE_IG_LINK || `https://instagram.com/${igHandle}`;
}

module.exports = {
  pool,
  formatRupiah,
  getWaLink,
  getIgLink
};
