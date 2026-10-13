<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- 1. Omzet Cabang (SRS Wajib) -->
    <x-cards.metric
        title="Omzet Cabang Hari Ini"
        :value="'Rp ' . number_format($metrics['today_omzet'], 0, ',', '.')"
        :subtitle="'Bulan ini: Rp ' . number_format($metrics['month_omzet'], 0, ',', '.')"
        icon="money"
        variant="emerald"
    />

    <!-- 2. Jumlah Transaksi (SRS Wajib) -->
    <x-cards.metric
        title="Jumlah Transaksi"
        :value="number_format($metrics['today_transactions']) . ' Trx'"
        :subtitle="'Bulan ini: ' . number_format($metrics['month_transactions']) . ' transaksi lunas'"
        icon="transaction"
        variant="primary"
    />

    <!-- 3. Tingkat Kehadiran Booking (SRS Wajib - 'Tidak berlaku' untuk Laundry) -->
    <x-cards.metric
        title="Kehadiran Booking"
        :value="$attendance['label']"
        :subtitle="$attendance['subtitle']"
        icon="calendar"
        variant="navy"
    />

    <!-- 4. Karyawan Bertugas Hari Ini (Data Pendukung Operasional) -->
    <x-cards.metric
        title="Karyawan Bertugas"
        :value="number_format($metrics['active_employees_on_duty']) . ' Staf'"
        subtitle="Shift aktif & terjadwal hari ini"
        icon="employee"
        variant="amber"
    />
</div>
