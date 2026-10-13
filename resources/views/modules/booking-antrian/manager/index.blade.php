@extends('layouts.app', ['title' => 'Daftar Order Drop-off'])

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-xl font-bold text-[#101828] m-0">Order Drop-off</h3>
            <p class="text-xs text-[#667085] mt-1 m-0">Kelola antrian drop-off layanan.</p>
        </div>
        @can('create', App\Models\Order::class)
        <x-buttons.btn variant="primary" href="{{ route('orders.create') }}" id="btn-buat-order">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Buat Order
        </x-buttons.btn>
        @endcan
    </div>

    {{-- Flash --}}
    @foreach(['success', 'error'] as $msg)
        @if(session($msg))
        <div class="{{ $msg === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-rose-50 border-rose-200 text-rose-700' }} border px-4 py-3 rounded-lg text-sm flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $msg === 'success' ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' }}"/>
            </svg>
            {{ session($msg) }}
        </div>
        @endif
    @endforeach

    {{-- Filter Toolbar Inline (Compact, Tanpa Card Besar) --}}
    <form method="GET" action="{{ route('orders.index') }}" class="flex flex-wrap items-center gap-2.5">
        @if(count($branches) > 0 && !in_array(auth()->user()?->role?->name, ['customer']))
            <div class="w-full sm:w-48 shrink-0">
                <select name="branch_id" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                    <option value="">Semua Cabang</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ ($filters['branch_id'] ?? '') == $branch->id ? 'selected' : '' }}>{{ $branch->display_name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="w-full sm:w-48 shrink-0">
            <select name="status" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                <option value="">Semua Status</option>
                @foreach(['pending' => 'Pending', 'received' => 'Diterima', 'processing' => 'Diproses', 'ready' => 'Siap', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'] as $val => $lbl)
                    <option value="{{ $val }}" {{ ($filters['status'] ?? '') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <button type="submit" class="h-10 px-4 bg-[#062072] hover:bg-[#04184F] text-white rounded-lg text-[13px] font-semibold transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                <x-icons.icon name="search" class="w-4 h-4 shrink-0" />
                <span>Filter</span>
            </button>
            <a href="{{ route('orders.index') }}" class="h-10 w-10 bg-white border border-[#D0D5DD] hover:bg-[#F5F8FC] text-[#667085] hover:text-[#101828] rounded-lg text-[13px] font-medium transition-colors flex items-center justify-center cursor-pointer shadow-2xs" title="Reset Filter">
                <x-icons.icon name="refresh" class="w-4 h-4 shrink-0" />
            </a>
        </div>
    </form>

    {{-- Table --}}
    <div class="space-y-4">
        <x-tables.table :headers="['#', 'Pelanggan', 'Cabang', 'Layanan', 'Est. Selesai', 'Pickup', 'Status', 'Aksi']">
            @forelse($orders as $order)
            @php
                $statusMap = [
                    'pending'    => ['label' => 'Pending',   'class' => 'bg-amber-100 text-amber-700'],
                    'received'   => ['label' => 'Diterima',  'class' => 'bg-slate-100 text-slate-700'],
                    'processing' => ['label' => 'Diproses',  'class' => 'bg-[#DCE8FF] text-[#062072]'],
                    'ready'      => ['label' => 'Siap',      'class' => 'bg-emerald-50 text-emerald-700'],
                    'completed'  => ['label' => 'Selesai',   'class' => 'bg-emerald-100 text-emerald-700'],
                    'cancelled'  => ['label' => 'Dibatalkan', 'class' => 'bg-rose-100 text-rose-700'],
                ];
                $s = $statusMap[$order->status->value] ?? ['label' => $order->status->value, 'class' => 'bg-slate-100 text-slate-700'];
            @endphp
            <tr class="hover:bg-[#F5F8FC]/50 transition-colors">
                <td class="px-3 py-2.5 text-[#667085] text-xs font-semibold whitespace-nowrap">#{{ $order->id }}</td>
                <td class="px-3 py-2.5 text-[#101828] truncate max-w-[140px]">{{ $order->user?->name ?? '-' }}</td>
                <td class="px-3 py-2.5 text-[#667085] truncate max-w-[120px]">{{ $order->branch?->display_name ?? '-' }}</td>
                <td class="px-3 py-2.5 text-[#101828] truncate max-w-[140px]">{{ $order->service?->name ?? '-' }}</td>
                <td class="px-3 py-2.5 text-[#667085] whitespace-nowrap">
                    {{ $order->estimated_finish_at ? $order->estimated_finish_at->format('d M H:i') : '-' }}
                </td>
                <td class="px-3 py-2.5 whitespace-nowrap">
                    @if($order->pickup_option)
                        <span class="inline-flex items-center gap-1 text-xs text-teal-700">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg> Ya
                        </span>
                    @else
                        <span class="text-xs text-[#667085]">Tidak</span>
                    @endif
                </td>
                <td class="px-3 py-2.5 whitespace-nowrap">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium {{ $s['class'] }}">{{ $s['label'] }}</span>
                </td>
                <td class="px-3 py-2.5 whitespace-nowrap">
                    <x-buttons.btn variant="outline" href="{{ route('orders.show', $order) }}" size="sm">Detail</x-buttons.btn>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-6 py-10 text-center text-[#667085]">
                    <svg class="w-10 h-10 mx-auto mb-2 text-[#E4E7EC]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    Belum ada order drop-off.
                </td>
            </tr>
            @endforelse
        </x-tables.table>
        @if($orders->hasPages())
            <div class="mt-4">{{ $orders->appends($filters)->links() }}</div>
        @endif
    </div>
</div>
@endsection
