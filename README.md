# Sewa Kamera Malang - Full-Stack Web Application (Node.js + MySQL)

Aplikasi web full-stack lokal untuk bisnis rental kamera dan peralatan fotografi **"Sewa Kamera Malang"**. Didesain dengan Node.js (Express + EJS) dan MySQL Server lokal sehingga **TIDAK MEMBUTUHKAN XAMPP / LARAGON** dan **TIDAK MENGANGGU (NABRAK) WEBSITE LAIN (seperti Toko Roti)**.

---

## ⚡ KENAPA TIDAK NABRAK DENGAN WEBSITE TOKO ROTI?

1. **Port Server Berbeda**:
   - Website `toko-roti` berjalan di Port **`5000`** (`http://localhost:5000`)
   - Website `sewa-kamera` berjalan di Port **`3000`** (`http://localhost:3000`)
   - *Keduanya bisa dinyalakan bersamaan tanpa bentrok!*

2. **Database MySQL Berbeda**:
   - Website `toko-roti` memakai database `toko_roti`
   - Website `sewa-kamera` memakai database `sewa_kamera`
   - *Keduanya hidup berdampingan dengan aman di MySQL Server lokal Anda.*

---

## 🚀 CARA MENJALANKAN APLIKASI (SEKARANG SUDAH RUNNING!)

Website sudah **langsung berjalan sekarang** di:
👉 **`http://localhost:3000`**

### Cara Menyalakan Ulang di Masa Depan:
1. Masuk ke folder `c:\sewa-kamera`
2. Klik 2x file **`start.bat`** (atau ketik `npm start` di CMD/Terminal).
3. Buka browser di **`http://localhost:3000`**!

---

## 🔐 KREDENSIAL LOGIN ADMIN DASHBOARD

Buka alamat:
👉 **`http://localhost:3000/admin/login`**

- **Username**: `admin`
- **Password**: `admin123`

---

## 🎯 FITUR UTAMA APLIKASI

1. **Katalog Produk & Slider Hero**:
   - Filter Kategori (*Mirrorless, Lensa, Lighting, Action Cam, Gimbal, Audio, Drone*).
   - Filter Best Seller & New Arrival.
   - Bar pencarian live.

2. **Section 8 Keunggulan Rental**:
   - Grid 8 poin keunggulan dengan icon lembut (*Gear Terawat, Pilihan Alat Lengkap, Durasi Bervariatif, Garansi Sewa 100%, Free Consultation, Fast Respon, Promo Bulanan, Diskon Pelajar/Mahasiswa Malang*).

3. **Detail Produk (`/product-detail/:id`)**:
   - Layout 2 Kolom dengan preview gambar & kelengkapan sewa.
   - Selector interaktif durasi sewa (**6 Jam, 12 Jam, 24 Jam**).
   - Tombol **"Chat Admin via WhatsApp"** yang otomatis memformat pesan ke WhatsApp toko (`081358491224`):
     `https://wa.me/6281358491224?text=Halo%20Admin%20Sewa%20Kamera%20Malang,%20saya%20mau%20sewa%20[Nama_Produk]%20untuk%20durasi%20[Durasi]`

4. **Dashboard Admin**:
   - Ringkasan statistik produk & promo.
   - CRUD Produk & Update Tarif Harga per durasi (6j, 12j, 24j).
   - CRUD Kategori alat fotografi.
   - CRUD Banner promo slider.
