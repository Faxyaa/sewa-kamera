<?php
$page_title = "Hubungi Kami - Sewa Kamera Malang";
require_once __DIR__ . '/includes/header.php';
?>

<!-- Header -->
<section class="bg-navybrand-900 text-white py-12 border-b border-navybrand-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-3">
        <span class="text-skybrand-300 font-extrabold text-xs uppercase tracking-widest bg-white/10 px-3 py-1 rounded-full border border-white/20">
            Contact & Location
        </span>
        <h1 class="text-3xl md:text-4xl font-extrabold text-white tracking-tight">
            Hubungi Toko Kami
        </h1>
        <p class="text-slate-300 text-sm max-w-2xl mx-auto">
            Kunjungi store fisik kami di Kota Malang atau hubungi Admin via WhatsApp untuk respon instan.
        </p>
    </div>
</section>

<!-- Content -->
<section class="py-14 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Left Info Cards (Column 5 of 12) -->
            <div class="lg:col-span-5 space-y-6">
                
                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
                    <h3 class="font-extrabold text-navybrand-800 text-lg border-b border-slate-100 pb-3">Informasi Kontak</h3>
                    
                    <ul class="space-y-4 text-xs font-semibold text-slate-700">
                        <li class="flex items-start space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-skybrand-50 text-skybrand-500 flex items-center justify-center text-base flex-shrink-0">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Alamat Store Malang</span>
                                <span class="text-slate-800 font-bold"><?php echo SITE_ADDRESS; ?></span>
                            </div>
                        </li>

                        <li class="flex items-start space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-base flex-shrink-0">
                                <i class="fa-brands fa-whatsapp"></i>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">WhatsApp Fast Response</span>
                                <a href="<?php echo getWaLink(); ?>" target="_blank" class="text-emerald-600 font-extrabold text-sm hover:underline">
                                    <?php echo SITE_WA; ?>
                                </a>
                            </div>
                        </li>

                        <li class="flex items-start space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center text-base flex-shrink-0">
                                <i class="fa-regular fa-envelope"></i>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Email Official</span>
                                <span class="text-slate-800 font-bold"><?php echo SITE_EMAIL; ?></span>
                            </div>
                        </li>

                        <li class="flex items-start space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center text-base flex-shrink-0">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Jam Buka Store</span>
                                <span class="text-slate-800 font-bold">Senin - Minggu: 08.00 - 21.00 WIB</span>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="bg-gradient-to-br from-navybrand-900 to-navybrand-800 rounded-3xl p-6 text-white space-y-3 shadow-md">
                    <h4 class="font-bold text-base flex items-center gap-2">
                        <i class="fa-brands fa-whatsapp text-emerald-400 text-xl"></i>
                        <span>Langsung Chat Admin</span>
                    </h4>
                    <p class="text-xs text-slate-300">
                        Pesan kamera impianmu sekarang tanpa perlu mengisi form panjang.
                    </p>
                    <a href="<?php echo getWaLink(); ?>" target="_blank" class="w-full inline-flex items-center justify-center space-x-2 bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3 px-4 rounded-xl text-xs shadow transition-colors btn-wa-pulse">
                        <span>Buka WhatsApp Web / App</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

            </div>

            <!-- Right Map & Form (Column 7 of 12) -->
            <div class="lg:col-span-7 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6">
                <div>
                    <h3 class="font-extrabold text-navybrand-800 text-lg">Peta Lokasi Store (Kota Malang)</h3>
                    <p class="text-slate-500 text-xs mt-1">Dekat dengan kampus Universitas Brawijaya & Polinema Malang.</p>
                </div>

                <!-- Google Maps Embedded Placeholder -->
                <div class="w-full h-64 bg-slate-100 rounded-2xl overflow-hidden border border-slate-200 relative flex items-center justify-center">
                    <iframe class="w-full h-full border-0" src="https://maps.google.com/maps?q=Lowokwaru%20Kota%20Malang&t=&z=14&ie=UTF8&iwloc=&output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>

                <div class="bg-skybrand-50 p-4 rounded-2xl border border-skybrand-100 flex items-center gap-3 text-xs text-navybrand-800 font-semibold">
                    <i class="fa-solid fa-location-arrow text-skybrand-500 text-lg"></i>
                    <span>Tersedia area parkir luas untuk mobil & motor saat pengambilan unit kamera.</span>
                </div>
            </div>

        </div>

    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
