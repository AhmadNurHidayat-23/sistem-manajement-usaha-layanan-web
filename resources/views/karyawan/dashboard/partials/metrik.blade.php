<!-- Tiga Kartu Metrik Utama: Shift Hari Ini, Layanan Selesai, dan Estimasi Komisi -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <!-- Shift Hari Ini -->
    <x-cards.metric
        :title="__('dashboard.employee.today_shift')"
        :value="$todayShift ? \Carbon\Carbon::parse($todayShift->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($todayShift->end_time)->format('H:i') : __('dashboard.employee.off')"
        :subtitle="$todayShift ? (__('dashboard.status.' . $todayShift->status->value) ?? $todayShift->status->label()) . ' • ' . $todayShift->branch->display_name : __('dashboard.employee.no_active_schedule_today')"
        icon="clock"
        variant="primary"
    />

    <!-- Layanan Selesai -->
    <x-cards.metric
        :title="__('dashboard.employee.completed_services')"
        :value="__('dashboard.employee.tasks_count', ['count' => $performance['completed_bookings']])"
        :subtitle="__('dashboard.employee.assigned_orders_summary', ['total' => $performance['total_bookings_assigned']])"
        icon="check"
        variant="primary"
    />

    <!-- Estimasi Komisi -->
    <x-cards.metric
        :title="__('dashboard.employee.estimated_commission')"
        :value="'Rp ' . number_format($performance['pending_commission'], 0, ',', '.')"
        :subtitle="__('dashboard.employee.paid_commission_subtitle', ['amount' => 'Rp ' . number_format($performance['paid_commission'], 0, ',', '.')])"
        icon="money"
        variant="primary"
    />
</div>
