@extends('layouts.pelanggan', ['title' => 'Pesanan Saya'])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-[#101828] m-0">Pesanan Saya</h2>
            <p class="text-xs text-[#667085] mt-1 m-0">Pantau status semua pesanan laundry Anda</p>
        </div>
        <a href="{{ route('customer.pesanan.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2 bg-[#062072] hover:bg-[#04184F] text-white rounded-lg text-sm font-semibold transition-colors no-underline shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Buat Pesanan Baru
        </a>
    </div>

    <!-- Flash -->
    @foreach(['success', 'error', 'info'] as $msg)
        @if(session($msg))
        <div class="{{ $msg === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : ($msg === 'error' ? 'bg-rose-50 border-rose-200 text-rose-700' : 'bg-blue-50 border-blue-200 text-blue-700') }} border px-4 py-3 rounded-lg text-sm flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $msg === 'success' ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' }}"/>
            </svg>
            {{ session($msg) }}
        </div>
        @endif
    @endforeach

    <!-- Filter status -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1">
        @php
            $statusTabs = [
                '' => 'Semua',
                'active' => 'Aktif',
                'completed' => 'Selesai',
                'cancelled' => 'Dibatalkan',
            ];
            $currentStatus = $filters['status'] ?? '';
            // Label status aktif
        @endphp
        @foreach($statusTabs as $val => $label)
            <a href="{{ route('customer.pesanan.index', $val ? ['status' => $val] : []) }}"
                class="inline-flex items-center px-4 py-2 rounded-lg text-xs font-medium transition-colors no-underline whitespace-nowrap
                    {{ $currentStatus === $val ? 'bg-[#062072] text-white' : 'bg-white border border-[#E4E7EC] text-[#667085] hover:text-[#101828] hover:bg-[#F5F8FC]' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <!-- Orders List -->
    <div class="space-y-4">
        @forelse($orders as $order)
            @php
                $statusConfig = [
                    'pending'    => ['label' => 'Menunggu', 'bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'dot' => 'bg-amber-500'],
                    'received'   => ['label' => 'Diterima', 'bg' => 'bg-slate-50', 'text' => 'text-slate-700', 'border' => 'border-slate-200', 'dot' => 'bg-slate-400'],
                    'processing' => ['label' => 'Dikerjakan', 'bg' => 'bg-[#DCE8FF]', 'text' => 'text-[#062072]', 'border' => 'border-[#0B3A9E]/20', 'dot' => 'bg-[#0B3A9E]'],
                    'ready'      => ['label' => 'Siap Diambil', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'dot' => 'bg-emerald-500'],
                    'completed'  => ['label' => 'Selesai', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'dot' => 'bg-emerald-500'],
                    'cancelled'  => ['label' => 'Dibatalkan', 'bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200', 'dot' => 'bg-rose-400'],
                ];
                $sc = $statusConfig[$order->status->value] ?? ['label' => $order->status->value, 'bg' => 'bg-slate-50', 'text' => 'text-slate-700', 'border' => 'border-slate-200', 'dot' => 'bg-slate-400'];
            @endphp
            <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-xs hover:shadow-md transition-shadow">
                <div class="p-4 sm:p-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-bold text-[#101828]">Order #{{ $order->id }}</span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold {{ $sc['bg'] }} {{ $sc['text'] }} {{ $sc['border'] }} border">
                                <span class="w-1.5 h-1.5 rounded-full {{ $sc['dot'] }}"></span>
                                {{ $sc['label'] }}
                            </span>
                        </div>
                        <span class="text-xs text-[#667085]">{{ $order->created_at->format('d M Y, H:i') }}</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                        <div>
                            <span class="text-[10px] text-[#667085] uppercase tracking-wider block">Layanan</span>
                            <span class="font-medium text-[#101828]">{{ $order->service?->name ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-[#667085] uppercase tracking-wider block">Cabang</span>
                            <span class="font-medium text-[#101828]">{{ $order->branch?->display_name ?? $order->branch?->name ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-[#667085] uppercase tracking-wider block">Est. Selesai</span>
                            <span class="font-medium text-[#101828]">
                                {{ $order->estimated_finish_at ? $order->estimated_finish_at->format('d M Y, H:i') : '-' }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-[#E4E7EC] flex items-center justify-between">
                        <div class="text-sm">
                            @if ($order->estimated_quantity && $order->service)
                                <span class="text-[#667085]">Est. Harga:</span>
                                <span class="font-bold text-[#062072]">
                                    Rp {{ number_format($order->estimated_quantity * $order->service->price, 0, ',', '.') }}
                                </span>
                            @elseif ($order->service)
                                <span class="text-[#667085]">Harga:</span>
                                <span class="font-bold text-[#062072]">
                                    Rp {{ number_format($order->service->price, 0, ',', '.') }}
                                </span>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('customer.pesanan.select') }}" class="m-0 inline">
                            @csrf
                            <input type="hidden" name="order_id" value="{{ $order->id }}">
                            <button type="submit"
                                class="inline-flex items-center px-3 py-1.5 border border-[#E4E7EC] rounded-lg text-xs font-medium text-[#062072] hover:bg-[#DCE8FF]/30 transition-colors cursor-pointer bg-transparent">
                                Lihat Detail
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white border border-[#E4E7EC] rounded-xl p-10 text-center">
                <svg class="w-12 h-12 mx-auto mb-3 text-[#E4E7EC]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <h3 class="text-sm font-bold text-[#101828] mb-1">Belum Ada Pesanan</h3>
                <p class="text-xs text-[#667085] mb-4">Anda belum memiliki pesanan. Mulai dengan memilih layanan laundry.</p>
                <a href="{{ route('customer.pesanan.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-[#062072] hover:bg-[#04184F] text-white rounded-lg text-sm font-semibold transition-colors no-underline">
                    Buat Pesanan Pertama
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($orders->hasPages())
        <div class="mt-4">{{ $orders->appends($filters)->links() }}</div>
    @endif
</div>
@endsection
