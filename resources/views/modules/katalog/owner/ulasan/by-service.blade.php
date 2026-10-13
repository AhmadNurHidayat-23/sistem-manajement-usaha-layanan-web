@extends('layouts.app', ['title' => 'Ulasan Layanan'])

@section('content')
<div class="max-w-4xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-xl font-bold text-[#101828] m-0">Ulasan Layanan</h3>
            <p class="text-xs text-[#667085] mt-1 m-0">Daftar ulasan untuk layanan ini beserta statistik rating.</p>
        </div>
        <x-buttons.btn variant="outline" href="{{ route('ulasan.index') }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Semua Ulasan
        </x-buttons.btn>
    </div>

    {{-- Rating Stats --}}
    <x-cards.card>
        <div class="flex items-center gap-8">
            <div class="text-center">
                <div class="text-5xl font-bold text-[#062072]">{{ number_format($stats['average'], 1) }}</div>
                <div class="mt-1">
                    @include('modules.katalog.owner.ulasan.partials.stars', ['rating' => round($stats['average'])])
                </div>
                <div class="text-xs text-[#667085] mt-1">dari {{ $stats['count'] }} ulasan</div>
            </div>

            <div class="flex-1 space-y-1.5">
                @for ($star = 5; $star >= 1; $star--)
                    @php
                        $count = $ulasans->getCollection()->where('rating', $star)->count();
                        $total = $stats['count'];
                        $pct = $total > 0 ? round(($count / $total) * 100) : 0;
                    @endphp
                    <div class="flex items-center gap-2 text-xs">
                        <span class="w-6 text-right text-[#667085]">{{ $star }}</span>
                        <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <div class="flex-1 h-2 bg-[#F5F8FC] rounded-full overflow-hidden">
                            <div class="h-full bg-amber-400 rounded-full" style="width: {{ $pct }}%"></div>
                        </div>
                        <span class="w-8 text-[#667085]">{{ $pct }}%</span>
                    </div>
                @endfor
            </div>
        </div>
    </x-cards.card>

    {{-- Review list --}}
    <x-cards.card title="Daftar Ulasan">
        @if ($ulasans->isEmpty())
            <div class="py-10 text-center text-[#667085]">
                <svg class="w-12 h-12 mx-auto mb-3 text-[#E4E7EC]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <p class="text-sm font-medium">Belum ada ulasan untuk layanan ini.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($ulasans as $ulasan)
                    <div class="p-4 border border-[#E4E7EC] rounded-lg hover:bg-[#F5F8FC] transition-colors">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-[#DCE8FF] text-[#062072] flex items-center justify-center font-bold text-sm">
                                    {{ strtoupper(substr($ulasan->user?->name ?? 'U', 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-[#101828] text-sm">{{ $ulasan->user?->name ?? 'Pelanggan' }}</p>
                                    <p class="text-xs text-[#667085]">{{ $ulasan->created_at->format('d M Y') }}</p>
                                </div>
                            </div>
                            <div>
                                @include('modules.katalog.owner.ulasan.partials.stars', ['rating' => $ulasan->rating, 'showValue' => true])
                            </div>
                        </div>
                        @if ($ulasan->komentar)
                            <p class="mt-3 text-sm text-[#344054] leading-relaxed">{{ $ulasan->komentar }}</p>
                        @endif
                        @if ($ulasan->employee)
                            <div class="mt-2 flex items-center gap-1.5 text-xs text-[#667085]">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Ditangani oleh <span class="font-medium text-[#344054]">{{ $ulasan->employee->name }}</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            @if ($ulasans->hasPages())
                <div class="pt-4 border-t border-[#E4E7EC]">
                    {{ $ulasans->links() }}
                </div>
            @endif
        @endif
    </x-cards.card>
</div>
@endsection
