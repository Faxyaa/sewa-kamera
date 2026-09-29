const { pool } = require('./db');

async function initTables() {
  try {
    // 1. Create orders table
    await pool.query(`
      CREATE TABLE IF NOT EXISTS \`orders\` (
        \`id\` INT AUTO_INCREMENT PRIMARY KEY,
        \`order_code\` VARCHAR(50) NOT NULL UNIQUE,
        \`client_name\` VARCHAR(100) NOT NULL,
        \`client_phone\` VARCHAR(30) DEFAULT '',
        \`client_address\` TEXT NOT NULL,
        \`guarantee_type\` VARCHAR(100) NOT NULL,
        \`guarantee_image\` TEXT DEFAULT NULL,
        \`rent_start_date\` DATETIME NOT NULL,
        \`rent_end_date\` DATETIME NOT NULL,
        \`payment_method\` ENUM('qris', 'transfer_bca') NOT NULL DEFAULT 'qris',
        \`payment_status\` ENUM('pending', 'paid', 'verified') NOT NULL DEFAULT 'paid',
        \`order_status\` ENUM('pending', 'active', 'completed', 'cancelled') NOT NULL DEFAULT 'active',
        \`total_amount\` DECIMAL(12, 0) NOT NULL DEFAULT 0,
        \`notes\` TEXT DEFAULT NULL,
        \`created_at\` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    `);

    // 2. Create order_items table
    await pool.query(`
      CREATE TABLE IF NOT EXISTS \`order_items\` (
        \`id\` INT AUTO_INCREMENT PRIMARY KEY,
        \`order_id\` INT NOT NULL,
        \`product_id\` INT NOT NULL,
        \`product_name\` VARCHAR(150) NOT NULL,
        \`duration_type\` VARCHAR(50) NOT NULL,
        \`quantity\` INT NOT NULL DEFAULT 1,
        \`price\` DECIMAL(10, 0) NOT NULL,
        \`subtotal\` DECIMAL(12, 0) NOT NULL,
        FOREIGN KEY (\`order_id\`) REFERENCES \`orders\`(\`id\`) ON DELETE CASCADE
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    `);

    // 3. Check if sample orders exist, if not seed sample active orders for rich reports
    const [existingOrders] = await pool.query('SELECT COUNT(*) as cnt FROM orders');
    if (existingOrders[0].cnt === 0) {
      const now = new Date();
      
      // Order 1: Active rental started 4 hours ago (ends in 20 hours for 24h rental)
      const start1 = new Date(now.getTime() - (4 * 60 * 60 * 1000));
      const end1 = new Date(start1.getTime() + (24 * 60 * 60 * 1000));

      const [res1] = await pool.query(`
        INSERT INTO orders 
        (order_code, client_name, client_phone, client_address, guarantee_type, guarantee_image, rent_start_date, rent_end_date, payment_method, payment_status, order_status, total_amount, notes)
        VALUES 
        (?, ?, ?, ?, ?, ?, ?, ?, 'qris', 'verified', 'active', 490000, ?)
      `, [
        'ORD-' + Date.now() + '-1',
        'Rizky Pratama (Mahasiswa UB)',
        '081234567890',
        'Jl. Veteran No. 12, Lowokwaru, Kota Malang',
        'Kartu Pelajar / KTM (Kartu Tanda Mahasiswa)',
        'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=600&q=80',
        start1,
        end1,
        'Paket wisuda shoot, minta SD card 64GB'
      ]);

      await pool.query(`
        INSERT INTO order_items (order_id, product_id, product_name, duration_type, quantity, price, subtotal)
        VALUES 
        (?, 1, 'Sony Alpha A7 IV Body Only', '24 Jam', 1, 260000, 260000),
        (?, 2, 'Sony Alpha A7 III Body Only', '24 Jam', 1, 230000, 230000)
      `, [res1.insertId, res1.insertId]);

      // Order 2: Active rental started 10 hours ago for 12h rental (ends in 2 hours)
      const start2 = new Date(now.getTime() - (10 * 60 * 60 * 1000));
      const end2 = new Date(start2.getTime() + (12 * 60 * 60 * 1000));

      const [res2] = await pool.query(`
        INSERT INTO orders 
        (order_code, client_name, client_phone, client_address, guarantee_type, guarantee_image, rent_start_date, rent_end_date, payment_method, payment_status, order_status, total_amount, notes)
        VALUES 
        (?, ?, ?, ?, ?, ?, ?, ?, 'transfer_bca', 'verified', 'active', 180000, ?)
      `, [
        'ORD-' + Date.now() + '-2',
        'Dina Sastro (Studio Foto Malang)',
        '085678901234',
        'Jl. Soekarno Hatta No. 88, Malang',
        'KTP (Kartu Tanda Penduduk)',
        'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=600&q=80',
        start2,
        end2,
        'Event photo session indoor'
      ]);

      await pool.query(`
        INSERT INTO order_items (order_id, product_id, product_name, duration_type, quantity, price, subtotal)
        VALUES 
        (?, 6, 'Sony FE 24-70mm f/2.8 GM II', '12 Jam', 1, 180000, 180000)
      `, [res2.insertId]);

      // Order 3: Completed order yesterday
      const start3 = new Date(now.getTime() - (36 * 60 * 60 * 1000));
      const end3 = new Date(now.getTime() - (12 * 60 * 60 * 1000));

      const [res3] = await pool.query(`
        INSERT INTO orders 
        (order_code, client_name, client_phone, client_address, guarantee_type, guarantee_image, rent_start_date, rent_end_date, payment_method, payment_status, order_status, total_amount, notes)
        VALUES 
        (?, ?, ?, ?, ?, ?, ?, ?, 'qris', 'verified', 'completed', 450000, ?)
      `, [
        'ORD-' + Date.now() + '-3',
        'Bagus Kurniawan',
        '089123456789',
        'Jl. Candi Panggung No. 4, Malang',
        'SIM A / SIM C',
        'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=600&q=80',
        start3,
        end3,
        'Rental lancar, unit dikembalikan tepat waktu'
      ]);

      await pool.query(`
        INSERT INTO order_items (order_id, product_id, product_name, duration_type, quantity, price, subtotal)
        VALUES 
        (?, 5, 'Sony Cinema Line FX3 Body', '24 Jam', 1, 450000, 450000)
      `, [res3.insertId]);

      console.log('Sample order data seeded successfully into orders & order_items tables.');
    }
  } catch (err) {
    console.error('Error initializing order tables:', err);
  }
}

module.exports = { initTables };
