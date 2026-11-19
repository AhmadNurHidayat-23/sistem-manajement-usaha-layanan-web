@extends('layouts.pelanggan', ['title' => __('public.navbar.dashboard') . ' - SIMAUL'])

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
@endpush

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
    {{-- A. Sapaan --}}
    <div>
        <h1 class="text-2xl font-bold text-[#101828] dark:text-white m-0">
            {{ __('public.customer.greeting', ['name' => preg_replace('/\s*\(.*?\)\s*$/', '', auth()->user()->name)]) }}
        </h1>
        <p class="text-sm text-[#667085] dark:text-[#94A3B8] mt-1 m-0">{{ __('public.customer.greeting_sub') }}</p>
    </div>

    {{-- Flash Notifications --}}
    @foreach(['success', 'error', 'info'] as $msg)
        @if(session($msg))
        <div class="{{ $msg === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : ($msg === 'error' ? 'bg-rose-50 border-rose-200 text-rose-700' : 'bg-blue-50 border-blue-200 text-blue-700') }} border px-4 py-3 rounded-xl text-sm flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session($msg) }}
        </div>
        @endif
    @endforeach

    {{-- B. Pilih Layanan (3 Usaha, Tepat 1 Kali per Usaha) --}}
    <section id="pilih-layanan" class="space-y-4">
        <div>
            <h2 class="text-lg font-bold text-[#101828] m-0">{{ __('public.customer.choose_service') }}</h2>
            <p class="text-xs text-[#667085] mt-0.5 m-0">{{ __('public.customer.choose_service_desc') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @foreach($branches as $branch)
            @php
                $modeBadge = match($branch->service_mode) {
                    'appointment' => ['label' => __('public.catalog.appointment_only'), 'bg' => 'bg-blue-50 text-[#062072] border-blue-200'],
                    'drop_off' => ['label' => __('public.catalog.drop_off_only'), 'bg' => 'bg-indigo-50 text-indigo-700 border-indigo-200'],
                    default => ['label' => __('public.catalog.both'), 'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                };
            @endphp
            <div class="bg-white border border-[#E4E7EC] hover:border-[#062072]/40 rounded-2xl p-5 shadow-xs transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-semibold border {{ $modeBadge['bg'] }}">
                            {{ $modeBadge['label'] }}
                        </span>
                        <span class="text-xs text-[#667085] font-medium">{{ __('public.customer.services_count', ['count' => $branch->services->count()]) }}</span>
                    </div>

                    <h3 class="text-base font-bold text-[#101828] mb-1 m-0">{{ $branch->display_name }}</h3>
                    <p class="text-xs text-[#667085] mb-4 m-0 line-clamp-1">{{ $branch->address }}</p>

                    <div class="p-3 bg-[#F5F8FC] rounded-xl border border-[#E4E7EC]/70 space-y-1.5 text-xs mb-5">
                        <div class="flex items-center gap-2 text-[#344054]">
                            <svg class="w-4 h-4 text-[#062072] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>{{ $branch->operating_days_label }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-[#344054]">
                            <svg class="w-4 h-4 text-[#062072] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ $branch->operating_hours_label }}</span>
                        </div>
                    </div>
                </div>

                <button type="button" data-modal-open="branch-modal-{{ $branch->id }}"
                    class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-[#062072] hover:bg-[#04184F] text-white text-xs font-semibold transition-colors cursor-pointer border-0">
                    {{ __('public.customer.view_services') }}
                </button>
            </div>

            {{-- Modal Layanan untuk Cabang ini --}}
            <div id="branch-modal-{{ $branch->id }}" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl border border-[#E4E7EC] shadow-2xl max-w-lg w-full max-h-[85vh] flex flex-col overflow-hidden">
                    <div class="p-5 border-b border-[#E4E7EC] flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-[#101828] m-0">{{ $branch->display_name }}</h3>
                            <p class="text-xs text-[#667085] mt-0.5 m-0">{{ $branch->operating_days_label }} &bull; {{ $branch->operating_hours_label }}</p>
                        </div>
                        <button type="button" data-modal-close="branch-modal-{{ $branch->id }}"
                            class="text-[#667085] hover:text-[#101828] p-1 rounded-lg hover:bg-neutral-100 cursor-pointer border-0 bg-transparent">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="p-5 overflow-y-auto space-y-3">
                        @forelse($branch->services as $service)
                        @php
                            $effectiveType = match($branch->service_mode) {
                                'appointment' => 'appointment',
                                'drop_off' => 'drop_off',
                                default => $service->service_type ?? 'appointment',
                            };
                        @endphp
                        <div class="p-3.5 rounded-xl border border-[#E4E7EC] hover:border-[#062072]/30 bg-white transition-all space-y-2">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h4 class="text-sm font-semibold text-[#101828] m-0">{{ $service->name }}</h4>
                                    <p class="text-[11px] text-[#667085] mt-0.5 m-0">
                                        {{ $service->category?->name ?? 'Layanan' }} &bull; Estimasi {{ $service->formatted_duration }}
                                    </p>
                                </div>
                                <span class="text-xs font-bold text-[#062072] shrink-0">
                                    Rp {{ number_format($service->price, 0, ',', '.') }}
                                </span>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-1 border-t border-[#F2F4F7]">
                                @if($effectiveType === 'appointment')
                                    <a href="{{ route('customer.booking.create', ['branch_id' => $branch->id, 'service_id' => $service->id]) }}"
                                        class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-[#062072] hover:bg-[#04184F] focus:ring-2 focus:ring-[#062072]/20 text-white text-xs font-semibold no-underline transition-colors">
                                        {{ __('public.customer.create_appointment') }}
                                    </a>
                                @elseif($effectiveType === 'drop_off')
                                    <a href="{{ route('customer.pesanan.create', ['branch_id' => $branch->id, 'service_id' => $service->id]) }}"
                                        data-action="open-order-modal"
                                        data-modal-target="modal-buat-pesanan"
                                        data-branch-id="{{ $branch->id }}"
                                        data-service-id="{{ $service->id }}"
                                        class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-[#062072] hover:bg-[#04184F] focus:ring-2 focus:ring-[#062072]/20 text-white text-xs font-semibold no-underline transition-colors cursor-pointer">
                                        {{ __('public.customer.order_dropoff') }}
                                    </a>
                                @else
                                    <a href="{{ route('customer.booking.create', ['branch_id' => $branch->id, 'service_id' => $service->id]) }}"
                                        class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-[#062072] hover:bg-[#04184F] focus:ring-2 focus:ring-[#062072]/20 text-white text-xs font-semibold no-underline transition-colors">
                                        {{ __('public.customer.appointment') }}
                                    </a>
                                    <a href="{{ route('customer.pesanan.create', ['branch_id' => $branch->id, 'service_id' => $service->id]) }}"
                                        data-action="open-order-modal"
                                        data-modal-target="modal-buat-pesanan"
                                        data-branch-id="{{ $branch->id }}"
                                        data-service-id="{{ $service->id }}"
                                        class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-[#062072] hover:bg-[#04184F] focus:ring-2 focus:ring-[#062072]/20 text-white text-xs font-semibold no-underline transition-colors cursor-pointer">
                                        {{ __('public.customer.drop_off') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                        @empty
                        <p class="text-xs text-[#667085] text-center py-4 m-0">{{ __('public.customer.no_active_services') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    {{-- C & D. Membership Ringkas & Promo Valid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        {{-- Member & Poin --}}
        <div class="bg-white dark:bg-[#111C35] border border-[#E4E7EC] dark:border-[#1E2D4A] rounded-2xl p-5 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-semibold text-[#062072] dark:text-blue-400 uppercase tracking-wide">{{ __('public.customer.member_and_points') }}</span>
                <p class="text-xl font-bold text-[#101828] dark:text-white m-0">
                    {{ number_format($membership?->points ?? 0) }} <span class="text-xs font-normal text-[#667085] dark:text-[#94A3B8]">{{ __('public.customer.points') }}</span>
                </p>
                <p class="text-xs text-[#667085] dark:text-[#94A3B8] m-0">
                    {{ __('public.customer.tier') }}: <span class="font-semibold text-[#101828] dark:text-white">{{ ucfirst($membership?->tier ?? 'Reguler') }}</span>
                </p>
            </div>
            <a href="{{ route('customer.member') }}" class="px-3.5 py-2 rounded-xl bg-[#F5F8FC] dark:bg-[#1E2D4A] hover:bg-[#E4E7EC] dark:hover:bg-[#2D4166] text-[#062072] dark:text-blue-300 text-xs font-semibold no-underline transition-colors">
                {{ __('public.customer.member_detail') }}
            </a>
        </div>

        {{-- Promo Relevan --}}
        <div class="bg-white dark:bg-[#111C35] border border-[#E4E7EC] dark:border-[#1E2D4A] rounded-2xl p-5 shadow-xs flex items-center justify-between">
            @if($activePromos->isNotEmpty())
                @php $promo = $activePromos->first(); @endphp
                <div class="space-y-1">
                    <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wide">{{ __('public.customer.active_promo') }}</span>
                    <p class="text-base font-bold text-[#101828] dark:text-white m-0">{{ $promo->name ?? 'Diskon Spesial' }}</p>
                    <p class="text-xs text-[#667085] dark:text-[#94A3B8] m-0">{{ __('public.customer.use_code', ['code' => $promo->code]) }}</p>
                </div>
                <a href="{{ route('customer.promo') }}" class="px-3.5 py-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 text-xs font-semibold no-underline transition-colors">
                    {{ __('public.customer.view_promo') }}
                </a>
            @else
                <div class="space-y-1">
                    <span class="text-xs font-semibold text-[#667085] dark:text-[#94A3B8] uppercase tracking-wide">{{ __('public.customer.promo_and_discount') }}</span>
                    <p class="text-sm font-semibold text-[#101828] dark:text-white m-0">{{ __('public.customer.save_more_with_voucher') }}</p>
                    <p class="text-xs text-[#667085] dark:text-[#94A3B8] m-0">{{ __('public.customer.coupon_checkout_hint') }}</p>
                </div>
                <a href="{{ route('customer.promo') }}" class="px-3.5 py-2 rounded-xl bg-[#F5F8FC] dark:bg-[#1E2D4A] hover:bg-[#E4E7EC] dark:hover:bg-[#2D4166] text-[#062072] dark:text-blue-300 text-xs font-semibold no-underline transition-colors">
                    {{ __('public.customer.view_promo') }}
                </a>
            @endif
        </div>
    </div>
    {{-- Modal Buat Pesanan (Pop-up di tengah layar dengan background blur) --}}
    @include('pelanggan.pesanan.partials.modal-pesanan', [
        'isStandalone' => false,
    ])
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
@endpush

@endsection
