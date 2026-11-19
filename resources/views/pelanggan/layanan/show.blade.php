@extends('layouts.pelanggan', ['title' => $service->name . ' - Detail Layanan'])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-[#667085] dark:text-[#94A3B8]">
        <a href="{{ route('customer.home') }}" class="hover:text-[#062072] dark:hover:text-white no-underline text-[#667085] dark:text-[#94A3B8]">Beranda</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-[#101828] dark:text-[#F8FAFC] font-medium">{{ $service->name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Service Details -->
        <div class="lg:col-span-2 space-y-5">
            <!-- Service Image + Info Card -->
            <div class="bg-white dark:bg-[#111C35] rounded-xl border border-[#E4E7EC] dark:border-[#1E2D4A] shadow-xs overflow-hidden">
                @php
                    $gallery = $service->images->pluck('image_url')->filter()->values();
                    if ($gallery->isEmpty() && $photoUrl) {
                        $gallery = collect([$photoUrl]);
                    }
                @endphp

                @if ($gallery->isNotEmpty())
                    <div class="relative w-full h-64 sm:h-80 bg-neutral-100 dark:bg-[#0E172B] overflow-hidden">
                        <img id="detail-main-img" src="{{ $gallery->first() }}" alt="{{ $service->name }}" class="w-full h-full object-cover">
                        @if ($gallery->count() > 1)
                            <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex items-center gap-1.5 bg-black/50 backdrop-blur-xs px-2.5 py-1.5 rounded-full z-10">
                                @foreach ($gallery as $idx => $img)
                                    <button type="button"
                                            onclick="document.getElementById('detail-main-img').src = '{{ $img }}'"
                                            class="w-2.5 h-2.5 rounded-full bg-white/60 hover:bg-white transition-all cursor-pointer border-0 p-0"
                                            aria-label="Foto {{ $idx + 1 }}"></button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                <div class="p-5 sm:p-6">
                    <!-- Category + Status -->
                    <div class="flex items-center gap-2 mb-3">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-[#DCE8FF] dark:bg-[#1E2D4A] text-[#062072] dark:text-[#93C5FD]">
                            {{ $service->category?->name ?? 'Umum' }}
                        </span>
                        @if ($service->is_active)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Tersedia
                            </span>
                        @endif
                        @if ($service->service_type === 'package')
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                                Paket Bundling
                            </span>
                        @endif
                    </div>

                    <h1 class="text-xl font-bold text-[#101828] dark:text-[#F8FAFC] mb-2 m-0">{{ $service->name }}</h1>
                    <p class="text-sm text-[#667085] dark:text-[#94A3B8] leading-relaxed mb-0">
                        {{ $service->description ?? 'Layanan terpercaya dengan kualitas terjamin.' }}
                    </p>
                </div>
            </div>

            <!-- Detail Informasi -->
            <div class="bg-white dark:bg-[#111C35] rounded-xl border border-[#E4E7EC] dark:border-[#1E2D4A] shadow-xs p-5 sm:p-6">
                <h3 class="text-sm font-bold text-[#101828] dark:text-[#F8FAFC] mb-4">Informasi Layanan</h3>
                <dl class="divide-y divide-[#E4E7EC] dark:divide-[#1E2D4A]">
                    <div class="py-3 flex justify-between gap-4">
                        <dt class="text-sm text-[#667085] dark:text-[#94A3B8]">Harga</dt>
                        <dd class="text-sm font-bold text-[#062072] dark:text-[#93C5FD]">
                            Rp {{ number_format($service->price, 0, ',', '.') }}{{ $service->unit ? ' / ' . $service->unit : '' }}
                        </dd>
                    </div>
                    <div class="py-3 flex justify-between gap-4">
                        <dt class="text-sm text-[#667085] dark:text-[#94A3B8]">Satuan</dt>
                        <dd class="text-sm font-medium text-[#101828] dark:text-[#F8FAFC]">
                            {{ $service->unit ? 'per ' . $service->unit : ($service->service_type === 'appointment' ? 'per sesi' : 'per layanan') }}
                        </dd>
                    </div>
                    <div class="py-3 flex justify-between gap-4">
                        <dt class="text-sm text-[#667085] dark:text-[#94A3B8]">Estimasi Pengerjaan</dt>
                        <dd class="text-sm font-medium text-[#101828] dark:text-[#F8FAFC]">
                            @if ($service->estimated_duration)
                                {{ $service->estimated_duration }} Menit
                                @if ($service->estimated_duration >= 60)
                                    ({{ number_format($service->estimated_duration / 60, 1) }} Jam)
                                @endif
                            @else
                                -
                            @endif
                        </dd>
                    </div>
                    <div class="py-3 flex justify-between gap-4">
                        <dt class="text-sm text-[#667085] dark:text-[#94A3B8]">Kategori</dt>
                        <dd class="text-sm font-medium text-[#101828] dark:text-[#F8FAFC]">{{ $service->category?->name ?? 'Umum' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Reviews Summary -->
            <div class="bg-white dark:bg-[#111C35] rounded-xl border border-[#E4E7EC] dark:border-[#1E2D4A] shadow-xs p-5 sm:p-6">
                <h3 class="text-sm font-bold text-[#101828] dark:text-[#F8FAFC] mb-3">Ulasan Pelanggan</h3>
                <div class="flex items-center gap-4">
                    <div class="text-center">
                        <span class="text-3xl font-bold text-[#062072] dark:text-[#93C5FD]">{{ $reviewStats['average'] }}</span>
                        <span class="text-sm text-[#667085] dark:text-[#94A3B8]">/5</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-0.5 mb-1">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= round($reviewStats['average']) ? 'text-amber-400' : 'text-gray-200 dark:text-gray-700' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>
                        <p class="text-xs text-[#667085] dark:text-[#94A3B8] m-0">{{ $reviewStats['count'] }} ulasan</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Action Sidebar -->
        <div class="space-y-5">
            <!-- Price Card -->
            <div class="bg-white dark:bg-[#111C35] rounded-xl border border-[#E4E7EC] dark:border-[#1E2D4A] shadow-xs p-5">
                <div class="text-center mb-4">
                    <span class="text-[10px] text-[#667085] dark:text-[#94A3B8] uppercase tracking-wider block mb-1">Harga Mulai</span>
                    <span class="text-2xl font-bold text-[#062072] dark:text-[#93C5FD]">Rp {{ number_format($service->price, 0, ',', '.') }}</span>
                </div>

                <a href="{{ route('customer.pesanan.create', ['service_id' => $service->id, 'branch_id' => $service->branch_id]) }}"
                    class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-[#062072] hover:bg-[#04184F] text-white rounded-lg text-sm font-semibold transition-colors no-underline shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                    </svg>
                    Pesan Sekarang
                </a>
            </div>

            <!-- Branches Available -->
            <div class="bg-white dark:bg-[#111C35] rounded-xl border border-[#E4E7EC] dark:border-[#1E2D4A] shadow-xs p-5">
                <h3 class="text-sm font-bold text-[#101828] dark:text-[#F8FAFC] mb-3">Cabang Tersedia</h3>
                <div class="space-y-2">
                    @forelse ($branches as $branch)
                        <div class="flex items-center gap-2 py-2 {{ !$loop->last ? 'border-b border-[#E4E7EC] dark:border-[#1E2D4A]' : '' }}">
                            <div class="w-7 h-7 rounded-lg bg-[#DCE8FF] dark:bg-[#1E2D4A] flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5 text-[#062072] dark:text-[#93C5FD]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-[#101828] dark:text-[#F8FAFC] m-0 truncate" title="{{ $branch->name }}">{{ $branch->display_name }}</p>
                                @if ($branch->address)
                                    <p class="text-[10px] text-[#667085] dark:text-[#94A3B8] m-0 truncate">{{ $branch->address }}</p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-[#667085] dark:text-[#94A3B8] m-0">Tidak ada cabang tersedia.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
