<!-- Tiga Kartu Metrik Utama: Shift Hari Ini, Layanan Selesai, dan Estimasi Komisi -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <!-- Shift Hari Ini -->
    <x-cards.metric
        title="Shift Hari Ini"
        :value="$todayShift ? \Carbon\Carbon::parse($todayShift->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($todayShift->end_time)->format('H:i') : 'Libur'"
        :subtitle="$todayShift ? ucfirst($todayShift->status->value) . ' • ' . $todayShift->branch->display_name : 'Tidak ada jadwal aktif hari ini'"
        icon="clock"
        variant="primary"
    />

    <!-- Layanan Selesai -->
    <x-cards.metric
        title="Layanan Selesai"
        :value="$performance['completed_bookings'] . ' Tugas'"
        :subtitle="'Dari ' . $performance['total_bookings_assigned'] . ' total order ditugaskan'"
        icon="check"
        variant="primary"
    />

    <!-- Estimasi Komisi -->
    <x-cards.metric
        title="Estimasi Komisi (Pending)"
        :value="'Rp ' . number_format($performance['pending_commission'], 0, ',', '.')"
        :subtitle="'Cair: Rp ' . number_format($performance['paid_commission'], 0, ',', '.')"
        icon="money"
        variant="primary"
    />
</div>
