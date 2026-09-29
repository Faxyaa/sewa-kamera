    </main>

    <!-- FOOTER SECTION -->
    <footer class="bg-navybrand-950 text-slate-300 pt-16 pb-8 border-t border-navybrand-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 pb-12 border-b border-navybrand-800">
                
                <!-- Col 1: About Business -->
                <div class="space-y-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-skybrand-400 to-skybrand-300 p-0.5">
                            <div class="w-full h-full bg-navybrand-900 rounded-full flex items-center justify-center">
                                <i class="fa-solid fa-camera text-skybrand-300 text-lg"></i>
                            </div>
                        </div>
                        <span class="text-lg font-bold text-white tracking-tight">
                            SEWA KAMERA <span class="text-skybrand-300">MALANG</span>
                        </span>
                    </div>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Penyedia jasa sewa kamera mirrorless, lensa profesional, lighting studio, action cam, gimbal stabilizer, & drone terpercaya di Kota Malang. Unit selalu terawat & bersih.
                    </p>
                    <div class="pt-2 flex items-center space-x-3">
                        <a href="<?php echo getIgLink(); ?>" target="_blank" class="w-9 h-9 rounded-full bg-navybrand-900 hover:bg-skybrand-500 hover:text-white flex items-center justify-center transition-colors">
                            <i class="fa-brands fa-instagram"></i>
                        </a>
                        <a href="https://facebook.com" target="_blank" class="w-9 h-9 rounded-full bg-navybrand-900 hover:bg-skybrand-500 hover:text-white flex items-center justify-center transition-colors">
                            <i class="fa-brands fa-facebook"></i>
                        </a>
                        <a href="https://tiktok.com" target="_blank" class="w-9 h-9 rounded-full bg-navybrand-900 hover:bg-skybrand-500 hover:text-white flex items-center justify-center transition-colors">
                            <i class="fa-brands fa-tiktok"></i>
                        </a>
                        <a href="<?php echo getWaLink(); ?>" target="_blank" class="w-9 h-9 rounded-full bg-navybrand-900 hover:bg-emerald-500 hover:text-white flex items-center justify-center transition-colors">
                            <i class="fa-brands fa-whatsapp"></i>
                        </a>
                    </div>
                </div>

                <!-- Col 2: Fast Navigation -->
                <div class="space-y-3">
                    <h4 class="text-white font-bold text-base border-b border-navybrand-800 pb-2">Navigasi Cepat</h4>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li><a href="index.php" class="hover:text-skybrand-300 transition-colors flex items-center gap-2"><i class="fa-solid fa-chevron-right text-[10px] text-skybrand-400"></i> Katalog Alat Foto</a></li>
                        <li><a href="booking-info.php" class="hover:text-skybrand-300 transition-colors flex items-center gap-2"><i class="fa-solid fa-chevron-right text-[10px] text-skybrand-400"></i> Syarat & Cara Sewa</a></li>
                        <li><a href="pricelist.php" class="hover:text-skybrand-300 transition-colors flex items-center gap-2"><i class="fa-solid fa-chevron-right text-[10px] text-skybrand-400"></i> Daftar Harga Lengkap</a></li>
                        <li><a href="promo.php" class="hover:text-skybrand-300 transition-colors flex items-center gap-2"><i class="fa-solid fa-chevron-right text-[10px] text-skybrand-400"></i> Promo & Diskon</a></li>
                        <li><a href="faq.php" class="hover:text-skybrand-300 transition-colors flex items-center gap-2"><i class="fa-solid fa-chevron-right text-[10px] text-skybrand-400"></i> Pertanyaan Umum (FAQ)</a></li>
                        <li><a href="admin/login.php" class="hover:text-skybrand-300 transition-colors flex items-center gap-2 text-slate-500"><i class="fa-solid fa-lock text-[10px]"></i> Login Admin Store</a></li>
                    </ul>
                </div>

                <!-- Col 3: Operational Hours & Location -->
                <div class="space-y-3">
                    <h4 class="text-white font-bold text-base border-b border-navybrand-800 pb-2">Jam Operasional Store</h4>
                    <div class="text-sm text-slate-400 space-y-2">
                        <div class="flex justify-between items-center py-1 border-b border-navybrand-900">
                            <span>Senin - Jumat</span>
                            <span class="text-skybrand-300 font-semibold">08.00 - 21.00 WIB</span>
                        </div>
                        <div class="flex justify-between items-center py-1 border-b border-navybrand-900">
                            <span>Sabtu - Minggu</span>
                            <span class="text-skybrand-300 font-semibold">07.00 - 22.00 WIB</span>
                        </div>
                        <p class="text-xs text-slate-500 pt-1">
                            <i class="fa-solid fa-clock mr-1 text-amber-400"></i> Pengambilan & pengembalian unit dapat dijadwalkan via WhatsApp Admin.
                        </p>
                    </div>
                </div>

                <!-- Col 4: Store Contact & Address -->
                <div class="space-y-3">
                    <h4 class="text-white font-bold text-base border-b border-navybrand-800 pb-2">Kontak & Lokasi</h4>
                    <ul class="space-y-3 text-sm text-slate-400">
                        <li class="flex items-start gap-3">
                            <i class="fa-solid fa-location-dot text-skybrand-300 mt-1"></i>
                            <span><?php echo SITE_ADDRESS; ?></span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fa-brands fa-whatsapp text-emerald-400 text-lg"></i>
                            <a href="<?php echo getWaLink(); ?>" target="_blank" class="hover:text-emerald-400 font-semibold text-white">
                                <?php echo SITE_WA; ?>
                            </a>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fa-regular fa-envelope text-skybrand-300"></i>
                            <span><?php echo SITE_EMAIL; ?></span>
                        </li>
                    </ul>
                </div>

            </div>

            <!-- Bottom Copyright Bar -->
            <div class="pt-8 flex flex-col sm:flex-row justify-between items-center text-xs text-slate-500 gap-4">
                <p>&copy; <?php echo date('Y'); ?> <strong class="text-slate-300"><?php echo SITE_NAME; ?></strong>. All Rights Reserved. Local Server Deployment.</p>
                <div class="flex items-center space-x-4">
                    <span class="hover:text-slate-400">Malang Camera Rental</span>
                    <span>•</span>
                    <span class="hover:text-slate-400">Fast Booking via WhatsApp</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Custom Main Script -->
    <script src="assets/js/main.js"></script>
</body>
</html>
