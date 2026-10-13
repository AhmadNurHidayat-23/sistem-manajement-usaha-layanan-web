@extends('layouts.pelanggan', ['title' => 'Ulasan Saya'])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-[#101828] m-0">Ulasan Saya</h2>
            <p class="text-xs text-[#667085] mt-1 m-0">Daftar ulasan dan rating yang telah Anda berikan untuk layanan laundry</p>
        </div>
        <a href="{{ route('customer.pesanan.index', ['status' => 'completed']) }}"
            class="inline-flex items-center gap-2 px-4 py-2 bg-[#062072] hover:bg-[#04184F] text-white rounded-lg text-sm font-semibold transition-colors no-underline shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
            </svg>
            Pesanan Selesai
        </a>
    </div>

    <!-- Flash Messages -->
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

    <!-- Reviews List -->
    <div class="space-y-4">
        @forelse($ulasans as $ulasan)
            @php
                $branchName = $ulasan->transaction?->branch?->display_name
                    ?? $ulasan->transaction?->order?->branch?->display_name
                    ?? $ulasan->service?->branch?->display_name
                    ?? 'Cabang';
                $orderId = $ulasan->transaction?->order_id;
            @endphp
            <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-xs hover:shadow-md transition-shadow">
                <div class="p-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[11px] font-semibold bg-[#DCE8FF] text-[#062072]">
                                {{ $ulasan->service?->category?->name ?? 'Layanan' }}
                            </span>
                            <span class="text-sm font-bold text-[#101828]">
                                {{ $ulasan->service?->name ?? 'Layanan Laundry' }}
                            </span>
                            @if ($orderId)
                                <span class="text-xs text-[#667085]">
                                    (Order #{{ $orderId }})
                                </span>
                            @endif
                        </div>
                        <span class="text-xs text-[#667085] shrink-0">
                            {{ $ulasan->created_at->format('d M Y, H:i') }}
                        </span>
                    </div>

                    <!-- Rating Stars -->
                    <div class="flex items-center gap-2 mb-3">
                        <div class="flex items-center gap-0.5">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= $ulasan->rating ? 'text-amber-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </svg>
                            @endfor
                        </div>
                        <span class="text-xs font-bold text-[#101828]">{{ number_format($ulasan->rating, 1) }} / 5.0</span>
                        <span class="text-xs text-[#667085]">• {{ $branchName }}</span>
                    </div>

                    <!-- Comment -->
                    @if ($ulasan->komentar)
                        <div class="p-3.5 bg-[#F5F8FC] rounded-lg border border-[#E4E7EC] text-xs text-[#101828] leading-relaxed mb-4">
                            "{{ $ulasan->komentar }}"
                        </div>
                    @else
                        <p class="text-xs italic text-[#667085] mb-4">Tidak ada komentar tertulis.</p>
                    @endif

                    <!-- Bottom Bar: Order link & Edit action -->
                    <div class="pt-3 border-t border-[#E4E7EC] flex items-center justify-between">
                        <div>
                            @if ($orderId)
                                <a href="{{ route('customer.pesanan.show', $orderId) }}"
                                    class="text-xs font-medium text-[#062072] hover:text-[#04184F] no-underline">
                                    Lihat Detail Pesanan
                                </a>
                            @endif
                        </div>
                        <a href="{{ route('customer.ulasan.edit', $ulasan) }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-[#E4E7EC] rounded-lg text-xs font-semibold text-[#062072] hover:bg-[#DCE8FF]/30 transition-colors no-underline">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit Ulasan
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white border border-[#E4E7EC] rounded-xl p-10 text-center">
                <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-[#DCE8FF] flex items-center justify-center text-[#062072]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-[#101828] mb-1">Belum Ada Ulasan</h3>
                <p class="text-xs text-[#667085] mb-4 max-w-sm mx-auto">
                    Anda belum memberikan ulasan untuk pesanan laundry Anda. Berikan penilaian setelah pesanan Anda selesai.
                </p>
                <a href="{{ route('customer.pesanan.index', ['status' => 'completed']) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-[#062072] hover:bg-[#04184F] text-white rounded-lg text-sm font-semibold transition-colors no-underline">
                    Lihat Pesanan Selesai
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($ulasans->hasPages())
        <div class="mt-4">{{ $ulasans->links() }}</div>
    @endif
</div>
@endsection
