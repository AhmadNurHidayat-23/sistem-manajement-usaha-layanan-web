@php
    $roleName = auth()->user()?->role?->name;

    $menuData = match($roleName) {
        'owner' => [
            [
                'title' => 'Dashboard',
                'section' => 'Utama',
                'url' => route('owner.dashboard'),
                'icon' => 'dashboard',
                'keywords' => 'beranda home ringkasan performa omset',
            ],
            [
                'title' => 'Cabang',
                'section' => 'Manajemen Bisnis',
                'url' => route('branches.index'),
                'icon' => 'branch',
                'keywords' => 'outlet unit lokasi kantor toko cabang usaha',
            ],
            [
                'title' => 'Katalog Layanan',
                'section' => 'Manajemen Bisnis',
                'url' => route('catalog.services.index'),
                'icon' => 'service',
                'keywords' => 'jasa servis service produk paket tarif harga kategori',
            ],
            [
                'title' => 'Promo & Voucher',
                'section' => 'Manajemen Bisnis',
                'url' => route('promotions.index'),
                'icon' => 'ticket',
                'keywords' => 'diskon voucher kupon promo promosi potongan cashback',
            ],
            [
                'title' => 'Data Karyawan',
                'section' => 'Karyawan',
                'url' => route('employees.index'),
                'icon' => 'employee',
                'keywords' => 'pegawai staf staff pekerja karyawan teknisi spesialis',
            ],
            [
                'title' => 'Skema & Komisi',
                'section' => 'Karyawan',
                'url' => route('employees.commissions.index'),
                'icon' => 'money',
                'keywords' => 'komisi bonus insentif bagi hasil skema uang gaji',
            ],
            [
                'title' => 'Akun & Hak Akses',
                'section' => 'Administrasi',
                'url' => route('owner.internal-users.index'),
                'icon' => 'user',
                'keywords' => 'user role pengguna akun internal hak akses permission manajer kasir',
            ],
            [
                'title' => 'Laporan Keuangan',
                'section' => 'Laporan',
                'url' => route('reports.index'),
                'icon' => 'report',
                'keywords' => 'laporan omset finansial laba rugi transaksi keuangan report rekap',
            ],
            [
                'title' => 'Log Aktivitas',
                'section' => 'Sistem',
                'url' => route('aktivitas.index'),
                'icon' => 'history',
                'keywords' => 'audit log aktivitas riwayat aksi sistem jejak',
            ],
            [
                'title' => 'Log Notifikasi WhatsApp',
                'section' => 'Sistem',
                'url' => route('notifikasi.index'),
                'icon' => 'bell',
                'keywords' => 'wa whatsapp pesan notifikasi pengingat blast status kirim',
            ],
        ],
        'manager' => [
            [
                'title' => 'Dashboard',
                'section' => 'Utama',
                'url' => route('manager.dashboard'),
                'icon' => 'dashboard',
                'keywords' => 'beranda home ringkasan performa cabang operasional',
            ],
            [
                'title' => 'Jadwal Shift',
                'section' => 'Operasional',
                'url' => route('employees.shifts.index'),
                'icon' => 'clock',
                'keywords' => 'shift jam kerja jadwal giliran kalender karyawan',
            ],
            [
                'title' => 'Inventori Bahan',
                'section' => 'Operasional',
                'url' => route('inventori.index'),
                'icon' => 'box',
                'keywords' => 'stok stock barang bahan baku logistik perlengkapan opname',
            ],
            [
                'title' => 'Promo & Voucher',
                'section' => 'Manajemen',
                'url' => route('promotions.index'),
                'icon' => 'ticket',
                'keywords' => 'diskon promo voucher kupon potongan promosi',
            ],
            [
                'title' => 'Laporan Keuangan',
                'section' => 'Laporan',
                'url' => route('reports.index'),
                'icon' => 'report',
                'keywords' => 'laporan omset finansial laba rugi keuangan report cabang',
            ],
            [
                'title' => 'Log Aktivitas',
                'section' => 'Sistem',
                'url' => route('aktivitas.index'),
                'icon' => 'history',
                'keywords' => 'audit log aktivitas riwayat aksi sistem jejak',
            ],
            [
                'title' => 'Log Notifikasi WhatsApp',
                'section' => 'Sistem',
                'url' => route('notifikasi.index'),
                'icon' => 'bell',
                'keywords' => 'wa whatsapp pesan notifikasi status kirim',
            ],
        ],
        'cashier' => [
            [
                'title' => 'Dashboard',
                'section' => 'Utama',
                'url' => route('cashier.dashboard'),
                'icon' => 'dashboard',
                'keywords' => 'beranda home ringkasan kasir shift transaksi',
            ],
            [
                'title' => 'Terminal POS',
                'section' => 'Operasional',
                'url' => route('pos.index'),
                'icon' => 'cart',
                'keywords' => 'kasir bayar transaksi order penjualan jual point of sale pos pesanan checkout',
            ],
            [
                'title' => 'Riwayat Transaksi',
                'section' => 'Operasional',
                'url' => route('pos.transactions.index'),
                'icon' => 'receipt',
                'keywords' => 'transaksi nota struk riwayat history invoice pembayaran cetak lunas',
            ],
            [
                'title' => 'Shift Kasir',
                'section' => 'Operasional',
                'url' => route('pos.shift.index'),
                'icon' => 'clock',
                'keywords' => 'shift buka tutup modal kas kasir saldo awal akhir balance',
            ],
            [
                'title' => 'Member',
                'section' => 'Pelanggan',
                'url' => route('members.index'),
                'icon' => 'user',
                'keywords' => 'pelanggan member loyalitas anggota konsumen poin pendaftaran',
            ],
        ],
        'employee' => [
            [
                'title' => 'Dashboard',
                'section' => 'Utama',
                'url' => route('employee.dashboard'),
                'icon' => 'dashboard',
                'keywords' => 'beranda home ringkasan tugas hari ini pengerjaan',
            ],
            [
                'title' => 'Antrean & Tugas',
                'section' => 'Operasional',
                'url' => route('queue.index'),
                'icon' => 'clock',
                'keywords' => 'antrean antrian order pesanan tugas pengerjaan service kerjaan antri mulai selesai',
            ],
            [
                'title' => 'Jadwal Shift',
                'section' => 'Operasional',
                'url' => route('employees.my-shifts'),
                'icon' => 'calendar',
                'keywords' => 'jadwal kerja shift kalender jam tugas saya giliran',
            ],
            [
                'title' => 'Komisi Saya',
                'section' => 'Kinerja',
                'url' => route('employees.my-commissions'),
                'icon' => 'star',
                'keywords' => 'insentif komisi bonus capaian kinerja pendapatan uang reward poin',
            ],
        ],
        default => [],
    };
@endphp

<div class="relative flex items-center" id="menu-search-wrapper" data-menus='@json($menuData)'>
    <div class="relative flex items-center">
        {{-- Ikon Pencarian --}}
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#667085]">
            <x-icons.icon name="search" class="w-4 h-4 text-[#667085]" />
        </div>

        {{-- Input Pencarian Pill Rounded-Full --}}
        <input type="text"
               id="menu-search-input"
               placeholder="Cari menu..."
               autocomplete="off"
               spellcheck="false"
               aria-label="Cari menu navigasi"
               class="w-32 xs:w-40 sm:w-48 md:w-56 lg:w-64 h-9 pl-9 pr-7 rounded-full text-xs sm:text-[13px] bg-[#F5F8FC] border border-[#E4E7EC] hover:border-[#D0D5DD] text-[#101828] placeholder-[#98A2B3] focus:outline-hidden focus:ring-2 focus:ring-[#062072]/15 focus:border-[#062072] focus:bg-white focus:w-44 xs:focus:w-52 sm:focus:w-56 md:focus:w-64 transition-all duration-200">

        {{-- Tombol Hapus Input --}}
        <button type="button"
                id="menu-search-clear"
                class="hidden absolute inset-y-0 right-0 pr-2.5 flex items-center text-[#98A2B3] hover:text-[#101828] transition-colors cursor-pointer bg-transparent border-0"
                title="Hapus pencarian"
                aria-label="Hapus pencarian">
            <x-icons.icon name="close" class="w-3.5 h-3.5" />
        </button>
    </div>

    {{-- Pesan Singkat Jika Menu Tidak Ditemukan (Clean, Tanpa Dropdown / Popup Besar) --}}
    <div id="menu-search-feedback"
         class="hidden absolute left-1/2 -translate-x-1/2 top-full mt-1.5 px-3 py-1 bg-[#101828] text-white text-[11px] font-medium rounded-full shadow-lg whitespace-nowrap z-50 pointer-events-none transition-opacity duration-150">
        Menu tidak ditemukan
    </div>
</div>
