@props([
    'status',
])

@php
    $val = $status instanceof \BackedEnum ? $status->value : (string) $status;
    $val = strtolower($val);

    $normalized = match($val) {
        '1', 'active' => 'active',
        '0', 'inactive' => 'inactive',
        'completed_trx', 'paid' => 'paid',
        'tersedia', 'available' => 'available',
        'menipis', 'low' => 'low',
        'habis', 'out_of_stock' => 'out_of_stock',
        'berakhir', 'ended' => 'ended',
        'ready', 'siap_diambil' => 'ready',
        'received', 'diterima' => 'received',
        default => $val,
    };

    $variantMap = [
        'completed' => 'success',
        'confirmed' => 'primary',
        'in_progress' => 'primary',
        'pending' => 'warning',
        'cancelled' => 'danger',
        'processing' => 'primary',
        'ready' => 'success',
        'received' => 'primary',
        'present' => 'success',
        'late' => 'warning',
        'absent' => 'danger',
        'scheduled' => 'neutral',
        'ended' => 'neutral',
        'off' => 'neutral',
        'paid' => 'success',
        'unpaid' => 'warning',
        'failed' => 'danger',
        'refunded' => 'danger',
        'active' => 'success',
        'inactive' => 'neutral',
        'available' => 'success',
        'low' => 'warning',
        'out_of_stock' => 'danger',
        'in' => 'success',
        'out' => 'danger',
        'adjustment' => 'primary',
        'draft' => 'neutral',
        'rejected' => 'danger',
        'expired' => 'danger',
        'suspended' => 'danger',
    ];

    $labelKey = 'dashboard.status.' . $normalized;
    $label = \Illuminate\Support\Facades\Lang::has($labelKey)
        ? __($labelKey)
        : ucfirst($val);
    $variant = $variantMap[$normalized] ?? 'neutral';
@endphp

<x-badges.badge :variant="$variant" {{ $attributes->merge(['class' => 'text-[10px] font-bold']) }}>
    {{ $label }}
</x-badges.badge>
