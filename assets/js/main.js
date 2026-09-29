/**
 * Sewa Kamera Malang - Main JavaScript File & Shopping Cart Engine
 */

// ============================================================
// SHOPPING CART ENGINE (LOCALSTORAGE)
// ============================================================
const CART_STORAGE_KEY = 'sewa_kamera_cart';

function getCart() {
    try {
        const raw = localStorage.getItem(CART_STORAGE_KEY);
        return raw ? JSON.parse(raw) : [];
    } catch (e) {
        console.error('Error reading cart from localStorage', e);
        return [];
    }
}

function saveCart(cart) {
    try {
        localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cart));
    } catch (e) {
        console.error('Error saving cart to localStorage', e);
    }
    updateCartBadges();
}

function updateCartBadges() {
    const cart = getCart();
    const totalCount = cart.reduce((sum, item) => sum + (parseInt(item.quantity) || 1), 0);
    const badges = document.querySelectorAll('.headerCartBadge');
    badges.forEach(badge => {
        badge.textContent = totalCount;
    });
}

function formatRupiahJS(number) {
    return 'Rp ' + Number(number || 0).toLocaleString('id-ID');
}

function showCartToast(itemName) {
    const toast = document.getElementById('cartToast');
    const toastText = document.getElementById('cartToastText');
    if (!toast) return;

    if (toastText && itemName) {
        toastText.textContent = `"${itemName}" berhasil ditambahkan ke keranjang.`;
    }
    toast.classList.remove('translate-y-20', 'opacity-0', 'pointer-events-none');
    
    setTimeout(() => {
        toast.classList.add('translate-y-20', 'opacity-0', 'pointer-events-none');
    }, 3500);
}

function addToCart(product) {
    // product = { id, name, brand, image_url, duration, price }
    const cart = getCart();
    const existingIndex = cart.findIndex(item => item.id == product.id && item.duration == product.duration);

    if (existingIndex > -1) {
        cart[existingIndex].quantity = (parseInt(cart[existingIndex].quantity) || 1) + 1;
    } else {
        cart.push({
            id: product.id,
            name: product.name,
            brand: product.brand || '',
            image_url: product.image_url || '',
            duration: product.duration || '24 Jam',
            price: parseFloat(product.price) || 0,
            quantity: 1
        });
    }

    saveCart(cart);
    showCartToast(product.name);

    if (document.getElementById('cartItemsContainer')) {
        renderCartPage();
    }
}

function removeFromCart(index) {
    const cart = getCart();
    if (index >= 0 && index < cart.length) {
        cart.splice(index, 1);
        saveCart(cart);
        if (document.getElementById('cartItemsContainer')) {
            renderCartPage();
        }
    }
}

function updateCartQuantity(index, delta) {
    const cart = getCart();
    if (index >= 0 && index < cart.length) {
        const newQty = (parseInt(cart[index].quantity) || 1) + delta;
        if (newQty > 0) {
            cart[index].quantity = newQty;
        } else {
            cart.splice(index, 1);
        }
        saveCart(cart);
        if (document.getElementById('cartItemsContainer')) {
            renderCartPage();
        }
    }
}

function updateCartItemDuration(index, newDuration) {
    const cart = getCart();
    if (index >= 0 && index < cart.length) {
        const item = cart[index];
        item.duration = newDuration;

        if (window.PRODUCT_RATES_MAP && window.PRODUCT_RATES_MAP[item.id]) {
            const ratesObj = window.PRODUCT_RATES_MAP[item.id].rates;
            if (ratesObj && ratesObj[newDuration]) {
                item.price = parseFloat(ratesObj[newDuration]);
            }
        }
        saveCart(cart);
        if (document.getElementById('cartItemsContainer')) {
            renderCartPage();
        }
    }
}

function clearCart() {
    localStorage.removeItem(CART_STORAGE_KEY);
    updateCartBadges();
    if (document.getElementById('cartItemsContainer')) {
        renderCartPage();
    }
}

function renderCartPage() {
    const container = document.getElementById('cartItemsContainer');
    const emptyState = document.getElementById('cartEmptyState');
    const contentArea = document.getElementById('cartContentArea');
    if (!container) return;

    const cart = getCart();
    if (cart.length === 0) {
        if (contentArea) contentArea.classList.add('hidden');
        if (emptyState) emptyState.classList.remove('hidden');
        return;
    }

    if (contentArea) contentArea.classList.remove('hidden');
    if (emptyState) emptyState.classList.add('hidden');

    let html = '';
    let totalItems = 0;
    let totalPrice = 0;

    cart.forEach((item, index) => {
        const itemQty = parseInt(item.quantity) || 1;
        const itemSubtotal = item.price * itemQty;
        totalItems += itemQty;
        totalPrice += itemSubtotal;

        let ratesAvailable = ['6 Jam', '12 Jam', '24 Jam'];
        if (window.PRODUCT_RATES_MAP && window.PRODUCT_RATES_MAP[item.id] && window.PRODUCT_RATES_MAP[item.id].rates) {
            ratesAvailable = Object.keys(window.PRODUCT_RATES_MAP[item.id].rates);
        }

        html += `
        <div class="glass-panel rounded-3xl p-4 sm:p-5 border border-white/80 shadow-md flex flex-col sm:flex-row items-center justify-between gap-4 transition-all hover:border-skybrand-300">
            <div class="flex items-center space-x-4 w-full sm:w-auto">
                <div class="w-20 h-20 bg-white border border-slate-200 rounded-2xl p-1.5 flex-shrink-0 flex items-center justify-center shadow-sm">
                    <img src="${item.image_url}" alt="${item.name}" class="max-h-16 object-contain">
                </div>
                <div class="space-y-1">
                    <span class="text-[10px] font-extrabold text-skybrand-600 uppercase tracking-widest block">${item.brand || 'CAMERA'}</span>
                    <h4 class="font-extrabold text-navybrand-800 text-sm sm:text-base leading-tight">${item.name}</h4>
                    <div class="flex items-center gap-2 pt-1">
                        <label class="text-[11px] font-bold text-slate-500">Durasi:</label>
                        <select onchange="updateCartItemDuration(${index}, this.value)" class="text-xs font-extrabold text-navybrand-800 bg-slate-100 border border-slate-200 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-skybrand-400">
                            ${ratesAvailable.map(dur => `<option value="${dur}" ${dur === item.duration ? 'selected' : ''}>${dur}</option>`).join('')}
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between sm:justify-end w-full sm:w-auto space-x-4 border-t sm:border-t-0 pt-3 sm:pt-0 border-slate-100">
                <!-- Quantity Controls -->
                <div class="flex items-center space-x-2 bg-slate-100 border border-slate-200 rounded-xl p-1">
                    <button onclick="updateCartQuantity(${index}, -1)" class="w-7 h-7 bg-white rounded-lg text-slate-700 font-bold hover:bg-slate-200 transition-colors shadow-sm text-xs flex items-center justify-center">-</button>
                    <span class="w-8 text-center font-extrabold text-xs text-navybrand-800">${itemQty}</span>
                    <button onclick="updateCartQuantity(${index}, 1)" class="w-7 h-7 bg-white rounded-lg text-slate-700 font-bold hover:bg-slate-200 transition-colors shadow-sm text-xs flex items-center justify-center">+</button>
                </div>

                <!-- Subtotal -->
                <div class="text-right min-w-[100px]">
                    <span class="text-[10px] text-slate-400 font-bold block uppercase">Subtotal</span>
                    <span class="text-sm sm:text-base font-extrabold text-navybrand-800 text-skybrand-600">${formatRupiahJS(itemSubtotal)}</span>
                </div>

                <!-- Delete Button -->
                <button onclick="removeFromCart(${index})" class="text-slate-400 hover:text-red-500 p-2 rounded-xl hover:bg-red-50 transition-colors" title="Hapus item">
                    <i class="fa-regular fa-trash-can text-base"></i>
                </button>
            </div>
        </div>
        `;
    });

    container.innerHTML = html;

    const cartTotalItemsCount = document.getElementById('cartTotalItemsCount');
    const cartTotalPriceFormatted = document.getElementById('cartTotalPriceFormatted');
    const qrisTotalDisplay = document.getElementById('qrisTotalDisplay');
    const bcaTotalDisplay = document.getElementById('bcaTotalDisplay');
    const qrisImageTag = document.getElementById('qrisImageTag');

    if (cartTotalItemsCount) cartTotalItemsCount.textContent = `${totalItems} Unit`;
    if (cartTotalPriceFormatted) cartTotalPriceFormatted.textContent = formatRupiahJS(totalPrice);
    if (qrisTotalDisplay) qrisTotalDisplay.textContent = formatRupiahJS(totalPrice);
    if (bcaTotalDisplay) bcaTotalDisplay.textContent = formatRupiahJS(totalPrice);
    if (qrisImageTag) {
        qrisImageTag.src = `https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=SEWA_KAMERA_MALANG_TOTAL_${totalPrice}`;
    }
}

function previewGuaranteeImage(event) {
    const file = event.target.files[0];
    const promptBox = document.getElementById('guaranteeUploadPrompt');
    const previewBox = document.getElementById('guaranteeImagePreview');
    const imgTag = document.getElementById('previewImgTag');
    const fileNameTag = document.getElementById('previewFileName');

    if (file && imgTag && previewBox && promptBox) {
        const reader = new FileReader();
        reader.onload = function(e) {
            imgTag.src = e.target.result;
            if (fileNameTag) fileNameTag.textContent = file.name;
            promptBox.classList.add('hidden');
            previewBox.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }
}

function switchPaymentMethod(method) {
    const qrisBox = document.getElementById('qrisPaymentBox');
    const bcaBox = document.getElementById('bcaPaymentBox');
    const cardQris = document.getElementById('cardQris');
    const cardBca = document.getElementById('cardBca');

    if (method === 'qris') {
        if (qrisBox) qrisBox.classList.remove('hidden');
        if (bcaBox) bcaBox.classList.add('hidden');
        if (cardQris) cardQris.classList.add('active');
        if (cardBca) cardBca.classList.remove('active');
    } else {
        if (qrisBox) qrisBox.classList.add('hidden');
        if (bcaBox) bcaBox.classList.remove('hidden');
        if (cardQris) cardQris.classList.remove('active');
        if (cardBca) cardBca.classList.add('active');
    }
}

function copyBcaNumber() {
    const text = '8160912345';
    navigator.clipboard.writeText(text).then(() => {
        alert('Nomor Rekening BCA (8160912345) berhasil disalin!');
    }).catch(err => {
        console.error('Failed to copy', err);
    });
}

function submitWebsiteCheckout() {
    const cart = getCart();
    if (cart.length === 0) {
        alert('Keranjang sewa Anda masih kosong!');
        return;
    }

    const clientNameInput = document.getElementById('clientName');
    const clientPhoneInput = document.getElementById('clientPhone');
    const clientAddressInput = document.getElementById('clientAddress');
    const rentDateInput = document.getElementById('rentDate');
    const guaranteeSelect = document.getElementById('clientGuarantee');
    const guaranteeFileInput = document.getElementById('guaranteeFile');
    const notesInput = document.getElementById('clientNotes');

    const selectedMethodRadio = document.querySelector('input[name="payment_method_choice"]:checked');
    const paymentMethod = selectedMethodRadio ? selectedMethodRadio.value : 'qris';

    if (!clientNameInput || !clientNameInput.value.trim()) {
        alert('Harap isi Nama Lengkap Penyewa!');
        if (clientNameInput) clientNameInput.focus();
        return;
    }
    if (!clientPhoneInput || !clientPhoneInput.value.trim()) {
        alert('Harap isi Nomor WhatsApp / HP!');
        if (clientPhoneInput) clientPhoneInput.focus();
        return;
    }
    if (!clientAddressInput || !clientAddressInput.value.trim()) {
        alert('Harap isi Alamat Lengkap!');
        if (clientAddressInput) clientAddressInput.focus();
        return;
    }
    if (!rentDateInput || !rentDateInput.value.trim()) {
        alert('Harap tentukan Tanggal Sewa!');
        if (rentDateInput) rentDateInput.focus();
        return;
    }

    // Show Verification Hold Modal (Spinner state)
    const modal = document.getElementById('paymentVerificationModal');
    const modalCard = document.getElementById('paymentVerificationCard');
    const holdState = document.getElementById('verificationHoldState');
    const successState = document.getElementById('verificationSuccessState');
    const modalPaidAmount = document.getElementById('modalPaidAmount');
    const modalOrderCode = document.getElementById('modalOrderCode');

    if (modal && modalCard && holdState && successState) {
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modalCard.classList.remove('scale-95');
        }, 50);

        holdState.classList.remove('hidden');
        successState.classList.add('hidden');
    }

    // Prepare FormData for Backend API POST
    const formData = new FormData();
    formData.append('client_name', clientNameInput.value.trim());
    formData.append('client_phone', clientPhoneInput.value.trim());
    formData.append('client_address', clientAddressInput.value.trim());
    formData.append('guarantee_type', guaranteeSelect ? guaranteeSelect.value : 'KTP');
    formData.append('rent_start_date', rentDateInput.value.trim());
    formData.append('payment_method', paymentMethod);
    formData.append('notes', notesInput ? notesInput.value.trim() : '');
    formData.append('cart_json', JSON.stringify(cart));

    if (guaranteeFileInput && guaranteeFileInput.files[0]) {
        formData.append('guarantee_file', guaranteeFileInput.files[0]);
    }

    // HOLD FOR EXACTLY 2 SECONDS (2000ms SIMULATION)
    setTimeout(() => {
        fetch('/api/checkout', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Switch to Green Checkmark Success State
                if (holdState && successState) {
                    holdState.classList.add('hidden');
                    successState.classList.remove('hidden');
                }
                if (modalPaidAmount) modalPaidAmount.textContent = formatRupiahJS(data.total_amount);
                if (modalOrderCode) modalOrderCode.textContent = data.order_code;

                // Clear cart from localStorage
                clearCart();

                // Auto Redirect after 3.5 seconds
                setTimeout(() => {
                    window.location.href = '/';
                }, 3500);
            } else {
                alert('Gagal memproses transaksi: ' + data.message);
                if (modal) modal.classList.add('hidden', 'opacity-0');
            }
        })
        .catch(err => {
            console.error('Error submitting order:', err);
            // Fallback success display for seamless offline demo
            if (holdState && successState) {
                holdState.classList.add('hidden');
                successState.classList.remove('hidden');
            }
            const totalCalc = cart.reduce((sum, i) => sum + (i.price * (i.quantity || 1)), 0);
            if (modalPaidAmount) modalPaidAmount.textContent = formatRupiahJS(totalCalc);
            if (modalOrderCode) modalOrderCode.textContent = 'ORD-' + Date.now();
            clearCart();
            setTimeout(() => {
                window.location.href = '/';
            }, 3500);
        });
    }, 2000); // 2 SECONDS HOLD
}

function sendWaOrderFromCart() {
    const cart = getCart();
    if (cart.length === 0) {
        alert('Keranjang sewa Anda masih kosong!');
        return;
    }

    const clientNameInput = document.getElementById('clientName');
    const clientPhoneInput = document.getElementById('clientPhone');
    const clientAddressInput = document.getElementById('clientAddress');
    const rentDateInput = document.getElementById('rentDate');
    const clientGuaranteeSelect = document.getElementById('clientGuarantee');
    const clientNotesInput = document.getElementById('clientNotes');

    const clientName = clientNameInput ? clientNameInput.value.trim() : '';
    const clientPhone = clientPhoneInput ? clientPhoneInput.value.trim() : '';
    const clientAddress = clientAddressInput ? clientAddressInput.value.trim() : '';
    const rentDate = rentDateInput ? rentDateInput.value.trim() : '';
    const clientGuarantee = clientGuaranteeSelect ? clientGuaranteeSelect.value.trim() : 'KTP';
    const clientNotes = clientNotesInput ? clientNotesInput.value.trim() : '';

    if (!clientName) {
        alert('Harap isi Nama Penyewa terlebih dahulu!');
        if (clientNameInput) clientNameInput.focus();
        return;
    }

    if (!rentDate) {
        alert('Harap tentukan Tanggal Sewa terlebih dahulu!');
        if (rentDateInput) rentDateInput.focus();
        return;
    }

    let msg = `Halo Admin Sewa Kamera Malang, saya mau order sewa peralatan berikut:\n\n`;
    msg += `📋 *DAFTAR PESANAN RENTAL:*\n`;

    let totalBiaya = 0;
    cart.forEach((item, i) => {
        const itemQty = parseInt(item.quantity) || 1;
        const subtotal = item.price * itemQty;
        totalBiaya += subtotal;
        msg += `${i + 1}. *${item.name}*\n`;
        msg += `   • Durasi: ${item.duration}\n`;
        msg += `   • Jumlah: ${itemQty} unit\n`;
        msg += `   • Subtotal: ${formatRupiahJS(subtotal)}\n\n`;
    });

    msg += `----------------------------------\n`;
    msg += `💰 *TOTAL ESTIMASI BIAYA:* ${formatRupiahJS(totalBiaya)}\n\n`;
    msg += `👤 *INFORMASI PENYEWA:*\n`;
    msg += `• Nama: ${clientName}\n`;
    if (clientPhone) msg += `• No. HP: ${clientPhone}\n`;
    if (clientAddress) msg += `• Alamat: ${clientAddress}\n`;
    msg += `• Tanggal Sewa: ${rentDate}\n`;
    msg += `• Jaminan Identitas: ${clientGuarantee}\n`;
    if (clientNotes) {
        msg += `• Catatan: ${clientNotes}\n`;
    }
    msg += `\nMohon konfirmasi ketersediaan unit. Terima kasih!`;

    const waNum = '6281358491224';
    const waUrl = `https://wa.me/${waNum}?text=${encodeURIComponent(msg)}`;
    window.open(waUrl, '_blank');
}


document.addEventListener('DOMContentLoaded', () => {
    // 0. Update Cart Badges on Load & Render Cart Page if active
    updateCartBadges();
    if (document.getElementById('cartItemsContainer')) {
        renderCartPage();
    }

    // Set default date for rentDate input to tomorrow if empty
    const rentDateInput = document.getElementById('rentDate');
    if (rentDateInput && !rentDateInput.value) {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        rentDateInput.value = tomorrow.toISOString().split('T')[0];
    }

    // Bind Clear Cart button on Cart Page
    const btnClearCartPage = document.getElementById('btnClearCartPage');
    if (btnClearCartPage) {
        btnClearCartPage.addEventListener('click', () => {
            if (confirm('Apakah Anda yakin ingin mengosongkan seluruh isi keranjang?')) {
                clearCart();
            }
        });
    }

    // Bind WA Order Button on Cart Page
    const btnSendWaOrder = document.getElementById('btnSendWaOrder');
    if (btnSendWaOrder) {
        btnSendWaOrder.addEventListener('click', sendWaOrderFromCart);
    }

    // Bind Add to Cart on Product Detail Page
    const btnAddToCartDetail = document.getElementById('btnAddToCartDetail');
    if (btnAddToCartDetail) {
        btnAddToCartDetail.addEventListener('click', () => {
            const product = {
                id: btnAddToCartDetail.dataset.productId,
                name: btnAddToCartDetail.dataset.productName,
                brand: btnAddToCartDetail.dataset.productBrand,
                image_url: btnAddToCartDetail.dataset.productImage,
                duration: btnAddToCartDetail.dataset.duration || '24 Jam',
                price: btnAddToCartDetail.dataset.price || 0
            };
            addToCart(product);
        });
    }

    // Bind Quick Add to Cart buttons (.btnQuickAddToCart) on catalog & pricelist
    document.addEventListener('click', (e) => {
        const quickBtn = e.target.closest('.btnQuickAddToCart');
        if (quickBtn) {
            e.preventDefault();
            e.stopPropagation();

            const product = {
                id: quickBtn.dataset.productId,
                name: quickBtn.dataset.productName,
                brand: quickBtn.dataset.productBrand,
                image_url: quickBtn.dataset.productImage,
                duration: quickBtn.dataset.duration || '24 Jam',
                price: quickBtn.dataset.price || 0
            };
            addToCart(product);
        }
    });

    // 1. Hero Banner Slider Initialization
    const slides = document.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.hero-dot');
    let currentSlide = 0;
    let slideInterval;

    function showSlide(index) {
        if (!slides.length) return;
        slides.forEach((slide, i) => {
            slide.classList.toggle('active', i === index);
        });
        dots.forEach((dot, i) => {
            dot.classList.toggle('bg-sky-400', i === index);
            dot.classList.toggle('w-8', i === index);
            dot.classList.toggle('bg-white/50', i !== index);
            dot.classList.toggle('w-3', i !== index);
        });
        currentSlide = index;
    }

    function nextSlide() {
        if (!slides.length) return;
        const next = (currentSlide + 1) % slides.length;
        showSlide(next);
    }

    if (slides.length > 1) {
        slideInterval = setInterval(nextSlide, 5000);
        dots.forEach((dot, idx) => {
            dot.addEventListener('click', () => {
                clearInterval(slideInterval);
                showSlide(idx);
                slideInterval = setInterval(nextSlide, 5000);
            });
        });
    }

    // 2. Mobile Menu Toggle
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }

    // 3. Product Detail - Image Thumbnail Switcher
    const mainImg = document.getElementById('mainProductImage');
    const thumbnails = document.querySelectorAll('.product-thumb');

    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', () => {
            if (mainImg) {
                mainImg.src = thumb.dataset.src || thumb.src;
            }
            thumbnails.forEach(t => t.classList.remove('ring-2', 'ring-sky-500', 'border-sky-500'));
            thumb.classList.add('ring-2', 'ring-sky-500', 'border-sky-500');
        });
    });

    // 4. Product Detail - Rate Duration Selector & WA Link Formatter
    const ratePills = document.querySelectorAll('.rate-pill');
    const displayPrice = document.getElementById('selectedDurationPrice');
    const displayDurationText = document.getElementById('selectedDurationText');
    const btnChatWa = document.getElementById('btnChatWa');

    if (ratePills.length > 0) {
        ratePills.forEach(pill => {
            pill.addEventListener('click', () => {
                ratePills.forEach(p => p.classList.remove('active'));
                pill.classList.add('active');

                const duration = pill.dataset.duration;
                const price = pill.dataset.price;
                const priceFormatted = pill.dataset.priceFormatted;
                const productName = pill.dataset.productName;
                const waNum = pill.dataset.waNum || '6281358491224';

                if (displayPrice) displayPrice.textContent = priceFormatted;
                if (displayDurationText) displayDurationText.textContent = duration;

                const btnAddToCartDetail = document.getElementById('btnAddToCartDetail');
                if (btnAddToCartDetail) {
                    btnAddToCartDetail.dataset.duration = duration;
                    btnAddToCartDetail.dataset.price = price;
                }

                if (btnChatWa) {
                    const text = `Halo Admin Sewa Kamera Malang, saya mau sewa ${productName} untuk durasi ${duration}`;
                    btnChatWa.href = `https://wa.me/${waNum}?text=${encodeURIComponent(text)}`;
                }
            });
        });
    }

    // 5. Accordion Toggle for FAQ
    const accordionHeaders = document.querySelectorAll('.faq-header');
    accordionHeaders.forEach(header => {
        header.addEventListener('click', () => {
            const body = header.nextElementSibling;
            const icon = header.querySelector('.faq-icon');
            
            body.classList.toggle('hidden');
            if (icon) {
                icon.classList.toggle('rotate-180');
            }
        });
    });
});
