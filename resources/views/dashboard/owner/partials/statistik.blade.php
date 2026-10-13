<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Total Cabang Aktif -->
    <x-cards.metric
        title="Total Cabang Aktif"
        :value="number_format($metrics['total_active_branches']) . ' Outlet'"
        subtitle="Operasional aktif saat ini"
        icon="branch"
        variant="primary"
    />

    <!-- Total Omzet Seluruh Cabang -->
    <x-cards.metric
        title="Total Omzet Bisnis"
        :value="'Rp ' . number_format($metrics['total_omzet'], 0, ',', '.')"
        subtitle="Akumulasi seluruh outlet"
        icon="money"
        variant="primary"
    />

    <!-- Jumlah Transaksi -->
    <x-cards.metric
        title="Jumlah Transaksi"
        :value="number_format($metrics['total_transactions']) . ' Trx'"
        subtitle="Transaksi kasir selesai & lunas"
        icon="transaction"
        variant="primary"
    />

    <!-- Total Booking -->
    <x-cards.metric
        title="Total Booking & Antrian"
        :value="number_format($metrics['total_bookings']) . ' Order'"
        subtitle="Total reservasi layanan"
        icon="booking"
        variant="primary"
    />
</div>
