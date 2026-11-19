/**
 * Modul Lokasi Antar-Jemput Pesanan (Leaflet + OpenStreetMap + Nominatim Reverse Geocoding)
 */

/**
 * Format payload Nominatim OSM menjadi string alamat Indonesia yang rapi:
 * Jalan, No (jika ada), Kelurahan/Desa, Kecamatan, Kota/Kabupaten, Kode Pos.
 *
 * @param {Object} data
 * @returns {string}
 */
export function formatNominatimAddress(data) {
    if (!data) return '';
    const addr = data.address || {};

    const parts = [];

    // 1. Jalan & Nomor Rumah
    let street = addr.road || addr.street || addr.pedestrian || addr.highway || addr.path || addr.cycleway || '';
    if (street) {
        if (addr.house_number) {
            street += ' No. ' + addr.house_number;
        }
        parts.push(street);
    }

    // 2. Lingkungan / Perumahan / Komplek
    const complex = addr.residential || addr.industrial || addr.commercial || '';
    if (complex && !parts.includes(complex)) {
        parts.push(complex);
    }

    // 3. Kelurahan / Desa / Lingkungan
    const subVillage = addr.neighbourhood || addr.quarter || '';
    const village = addr.village || addr.suburb || addr.hamlet || '';
    if (subVillage && !parts.includes(subVillage)) parts.push(subVillage);
    if (village && !parts.includes(village)) parts.push(village);

    // 4. Kecamatan
    const district = addr.city_district || addr.subdistrict || addr.municipality || '';
    if (district && !parts.includes(district)) parts.push(district);

    // 5. Kota / Kabupaten
    const city = addr.city || addr.town || addr.regency || addr.county || '';
    if (city && !parts.includes(city)) parts.push(city);

    // 6. Kode Pos
    if (addr.postcode && !parts.includes(addr.postcode)) {
        parts.push(addr.postcode);
    }

    if (parts.length >= 2) {
        return parts.join(', ');
    }

    return data.display_name || parts.join(', ');
}

// Controller pembatalan request pending untuk mencegah race conditions saat geser marker cepat
let activePickupAbort = null;
let activeDeliveryAbort = null;

/**
 * Lakukan reverse geocoding dari koordinat GPS ke alamat menggunakan Nominatim OpenStreetMap.
 *
 * @param {number} lat
 * @param {number} lng
 * @param {'pickup'|'delivery'} target
 */
export async function reverseGeocodeCoordinates(lat, lng, target = 'pickup') {
    const isPickup = target === 'pickup';
    const addressInput = document.getElementById(isPickup ? 'pickup_address' : 'delivery_address');
    const statusEl = document.getElementById(isPickup ? 'pickup-address-status' : 'delivery-address-status');
    const errorEl = document.getElementById(isPickup ? 'error-pickup-address' : 'error-delivery-address');

    if (!addressInput) return;

    // Batalkan request yang sedang berjalan untuk target ini
    if (isPickup && activePickupAbort) {
        activePickupAbort.abort();
    } else if (!isPickup && activeDeliveryAbort) {
        activeDeliveryAbort.abort();
    }

    const controller = new AbortController();
    if (isPickup) activePickupAbort = controller;
    else activeDeliveryAbort = controller;

    // Tampilkan indikator loading pencarian alamat
    if (statusEl) {
        statusEl.className = 'inline-flex items-center gap-1.5 text-xs text-[#062072] dark:text-[#93C5FD] font-medium';
        statusEl.innerHTML = `
            <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span>Mencari alamat...</span>
        `;
    }

    const timeoutId = setTimeout(() => controller.abort(), 10000);

    try {
        let nominatimData = null;

        // 1. Prioritaskan endpoint server-side proxy SIMAUL (bebas CORS & mematuhi TOS OSM)
        try {
            const internalUrl = `/customer/pesanan/reverse-geocode?lat=${encodeURIComponent(lat)}&lng=${encodeURIComponent(lng)}`;
            const intRes = await fetch(internalUrl, {
                signal: controller.signal,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (intRes.ok) {
                const intJson = await intRes.json();
                if (intJson.success && intJson.data) {
                    nominatimData = intJson.data;
                }
            }
        } catch (intErr) {
            if (intErr.name === 'AbortError') throw intErr;
            console.warn('Gagal memanggil endpoint proxy reverse-geocode SIMAUL:', intErr);
        }

        // 2. Fallback direct client fetch ke Nominatim OSM jika endpoint internal gagal
        if (!nominatimData) {
            const extUrl = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}&zoom=18&addressdetails=1&accept-language=id`;
            const extRes = await fetch(extUrl, {
                signal: controller.signal,
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (extRes.ok) {
                nominatimData = await extRes.json();
            }
        }

        clearTimeout(timeoutId);

        if (!nominatimData) {
            throw new Error('Alamat tidak dapat ditemukan dari koordinat ini.');
        }

        const formatted = formatNominatimAddress(nominatimData);

        if (formatted && formatted.trim().length > 0) {
            addressInput.value = formatted;
            addressInput.dispatchEvent(new Event('input', { bubbles: true }));
            addressInput.dispatchEvent(new Event('change', { bubbles: true }));

            // Hilangkan pesan error validasi "Alamat wajib diisi"
            if (errorEl) {
                errorEl.classList.add('hidden');
            }
            const parentErr = addressInput.parentElement?.querySelector('.text-rose-600');
            if (parentErr) {
                parentErr.classList.add('hidden');
            }

            // Tampilkan indikator sukses terisi (pelanggan tetap dapat mengedit manual)
            if (statusEl) {
                statusEl.className = 'inline-flex items-center gap-1 text-xs text-emerald-600 dark:text-emerald-400 font-medium';
                statusEl.innerHTML = `
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Alamat terdeteksi (dapat disunting)</span>
                `;
                setTimeout(() => {
                    if (statusEl.textContent.includes('Alamat terdeteksi')) {
                        statusEl.className = 'hidden';
                        statusEl.innerHTML = '';
                    }
                }, 4000);
            }
        } else {
            throw new Error('Alamat tidak ditemukan.');
        }
    } catch (err) {
        if (err.name === 'AbortError') {
            return;
        }
        console.warn('Gagal reverse geocoding lokasi:', err);

        // Jika geocoding gagal, tampilkan pesan informatif tanpa mengunci kolom
        if (statusEl) {
            statusEl.className = 'inline-flex items-center gap-1 text-xs text-amber-600 dark:text-amber-400 font-medium';
            statusEl.innerHTML = `
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Alamat tidak ditemukan otomatis. Silakan ketik manual.</span>
            `;
        }
    } finally {
        if (isPickup) activePickupAbort = null;
        else activeDeliveryAbort = null;
    }
}

export function initPickupLocation() {
    const pickupOption = document.getElementById('pickup_option');
    const locationSection = document.getElementById('pickup-location-section');
    const diffDeliveryCheck = document.getElementById('different_delivery_location');
    const deliveryContainer = document.getElementById('delivery-location-container');

    if (!pickupOption || !locationSection) return;
    if (pickupOption.dataset.locationBound === '1') return;
    pickupOption.dataset.locationBound = '1';

    let pickupMap = null;
    let pickupMarker = null;
    let deliveryMap = null;
    let deliveryMarker = null;

    // Listener input manual agar pesan error hilang saat mulai mengetik
    const bindManualInputClear = (inputId, errorId) => {
        const el = document.getElementById(inputId);
        if (el && !el.dataset.errorClearBound) {
            el.dataset.errorClearBound = '1';
            el.addEventListener('input', () => {
                if (el.value.trim().length > 0) {
                    const err = document.getElementById(errorId) || el.parentElement?.querySelector('.text-rose-600');
                    if (err) err.classList.add('hidden');
                }
            });
        }
    };

    bindManualInputClear('pickup_address', 'error-pickup-address');
    bindManualInputClear('delivery_address', 'error-delivery-address');

    // Toggle visibilitas formulir lokasi antar-jemput
    function updatePickupVisibility() {
        if (pickupOption.checked) {
            locationSection.classList.remove('hidden');
            setTimeout(() => {
                ensurePickupMap();
                if (diffDeliveryCheck && diffDeliveryCheck.checked) {
                    ensureDeliveryMap();
                }
            }, 100);
        } else {
            locationSection.classList.add('hidden');
        }
    }

    pickupOption.addEventListener('change', updatePickupVisibility);

    // Toggle lokasi pengantaran berbeda
    if (diffDeliveryCheck && deliveryContainer) {
        diffDeliveryCheck.addEventListener('change', () => {
            if (diffDeliveryCheck.checked) {
                deliveryContainer.classList.remove('hidden');
                setTimeout(() => {
                    ensureDeliveryMap();
                }, 100);
            } else {
                deliveryContainer.classList.add('hidden');
            }
        });
    }

    // Inisialisasi Peta Penjemputan
    function ensurePickupMap() {
        if (typeof window.L === 'undefined') {
            setTimeout(ensurePickupMap, 200);
            return;
        }

        const mapEl = document.getElementById('pickup-map');
        const latInput = document.getElementById('pickup_latitude');
        const lngInput = document.getElementById('pickup_longitude');
        const coordsText = document.getElementById('text-pickup-coords');
        const btnGps = document.getElementById('btn-gps-pickup');

        if (!mapEl) return;

        let lat = parseFloat(latInput?.value) || -6.2088;
        let lng = parseFloat(lngInput?.value) || 106.8456;

        if (!pickupMap) {
            pickupMap = window.L.map('pickup-map').setView([lat, lng], 15);
            window.pickupMapInstance = pickupMap;
            window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(pickupMap);

            pickupMarker = window.L.marker([lat, lng], { draggable: true }).addTo(pickupMap);

            function updateCoords(newLat, newLng) {
                if (latInput) latInput.value = newLat.toFixed(6);
                if (lngInput) lngInput.value = newLng.toFixed(6);
                if (coordsText) coordsText.textContent = `${newLat.toFixed(6)}, ${newLng.toFixed(6)}`;
            }

            // Saat pin digeser (dragend) -> perbarui koordinat dan cari alamat otomatis
            pickupMarker.on('dragend', () => {
                const pos = pickupMarker.getLatLng();
                updateCoords(pos.lat, pos.lng);
                reverseGeocodeCoordinates(pos.lat, pos.lng, 'pickup');
            });

            // Saat peta diklik -> pindahkan pin dan cari alamat otomatis
            pickupMap.on('click', (e) => {
                pickupMarker.setLatLng(e.latlng);
                updateCoords(e.latlng.lat, e.latlng.lng);
                reverseGeocodeCoordinates(e.latlng.lat, e.latlng.lng, 'pickup');
            });

            // Tombol "Gunakan Lokasi Saya" (GPS Browser)
            btnGps?.addEventListener('click', () => {
                if (!navigator.geolocation) {
                    alert('Browser Anda tidak mendukung deteksi lokasi otomatis.');
                    return;
                }
                const originalHtml = btnGps.innerHTML;
                btnGps.disabled = true;
                btnGps.innerHTML = `
                    <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    Mendeteksi Lokasi...
                `;

                const fetchGeo = (highAccuracy = true) => {
                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            btnGps.disabled = false;
                            btnGps.innerHTML = originalHtml;
                            const userLat = pos.coords.latitude;
                            const userLng = pos.coords.longitude;
                            pickupMap.setView([userLat, userLng], 16);
                            pickupMarker.setLatLng([userLat, userLng]);
                            updateCoords(userLat, userLng);
                            // Ambil alamat otomatis via proxy geocode
                            reverseGeocodeCoordinates(userLat, userLng, 'pickup');
                        },
                        (err) => {
                            if (highAccuracy && err.code === 3) {
                                // Timeout pada akurasi tinggi, coba lagi dengan akurasi standar
                                fetchGeo(false);
                                return;
                            }
                            btnGps.disabled = false;
                            btnGps.innerHTML = originalHtml;
                            console.warn('Geolocation error:', err);
                            alert('Izin lokasi tidak diberikan atau GPS tidak aktif. Anda tetap dapat menggeser pin pada peta atau mengetik alamat secara manual.');
                        },
                        { enableHighAccuracy: highAccuracy, timeout: 8000, maximumAge: 60000 }
                    );
                };
                fetchGeo(true);
            });
        } else {
            pickupMap.invalidateSize();
        }
    }

    // Inisialisasi Peta Pengantaran
    function ensureDeliveryMap() {
        if (typeof window.L === 'undefined') {
            setTimeout(ensureDeliveryMap, 200);
            return;
        }

        const mapEl = document.getElementById('delivery-map');
        const latInput = document.getElementById('delivery_latitude');
        const lngInput = document.getElementById('delivery_longitude');
        const coordsText = document.getElementById('text-delivery-coords');
        const btnGps = document.getElementById('btn-gps-delivery');

        if (!mapEl) return;

        let lat = parseFloat(latInput?.value) || -6.2088;
        let lng = parseFloat(lngInput?.value) || 106.8456;

        if (!deliveryMap) {
            deliveryMap = window.L.map('delivery-map').setView([lat, lng], 15);
            window.deliveryMapInstance = deliveryMap;
            window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(deliveryMap);

            deliveryMarker = window.L.marker([lat, lng], { draggable: true }).addTo(deliveryMap);

            function updateCoords(newLat, newLng) {
                if (latInput) latInput.value = newLat.toFixed(6);
                if (lngInput) lngInput.value = newLng.toFixed(6);
                if (coordsText) coordsText.textContent = `${newLat.toFixed(6)}, ${newLng.toFixed(6)}`;
            }

            // Saat pin pengantaran digeser (dragend) -> perbarui koordinat dan cari alamat otomatis
            deliveryMarker.on('dragend', () => {
                const pos = deliveryMarker.getLatLng();
                updateCoords(pos.lat, pos.lng);
                reverseGeocodeCoordinates(pos.lat, pos.lng, 'delivery');
            });

            // Saat peta pengantaran diklik -> pindahkan pin dan cari alamat otomatis
            deliveryMap.on('click', (e) => {
                deliveryMarker.setLatLng(e.latlng);
                updateCoords(e.latlng.lat, e.latlng.lng);
                reverseGeocodeCoordinates(e.latlng.lat, e.latlng.lng, 'delivery');
            });

            // Tombol "Gunakan Lokasi Saya" untuk Pengantaran
            btnGps?.addEventListener('click', () => {
                if (!navigator.geolocation) {
                    alert('Browser Anda tidak mendukung deteksi lokasi otomatis.');
                    return;
                }
                const originalHtml = btnGps.innerHTML;
                btnGps.disabled = true;
                btnGps.innerHTML = `
                    <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    Mendeteksi Lokasi...
                `;

                const fetchGeo = (highAccuracy = true) => {
                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            btnGps.disabled = false;
                            btnGps.innerHTML = originalHtml;
                            const userLat = pos.coords.latitude;
                            const userLng = pos.coords.longitude;
                            deliveryMap.setView([userLat, userLng], 16);
                            deliveryMarker.setLatLng([userLat, userLng]);
                            updateCoords(userLat, userLng);
                            // Ambil alamat otomatis via proxy geocode
                            reverseGeocodeCoordinates(userLat, userLng, 'delivery');
                        },
                        (err) => {
                            if (highAccuracy && err.code === 3) {
                                fetchGeo(false);
                                return;
                            }
                            btnGps.disabled = false;
                            btnGps.innerHTML = originalHtml;
                            console.warn('Geolocation error:', err);
                            alert('Izin lokasi tidak diberikan atau GPS tidak aktif. Anda tetap dapat menggeser pin pada peta atau mengetik alamat secara manual.');
                        },
                        { enableHighAccuracy: highAccuracy, timeout: 8000, maximumAge: 60000 }
                    );
                };
                fetchGeo(true);
            });
        } else {
            deliveryMap.invalidateSize();
        }
    }

    // Inisialisasi awal jika form terisi kembali (misal saat error validasi)
    if (pickupOption.checked) {
        updatePickupVisibility();
    }

    // Tangani saat modal-buat-pesanan dibuka agar ukuran Leaflet responsif dan akurat
    document.addEventListener('modal-opened', (e) => {
        if (e.detail?.id === 'modal-buat-pesanan' || e.target?.id === 'modal-buat-pesanan') {
            if (pickupOption && pickupOption.checked) {
                ensurePickupMap();
                if (diffDeliveryCheck && diffDeliveryCheck.checked) {
                    ensureDeliveryMap();
                }
            }
            invalidatePickupMaps();
        }
    });
}

export function invalidatePickupMaps() {
    setTimeout(() => {
        const mapEl = document.getElementById('pickup-map');
        if (mapEl && window.pickupMapInstance) {
            window.pickupMapInstance.invalidateSize();
        }
        const delEl = document.getElementById('delivery-map');
        if (delEl && window.deliveryMapInstance) {
            window.deliveryMapInstance.invalidateSize();
        }
    }, 180);
}

if (typeof window !== 'undefined') {
    window.initPickupLocation = initPickupLocation;
    window.formatNominatimAddress = formatNominatimAddress;
    window.reverseGeocodeCoordinates = reverseGeocodeCoordinates;
    window.invalidatePickupMaps = invalidatePickupMaps;
}
