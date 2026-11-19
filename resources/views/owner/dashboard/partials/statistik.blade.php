<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Total Cabang Aktif -->
    <x-cards.metric
        :title="__('dashboard.owner.total_active_branches')"
        :value="number_format($metrics['total_active_branches']) . ' ' . __('dashboard.units.outlets')"
        :subtitle="__('dashboard.owner.branches_subtitle')"
        icon="branch"
        variant="primary"
    />

    <!-- Total Omzet Seluruh Cabang -->
    <x-cards.metric
        :title="__('dashboard.owner.total_omzet')"
        :value="'Rp ' . number_format($metrics['total_omzet'], 0, ',', '.')"
        :subtitle="__('dashboard.owner.omzet_subtitle')"
        icon="money"
        variant="primary"
    />

    <!-- Jumlah Transaksi -->
    <x-cards.metric
        :title="__('dashboard.owner.total_transactions')"
        :value="number_format($metrics['total_transactions']) . ' ' . __('dashboard.units.transactions')"
        :subtitle="__('dashboard.owner.transactions_subtitle')"
        icon="transaction"
        variant="primary"
    />

    <!-- Total Booking -->
    <x-cards.metric
        :title="__('dashboard.owner.total_bookings')"
        :value="number_format($metrics['total_bookings']) . ' ' . __('dashboard.units.orders')"
        :subtitle="__('dashboard.owner.bookings_subtitle')"
        icon="booking"
        variant="primary"
    />
</div>
