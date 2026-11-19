<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- 1. Omzet Cabang (SRS Wajib) -->
    <x-cards.metric
        :title="__('dashboard.manager.today_omzet')"
        :value="'Rp ' . number_format($metrics['today_omzet'], 0, ',', '.')"
        :subtitle="__('dashboard.manager.this_month_omzet', ['amount' => 'Rp ' . number_format($metrics['month_omzet'], 0, ',', '.')])"
        icon="money"
        variant="emerald"
    />

    <!-- 2. Jumlah Transaksi (SRS Wajib) -->
    <x-cards.metric
        :title="__('dashboard.manager.today_transactions')"
        :value="number_format($metrics['today_transactions']) . ' ' . __('dashboard.units.transactions')"
        :subtitle="__('dashboard.manager.this_month_transactions', ['count' => number_format($metrics['month_transactions'])])"
        icon="transaction"
        variant="primary"
    />

    <!-- 3. Tingkat Kehadiran Booking (SRS Wajib - 'Tidak berlaku' untuk Laundry) -->
    <x-cards.metric
        :title="__('dashboard.manager.attendance_rate')"
        :value="$attendance['label']"
        :subtitle="$attendance['subtitle']"
        icon="calendar"
        variant="navy"
    />

    <!-- 4. Karyawan Bertugas Hari Ini (Data Pendukung Operasional) -->
    <x-cards.metric
        :title="__('dashboard.manager.staff_on_duty')"
        :value="number_format($metrics['active_employees_on_duty']) . ' ' . __('dashboard.units.staff')"
        :subtitle="__('dashboard.manager.active_shifts_today')"
        icon="employee"
        variant="amber"
    />
</div>
