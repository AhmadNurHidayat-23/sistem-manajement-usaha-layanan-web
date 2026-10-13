@props([
    'status',
])

@php
    $val = $status instanceof \BackedEnum ? $status->value : (string) $status;
    $val = strtolower($val);

    $map = [
        // Status booking
        'completed' => ['label' => 'Selesai', 'variant' => 'success'],
        'confirmed' => ['label' => 'Dikerjakan', 'variant' => 'primary'],
        'in_progress' => ['label' => 'Dikerjakan', 'variant' => 'primary'],
        'pending' => ['label' => 'Menunggu', 'variant' => 'warning'],
        'cancelled' => ['label' => 'Batal', 'variant' => 'danger'],

        // Shift attendance status
        'present' => ['label' => 'Hadir', 'variant' => 'success'],
        'late' => ['label' => 'Terlambat', 'variant' => 'warning'],
        'absent' => ['label' => 'Absen', 'variant' => 'danger'],
        'scheduled' => ['label' => 'Terjadwal', 'variant' => 'neutral'],
        'off' => ['label' => 'Libur', 'variant' => 'neutral'],

        // Status transaksi
        'paid' => ['label' => 'Lunas', 'variant' => 'success'],
        'completed_trx' => ['label' => 'Lunas', 'variant' => 'success'],
        'failed' => ['label' => 'Gagal', 'variant' => 'danger'],
        'refunded' => ['label' => 'Refund', 'variant' => 'danger'],

        // Active / Inactive status
        '1' => ['label' => 'Aktif', 'variant' => 'success'],
        '0' => ['label' => 'Nonaktif', 'variant' => 'neutral'],
        'active' => ['label' => 'Aktif', 'variant' => 'success'],
        'inactive' => ['label' => 'Nonaktif', 'variant' => 'neutral'],

        // Inventory Stock & Mutation status
        'tersedia' => ['label' => 'Tersedia', 'variant' => 'success'],
        'menipis' => ['label' => 'Menipis', 'variant' => 'warning'],
        'habis' => ['label' => 'Habis', 'variant' => 'danger'],
        'available' => ['label' => 'Tersedia', 'variant' => 'success'],
        'low' => ['label' => 'Menipis', 'variant' => 'warning'],
        'out_of_stock' => ['label' => 'Habis', 'variant' => 'danger'],
        'in' => ['label' => 'Masuk', 'variant' => 'success'],
        'out' => ['label' => 'Keluar', 'variant' => 'danger'],
        'adjustment' => ['label' => 'Penyesuaian', 'variant' => 'primary'],
    ];

    $config = $map[$val] ?? ['label' => ucfirst($val), 'variant' => 'neutral'];
@endphp

<x-badges.badge :variant="$config['variant']" {{ $attributes->merge(['class' => 'text-[10px] font-bold']) }}>
    {{ $config['label'] }}
</x-badges.badge>
