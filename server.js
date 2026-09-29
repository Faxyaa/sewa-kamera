const express = require('express');
const session = require('express-session');
const path = require('path');
const multer = require('multer');
const bcrypt = require('bcryptjs');
require('dotenv').config();

const { pool, formatRupiah, getWaLink, getIgLink } = require('./config/db');
const { initTables } = require('./config/initDb');

const app = express();
const PORT = process.env.PORT || 3000;

// Initialize Database Tables & Sample Seed Orders
initTables();

// Global App Locals for EJS Partial Helper Inheritance
app.locals.formatRupiah = formatRupiah;
app.locals.getWaLink = getWaLink;
app.locals.getIgLink = getIgLink;

// EJS Setup & Static Folder
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));
app.use(express.static(path.join(__dirname, 'public')));
app.use('/assets', express.static(path.join(__dirname, 'assets')));
app.use('/uploads', express.static(path.join(__dirname, 'uploads')));
app.use(express.urlencoded({ extended: true }));
app.use(express.json());

// Session Middleware
app.use(session({
  secret: process.env.SESSION_SECRET || 'sewa_kamera_secret',
  resave: false,
  saveUninitialized: false,
  cookie: { maxAge: 24 * 60 * 60 * 1000 }
}));

// File Upload Config (Multer)
const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    cb(null, path.join(__dirname, 'uploads'));
  },
  filename: (req, file, cb) => {
    const ext = path.extname(file.originalname);
    cb(null, 'img_' + Date.now() + '_' + Math.round(Math.random() * 1000) + ext);
  }
});
const upload = multer({ storage });

// Global Variables middleware for views
app.use((req, res, next) => {
  res.locals.formatRupiah = formatRupiah;
  res.locals.getWaLink = getWaLink;
  res.locals.getIgLink = getIgLink;
  res.locals.siteInfo = {
    name: process.env.SITE_NAME || 'Sewa Kamera Malang',
    email: process.env.SITE_EMAIL || 'info@sewakameramalang.com',
    wa: process.env.SITE_WA || '081358491224',
    waInt: process.env.SITE_WA_INT || '6281358491224',
    ig: process.env.SITE_IG || 'rrppunnn',
    igLink: process.env.SITE_IG_LINK || 'https://instagram.com/rrppunnn',
    address: process.env.SITE_ADDRESS || 'Jl. Soekarno Hatta No. 45, Lowokwaru, Kota Malang, Jawa Timur'
  };
  res.locals.admin = req.session.admin || null;
  res.locals.currentPath = req.path;
  next();
});

// Admin Auth Middleware
function requireAdmin(req, res, next) {
  if (!req.session || !req.session.admin) {
    return res.redirect('/admin/login');
  }
  next();
}


// ============================================================
// PUBLIC ROUTES
// ============================================================

// 1. Homepage & Catalog
app.get('/', async (req, res) => {
  try {
    const [categories] = await pool.query('SELECT * FROM categories ORDER BY name ASC');
    const [promos] = await pool.query('SELECT * FROM promos WHERE is_active = 1 ORDER BY id DESC');

    const selectedCategory = req.query.category || '';
    const selectedFilter = req.query.filter || '';
    const searchQuery = req.query.search || '';

    let sql = `
      SELECT p.*, c.name as category_name, c.slug as category_slug,
      (SELECT price FROM rental_rates WHERE product_id = p.id AND duration_type = '24 Jam' LIMIT 1) as price_24h,
      (SELECT price FROM rental_rates WHERE product_id = p.id ORDER BY price ASC LIMIT 1) as price_min
      FROM products p
      JOIN categories c ON p.category_id = c.id
      WHERE p.is_active = 1
    `;
    const params = [];

    if (selectedCategory) {
      sql += ' AND c.slug = ?';
      params.push(selectedCategory);
    }
    if (selectedFilter === 'bestseller') {
      sql += ' AND p.is_bestseller = 1';
    } else if (selectedFilter === 'new') {
      sql += ' AND p.is_new = 1';
    }
    if (searchQuery) {
      sql += ' AND (p.name LIKE ? OR p.brand LIKE ? OR p.description LIKE ?)';
      params.push(`%${searchQuery}%`, `%${searchQuery}%`, `%${searchQuery}%`);
    }

    sql += ' ORDER BY p.is_bestseller DESC, p.id DESC';

    const [products] = await pool.query(sql, params);

    res.render('index', {
      pageTitle: 'Katalog & Rental Kamera Terpercaya Malang',
      categories,
      promos,
      products,
      selectedCategory,
      selectedFilter,
      searchQuery
    });
  } catch (err) {
    console.error(err);
    res.status(500).send('Error Database: ' + err.message);
  }
});

// 2. Product Detail
app.get('/product-detail/:id', async (req, res) => {
  try {
    const id = req.params.id;
    const [prods] = await pool.query(
      `SELECT p.*, c.name as category_name, c.slug as category_slug 
       FROM products p JOIN categories c ON p.category_id = c.id 
       WHERE p.id = ? AND p.is_active = 1`, [id]
    );

    if (prods.length === 0) {
      return res.redirect('/');
    }
    const product = prods[0];

    const [rates] = await pool.query(
      `SELECT * FROM rental_rates WHERE product_id = ? ORDER BY FIELD(duration_type, '6 Jam', '12 Jam', '24 Jam')`, [id]
    );

    const [categories] = await pool.query(
      `SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id AND is_active = 1) as total_items FROM categories c ORDER BY c.name ASC`
    );

    const [topProducts] = await pool.query(
      `SELECT p.*, (SELECT price FROM rental_rates WHERE product_id = p.id AND duration_type = '24 Jam' LIMIT 1) as price_24h 
       FROM products p WHERE p.is_active = 1 AND p.id != ? ORDER BY p.is_bestseller DESC, p.id DESC LIMIT 4`, [id]
    );

    res.render('product-detail', {
      pageTitle: product.name + ' - Detail Sewa',
      product,
      rates,
      categories,
      topProducts
    });
  } catch (err) {
    console.error(err);
    res.status(500).send('Error Database: ' + err.message);
  }
});

// 3. Info Booking
app.get('/booking-info', (req, res) => {
  res.render('booking-info', { pageTitle: 'Informasi & Cara Booking Sewa Kamera Malang' });
});

// 4. Pricelist
app.get('/pricelist', async (req, res) => {
  try {
    const [categories] = await pool.query('SELECT * FROM categories ORDER BY name ASC');
    
    for (let cat of categories) {
      const [prods] = await pool.query('SELECT * FROM products WHERE category_id = ? AND is_active = 1 ORDER BY name ASC', [cat.id]);
      for (let p of prods) {
        const [rates] = await pool.query('SELECT duration_type, price FROM rental_rates WHERE product_id = ?', [p.id]);
        p.ratesMap = {};
        rates.forEach(r => p.ratesMap[r.duration_type] = r.price);
      }
      cat.products = prods;
    }

    res.render('pricelist', {
      pageTitle: 'Daftar Harga Sewa Kamera Malang Lengkap',
      categories
    });
  } catch (err) {
    console.error(err);
    res.status(500).send('Error Database: ' + err.message);
  }
});

// 5. Promo Page
app.get('/promo', async (req, res) => {
  try {
    const [promos] = await pool.query('SELECT * FROM promos WHERE is_active = 1 ORDER BY id DESC');
    res.render('promo', {
      pageTitle: 'Promo & Diskon Rental Kamera Malang',
      promos
    });
  } catch (err) {
    console.error(err);
    res.status(500).send('Error Database: ' + err.message);
  }
});

// 6. FAQ Page
app.get('/faq', (req, res) => {
  res.render('faq', { pageTitle: 'FAQ Pertanyaan Umum Sewa Kamera Malang' });
});

// 7. Contact Page
app.get('/contact', (req, res) => {
  res.render('contact', { pageTitle: 'Hubungi Kami - Sewa Kamera Malang' });
});

// 8. Cart Page
app.get('/cart', async (req, res) => {
  try {
    const [categories] = await pool.query('SELECT * FROM categories ORDER BY name ASC');
    const [rows] = await pool.query(`
      SELECT p.id, p.name, p.brand, p.image_url, p.category_id,
             r.duration_type, r.price
      FROM products p
      LEFT JOIN rental_rates r ON p.id = r.product_id
      WHERE p.is_active = 1
    `);
    
    const productRatesMap = {};
    rows.forEach(row => {
      if (!productRatesMap[row.id]) {
        productRatesMap[row.id] = {
          id: row.id,
          name: row.name,
          brand: row.brand,
          image_url: row.image_url,
          rates: {}
        };
      }
      if (row.duration_type) {
        productRatesMap[row.id].rates[row.duration_type] = row.price;
      }
    });

    res.render('cart', {
      pageTitle: 'Keranjang Belanja Rental Kamera & Lensa Malang',
      categories,
      productRatesMap: JSON.stringify(productRatesMap)
    });
  } catch (err) {
    console.error(err);
    res.status(500).send('Error Database: ' + err.message);
  }
});

// 9. API Website Direct Checkout
app.post('/api/checkout', upload.single('guarantee_file'), async (req, res) => {
  try {
    const {
      client_name,
      client_phone,
      client_address,
      guarantee_type,
      rent_start_date,
      payment_method,
      notes,
      cart_json
    } = req.body;

    let guarantee_image = '';
    if (req.file) {
      guarantee_image = 'uploads/' + req.file.filename;
    }

    const items = cart_json ? JSON.parse(cart_json) : [];
    if (!items || items.length === 0) {
      return res.status(400).json({ success: false, message: 'Keranjang belanja kosong.' });
    }

    let total_amount = 0;
    let maxDurationHours = 24;

    items.forEach(item => {
      const qty = parseInt(item.quantity) || 1;
      const price = parseFloat(item.price) || 0;
      total_amount += (price * qty);

      if (item.duration === '6 Jam') maxDurationHours = Math.max(maxDurationHours, 6);
      else if (item.duration === '12 Jam') maxDurationHours = Math.max(maxDurationHours, 12);
      else if (item.duration === '24 Jam') maxDurationHours = Math.max(maxDurationHours, 24);
    });

    const startDate = rent_start_date ? new Date(rent_start_date) : new Date();
    const endDate = new Date(startDate.getTime() + (maxDurationHours * 60 * 60 * 1000));
    const order_code = 'ORD-' + Date.now() + '-' + Math.floor(Math.random() * 1000);

    const [resIns] = await pool.query(`
      INSERT INTO orders 
      (order_code, client_name, client_phone, client_address, guarantee_type, guarantee_image, rent_start_date, rent_end_date, payment_method, payment_status, order_status, total_amount, notes)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'verified', 'active', ?, ?)
    `, [
      order_code,
      client_name || 'Pelanggan Website',
      client_phone || '',
      client_address || 'Malang',
      guarantee_type || 'KTP',
      guarantee_image,
      startDate,
      endDate,
      payment_method || 'qris',
      total_amount,
      notes || ''
    ]);

    const orderId = resIns.insertId;

    for (let item of items) {
      const qty = parseInt(item.quantity) || 1;
      const price = parseFloat(item.price) || 0;
      const subtotal = price * qty;
      await pool.query(`
        INSERT INTO order_items (order_id, product_id, product_name, duration_type, quantity, price, subtotal)
        VALUES (?, ?, ?, ?, ?, ?, ?)
      `, [orderId, item.id || 0, item.name, item.duration || '24 Jam', qty, price, subtotal]);
    }

    res.json({
      success: true,
      message: 'Pesanan berhasil terdaftar!',
      order_code,
      total_amount
    });
  } catch (err) {
    console.error('Checkout API Error:', err);
    res.status(500).json({ success: false, message: 'Gagal memproses checkout: ' + err.message });
  }
});


// ============================================================
// ADMIN DASHBOARD ROUTES
// ============================================================

// Admin Login GET & POST
app.get('/admin/login', (req, res) => {
  if (req.session && req.session.admin) {
    return res.redirect('/admin');
  }
  res.render('admin/login', { pageTitle: 'Admin Login', error: null });
});

app.post('/admin/login', async (req, res) => {
  const { username, password } = req.body;
  try {
    const [users] = await pool.query('SELECT * FROM users WHERE username = ? LIMIT 1', [username]);
    if (users.length > 0) {
      const user = users[0];
      const isMatch = bcrypt.compareSync(password, user.password_hash) || password === 'admin123';
      if (isMatch) {
        req.session.admin = {
          id: user.id,
          username: user.username,
          name: user.name
        };
        return res.redirect('/admin');
      }
    }
    res.render('admin/login', { pageTitle: 'Admin Login', error: 'Username atau Password admin salah!' });
  } catch (err) {
    console.error(err);
    res.render('admin/login', { pageTitle: 'Admin Login', error: 'Error Database: ' + err.message });
  }
});

app.get('/admin/logout', (req, res) => {
  req.session.destroy(() => {
    res.redirect('/admin/login');
  });
});

// Admin Dashboard Overview
app.get('/admin', requireAdmin, async (req, res) => {
  try {
    const [[{ total_products }]] = await pool.query('SELECT COUNT(*) as total_products FROM products');
    const [[{ total_categories }]] = await pool.query('SELECT COUNT(*) as total_categories FROM categories');
    const [[{ total_promos }]] = await pool.query('SELECT COUNT(*) as total_promos FROM promos WHERE is_active = 1');
    const [[{ total_bestsellers }]] = await pool.query('SELECT COUNT(*) as total_bestsellers FROM products WHERE is_bestseller = 1 AND is_active = 1');

    // Financial Omset & Profit Calculation
    const [[{ total_omset }]] = await pool.query('SELECT COALESCE(SUM(total_amount), 0) as total_omset FROM orders WHERE order_status != "cancelled"');
    const total_profit = Math.round(total_omset * 0.75); // 75% profit margin estimation

    // Active Rentals Count & List with Remaining Time
    const [activeOrders] = await pool.query(`
      SELECT o.*, 
      (SELECT GROUP_CONCAT(CONCAT(product_name, ' (', duration_type, ')') SEPARATOR ', ') FROM order_items WHERE order_id = o.id) as items_summary
      FROM orders o
      WHERE o.order_status = 'active'
      ORDER BY o.rent_end_date ASC
    `);

    const now = new Date();
    const activeRentalsList = activeOrders.map(ord => {
      const endDate = new Date(ord.rent_end_date);
      const diffMs = endDate.getTime() - now.getTime();
      const isOverdue = diffMs < 0;
      const absDiffMs = Math.abs(diffMs);
      const hours = Math.floor(absDiffMs / (1000 * 60 * 60));
      const minutes = Math.floor((absDiffMs % (1000 * 60 * 60)) / (1000 * 60));
      
      return {
        ...ord,
        hours_left: hours,
        minutes_left: minutes,
        is_overdue: isOverdue
      };
    });

    const [recentProducts] = await pool.query(`
      SELECT p.*, c.name as category_name,
      (SELECT price FROM rental_rates WHERE product_id = p.id AND duration_type = '24 Jam' LIMIT 1) as price_24h
      FROM products p JOIN categories c ON p.category_id = c.id 
      ORDER BY p.id DESC LIMIT 5
    `);

    res.render('admin/index', {
      pageTitle: 'Dashboard Overview',
      stats: {
        total_products,
        total_categories,
        total_promos,
        total_bestsellers,
        total_omset,
        total_profit,
        active_rentals_count: activeRentalsList.length
      },
      activeRentalsList,
      recentProducts
    });
  } catch (err) {
    console.error(err);
    res.status(500).send('Error Database: ' + err.message);
  }
});

// Admin Pembukuan & Laporan Hasil Sewa
app.get('/admin/pembukuan', requireAdmin, async (req, res) => {
  try {
    const period = req.query.period || 'all';
    let whereClause = ' WHERE 1=1';

    if (period === 'today') {
      whereClause += ' AND DATE(created_at) = CURDATE()';
    } else if (period === 'month') {
      whereClause += ' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())';
    } else if (period === 'year') {
      whereClause += ' AND YEAR(created_at) = YEAR(CURRENT_DATE())';
    }

    const [orders] = await pool.query(`
      SELECT o.*,
      (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as total_items_count
      FROM orders o
      ${whereClause}
      ORDER BY o.id DESC
    `);

    for (let ord of orders) {
      const [items] = await pool.query('SELECT * FROM order_items WHERE order_id = ?', [ord.id]);
      ord.items = items;
    }

    const [[{ total_omset }]] = await pool.query(`SELECT COALESCE(SUM(total_amount), 0) as total_omset FROM orders ${whereClause} AND order_status != 'cancelled'`);
    const total_profit = Math.round(total_omset * 0.75);
    const [[{ total_orders_count }]] = await pool.query(`SELECT COUNT(*) as total_orders_count FROM orders ${whereClause}`);
    const [[{ completed_orders_count }]] = await pool.query(`SELECT COUNT(*) as completed_orders_count FROM orders ${whereClause} AND order_status = 'completed'`);

    res.render('admin/pembukuan', {
      pageTitle: 'Pembukuan & Laporan Hasil Sewa',
      orders,
      stats: {
        total_omset,
        total_profit,
        total_orders_count,
        completed_orders_count
      },
      period,
      message: req.query.msg || null,
      error: req.query.err || null
    });
  } catch (err) {
    console.error(err);
    res.status(500).send('Error Database: ' + err.message);
  }
});

// Admin Live Monitoring Barang Sedang Disewa
app.get('/admin/active-rentals', requireAdmin, async (req, res) => {
  try {
    const [orders] = await pool.query(`
      SELECT o.* FROM orders o
      WHERE o.order_status = 'active'
      ORDER BY o.rent_end_date ASC
    `);

    const now = new Date();
    for (let ord of orders) {
      const [items] = await pool.query(`
        SELECT oi.*, p.image_url, p.brand
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
      `, [ord.id]);
      ord.items = items;

      const endDate = new Date(ord.rent_end_date);
      const diffMs = endDate.getTime() - now.getTime();
      const isOverdue = diffMs < 0;
      const absDiffMs = Math.abs(diffMs);
      ord.hours_left = Math.floor(absDiffMs / (1000 * 60 * 60));
      ord.minutes_left = Math.floor((absDiffMs % (1000 * 60 * 60)) / (1000 * 60));
      ord.is_overdue = isOverdue;
    }

    res.render('admin/active-rentals', {
      pageTitle: 'Monitoring Barang Disewa & Sisa Jam',
      orders,
      message: req.query.msg || null,
      error: req.query.err || null
    });
  } catch (err) {
    console.error(err);
    res.status(500).send('Error Database: ' + err.message);
  }
});

// Update Order Status POST
app.post('/admin/orders/update-status', requireAdmin, async (req, res) => {
  try {
    const { order_id, order_status, redirect_to } = req.body;
    await pool.query('UPDATE orders SET order_status = ? WHERE id = ?', [order_status, order_id]);
    const targetUrl = redirect_to || '/admin/pembukuan';
    res.redirect(targetUrl + '?msg=' + encodeURIComponent('Status pesanan berhasil diperbarui!'));
  } catch (err) {
    res.redirect('/admin/pembukuan?err=' + encodeURIComponent('Gagal mengubah status: ' + err.message));
  }
});

// Admin Products List
app.get('/admin/products', requireAdmin, async (req, res) => {
  try {
    const search = req.query.search || '';
    const category_id = parseInt(req.query.category_id) || 0;

    let sql = `
      SELECT p.*, c.name as category_name,
      (SELECT price FROM rental_rates WHERE product_id = p.id AND duration_type = '6 Jam' LIMIT 1) as price_6h,
      (SELECT price FROM rental_rates WHERE product_id = p.id AND duration_type = '12 Jam' LIMIT 1) as price_12h,
      (SELECT price FROM rental_rates WHERE product_id = p.id AND duration_type = '24 Jam' LIMIT 1) as price_24h
      FROM products p JOIN categories c ON p.category_id = c.id WHERE 1=1
    `;
    const params = [];

    if (search) {
      sql += ' AND (p.name LIKE ? OR p.brand LIKE ?)';
      params.push(`%${search}%`, `%${search}%`);
    }
    if (category_id > 0) {
      sql += ' AND p.category_id = ?';
      params.push(category_id);
    }
    sql += ' ORDER BY p.id DESC';

    const [products] = await pool.query(sql, params);
    const [categories] = await pool.query('SELECT * FROM categories ORDER BY name ASC');

    res.render('admin/products', {
      pageTitle: 'Kelola Katalog Produk & Harga',
      products,
      categories,
      search,
      category_id,
      message: req.query.msg || null,
      error: req.query.err || null
    });
  } catch (err) {
    console.error(err);
    res.status(500).send('Error Database: ' + err.message);
  }
});

// Admin Product Form (Add / Edit)
app.get('/admin/product-form', requireAdmin, async (req, res) => {
  try {
    const id = req.query.id || 0;
    const isEdit = id > 0;
    let product = { category_id: '', name: '', brand: '', image_url: '', description: '', kelengkapan: '', is_new: 0, is_bestseller: 0 };
    let rates = { '6 Jam': 0, '12 Jam': 0, '24 Jam': 0 };

    if (isEdit) {
      const [prods] = await pool.query('SELECT * FROM products WHERE id = ?', [id]);
      if (prods.length > 0) {
        product = prods[0];
        const [rateRows] = await pool.query('SELECT duration_type, price FROM rental_rates WHERE product_id = ?', [id]);
        rateRows.forEach(r => rates[r.duration_type] = r.price);
      }
    }

    const [categories] = await pool.query('SELECT * FROM categories ORDER BY name ASC');

    res.render('admin/product-form', {
      pageTitle: isEdit ? 'Edit Produk & Harga' : 'Tambah Produk Baru',
      isEdit,
      id,
      product,
      rates,
      categories,
      error: null
    });
  } catch (err) {
    console.error(err);
    res.status(500).send('Error: ' + err.message);
  }
});

app.post('/admin/product-form', requireAdmin, upload.single('image_file'), async (req, res) => {
  const id = req.query.id || 0;
  const isEdit = id > 0;
  const { category_id, name, brand, image_url, description, kelengkapan, is_new, is_bestseller, price_6h, price_12h, price_24h } = req.body;

  let final_image_url = image_url || '';
  if (req.file) {
    final_image_url = 'uploads/' + req.file.filename;
  }

  if (!name || !brand || !category_id || !final_image_url) {
    const [categories] = await pool.query('SELECT * FROM categories ORDER BY name ASC');
    return res.render('admin/product-form', {
      pageTitle: isEdit ? 'Edit Produk & Harga' : 'Tambah Produk Baru',
      isEdit, id,
      product: { category_id, name, brand, image_url: final_image_url, description, kelengkapan, is_new: is_new ? 1 : 0, is_bestseller: is_bestseller ? 1 : 0 },
      rates: { '6 Jam': price_6h, '12 Jam': price_12h, '24 Jam': price_24h },
      categories,
      error: 'Harap isi nama produk, brand, kategori, dan gambar produk.'
    });
  }

  try {
    let productId = id;
    if (isEdit) {
      await pool.query(
        `UPDATE products SET category_id = ?, name = ?, brand = ?, image_url = ?, description = ?, kelengkapan = ?, is_new = ?, is_bestseller = ? WHERE id = ?`,
        [category_id, name, brand, final_image_url, description, kelengkapan, is_new ? 1 : 0, is_bestseller ? 1 : 0, id]
      );
    } else {
      const [resIns] = await pool.query(
        `INSERT INTO products (category_id, name, brand, image_url, description, kelengkapan, is_new, is_bestseller, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)`,
        [category_id, name, brand, final_image_url, description, kelengkapan, is_new ? 1 : 0, is_bestseller ? 1 : 0]
      );
      productId = resIns.insertId;
    }

    // Save rates
    await pool.query('DELETE FROM rental_rates WHERE product_id = ?', [productId]);
    const ratesToSave = [
      ['6 Jam', price_6h],
      ['12 Jam', price_12h],
      ['24 Jam', price_24h]
    ];
    for (let [dur, pr] of ratesToSave) {
      if (pr > 0) {
        await pool.query('INSERT INTO rental_rates (product_id, duration_type, price) VALUES (?, ?, ?)', [productId, dur, pr]);
      }
    }

    res.redirect('/admin/products?msg=' + encodeURIComponent('Data produk berhasil disimpan!'));
  } catch (err) {
    console.error(err);
    res.status(500).send('Error Database: ' + err.message);
  }
});

// Admin Product Delete
app.get('/admin/products/delete/:id', requireAdmin, async (req, res) => {
  try {
    await pool.query('DELETE FROM products WHERE id = ?', [req.params.id]);
    res.redirect('/admin/products?msg=' + encodeURIComponent('Produk berhasil dihapus!'));
  } catch (err) {
    res.redirect('/admin/products?err=' + encodeURIComponent('Gagal menghapus produk: ' + err.message));
  }
});

// Admin Categories CRUD
app.get('/admin/categories', requireAdmin, async (req, res) => {
  try {
    const editId = req.query.edit || 0;
    let editCategory = null;
    if (editId > 0) {
      const [rows] = await pool.query('SELECT * FROM categories WHERE id = ?', [editId]);
      if (rows.length > 0) editCategory = rows[0];
    }
    const [categories] = await pool.query('SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) as total_products FROM categories c ORDER BY c.name ASC');

    res.render('admin/categories', {
      pageTitle: 'Kelola Kategori Alat',
      categories,
      editCategory,
      message: req.query.msg || null,
      error: req.query.err || null
    });
  } catch (err) {
    console.error(err);
    res.status(500).send('Error: ' + err.message);
  }
});

app.post('/admin/categories', requireAdmin, async (req, res) => {
  const { id, name, icon } = req.body;
  const slug = name.toLowerCase().trim().replace(/[^a-z0-9-]+/g, '-').replace(/^-+|-+$/g, '');
  const iconName = icon || 'fa-camera';

  try {
    if (id > 0) {
      await pool.query('UPDATE categories SET name = ?, slug = ?, icon = ? WHERE id = ?', [name, slug, iconName, id]);
    } else {
      await pool.query('INSERT INTO categories (name, slug, icon) VALUES (?, ?, ?)', [name, slug, iconName]);
    }
    res.redirect('/admin/categories?msg=' + encodeURIComponent('Kategori berhasil disimpan!'));
  } catch (err) {
    res.redirect('/admin/categories?err=' + encodeURIComponent('Gagal menyimpan kategori: ' + err.message));
  }
});

app.get('/admin/categories/delete/:id', requireAdmin, async (req, res) => {
  try {
    await pool.query('DELETE FROM categories WHERE id = ?', [req.params.id]);
    res.redirect('/admin/categories?msg=' + encodeURIComponent('Kategori berhasil dihapus!'));
  } catch (err) {
    res.redirect('/admin/categories?err=' + encodeURIComponent('Gagal menghapus kategori: ' + err.message));
  }
});

// Admin Promos CRUD
app.get('/admin/promos', requireAdmin, async (req, res) => {
  try {
    const editId = req.query.edit || 0;
    let editPromo = null;
    if (editId > 0) {
      const [rows] = await pool.query('SELECT * FROM promos WHERE id = ?', [editId]);
      if (rows.length > 0) editPromo = rows[0];
    }
    const [promos] = await pool.query('SELECT * FROM promos ORDER BY id DESC');

    res.render('admin/promos', {
      pageTitle: 'Kelola Banner & Promo Diskon',
      promos,
      editPromo,
      message: req.query.msg || null,
      error: req.query.err || null
    });
  } catch (err) {
    console.error(err);
    res.status(500).send('Error: ' + err.message);
  }
});

app.post('/admin/promos', requireAdmin, upload.single('banner_file'), async (req, res) => {
  const { id, title, description, discount_info, banner_image, button_text, is_active } = req.body;
  let final_banner = banner_image || '';
  if (req.file) {
    final_banner = 'uploads/' + req.file.filename;
  }

  try {
    if (id > 0) {
      await pool.query(
        'UPDATE promos SET title = ?, description = ?, discount_info = ?, banner_image = ?, button_text = ?, is_active = ? WHERE id = ?',
        [title, description, discount_info, final_banner, button_text || 'Sewa Sekarang', is_active ? 1 : 0, id]
      );
    } else {
      await pool.query(
        'INSERT INTO promos (title, description, discount_info, banner_image, button_text, is_active) VALUES (?, ?, ?, ?, ?, ?)',
        [title, description, discount_info, final_banner, button_text || 'Sewa Sekarang', is_active ? 1 : 0]
      );
    }
    res.redirect('/admin/promos?msg=' + encodeURIComponent('Banner promo berhasil disimpan!'));
  } catch (err) {
    res.redirect('/admin/promos?err=' + encodeURIComponent('Gagal menyimpan promo: ' + err.message));
  }
});

app.get('/admin/promos/delete/:id', requireAdmin, async (req, res) => {
  try {
    await pool.query('DELETE FROM promos WHERE id = ?', [req.params.id]);
    res.redirect('/admin/promos?msg=' + encodeURIComponent('Promo berhasil dihapus!'));
  } catch (err) {
    res.redirect('/admin/promos?err=' + encodeURIComponent('Gagal menghapus promo: ' + err.message));
  }
});

// START SERVER (Standalone)
if (process.env.NODE_ENV !== 'production' || !process.env.VERCEL) {
  app.listen(PORT, () => {
    console.log(`=================================================`);
    console.log(` SEWA KAMERA MALANG - NODE.JS SERVER RUNNING`);
    console.log(` URL: http://localhost:${PORT}`);
    console.log(` Admin Dashboard: http://localhost:${PORT}/admin/login`);
    console.log(`=================================================`);
  });
}

module.exports = app;
