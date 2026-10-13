@extends('layouts.pelanggan', ['title' => 'Dashboard Pelanggan'])

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
    {{-- A. Sapaan --}}
    <div class="bg-white border border-[#E4E7EC] rounded-2xl p-6 sm:p-7 shadow-xs">
        <h1 class="text-2xl font-bold text-[#101828] m-0">
            Halo, {{ preg_replace('/\s*\(.*?\)\s*$/', '', auth()->user()->name) }}!
        </h1>
        <p class="text-sm text-[#667085] mt-1 m-0">Mau menggunakan layanan apa hari ini? Pilih usaha di bawah atau pantau aktivitas Anda.</p>
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
            <h2 class="text-lg font-bold text-[#101828] m-0">Pilih Layanan</h2>
            <p class="text-xs text-[#667085] mt-0.5 m-0">Temukan layanan terbaik dari 3 unit usaha SIMAUL dengan jadwal operasional aktual.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @foreach($branches as $branch)
            @php
                $modeBadge = match($branch->service_mode) {
                    'appointment' => ['label' => 'Appointment Only', 'bg' => 'bg-blue-50 text-[#062072] border-blue-200'],
                    'drop_off' => ['label' => 'Drop-off Only', 'bg' => 'bg-indigo-50 text-indigo-700 border-indigo-200'],
                    default => ['label' => 'Appointment & Drop-off', 'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                };
            @endphp
            <div class="bg-white border border-[#E4E7EC] hover:border-[#062072]/40 rounded-2xl p-5 shadow-xs transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-semibold border {{ $modeBadge['bg'] }}">
                            {{ $modeBadge['label'] }}
                        </span>
                        <span class="text-xs text-[#667085] font-medium">{{ $branch->services->count() }} layanan</span>
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
                    Lihat Layanan
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
                                        Buat Appointment
                                    </a>
                                @elseif($effectiveType === 'drop_off')
                                    <a href="{{ route('customer.pesanan.create', ['branch_id' => $branch->id, 'service_id' => $service->id]) }}"
                                        class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-[#062072] hover:bg-[#04184F] focus:ring-2 focus:ring-[#062072]/20 text-white text-xs font-semibold no-underline transition-colors">
                                        Pesan Drop-off
                                    </a>
                                @else
                                    <a href="{{ route('customer.booking.create', ['branch_id' => $branch->id, 'service_id' => $service->id]) }}"
                                        class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-[#062072] hover:bg-[#04184F] focus:ring-2 focus:ring-[#062072]/20 text-white text-xs font-semibold no-underline transition-colors">
                                        Appointment
                                    </a>
                                    <a href="{{ route('customer.pesanan.create', ['branch_id' => $branch->id, 'service_id' => $service->id]) }}"
                                        class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-[#062072] hover:bg-[#04184F] focus:ring-2 focus:ring-[#062072]/20 text-white text-xs font-semibold no-underline transition-colors">
                                        Drop-off
                                    </a>
                                @endif
                            </div>
                        </div>
                        @empty
                        <p class="text-xs text-[#667085] text-center py-4 m-0">Belum ada layanan aktif pada cabang ini.</p>
                        @endforelse
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    {{-- C. Aktivitas Saya (Unified Bookings & Orders) --}}
    <section id="aktivitas-saya" class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-[#101828] m-0">Aktivitas Saya</h2>
                <p class="text-xs text-[#667085] mt-0.5 m-0">Pantau seluruh janji temu dan pesanan drop-off Anda di satu tempat.</p>
            </div>
            {{-- Tabs Filter --}}
            <div class="inline-flex p-1 bg-[#F5F8FC] border border-[#E4E7EC] rounded-xl text-xs font-semibold">
                <button type="button" data-filter-activity="semua" id="tab-semua"
                    class="activity-tab-btn px-3 py-1.5 rounded-lg bg-white text-[#062072] shadow-xs cursor-pointer border-0">
                    Semua ({{ $activities->count() }})
                </button>
                <button type="button" data-filter-activity="appointment" id="tab-appointment"
                    class="activity-tab-btn px-3 py-1.5 rounded-lg text-[#667085] hover:text-[#101828] cursor-pointer border-0 bg-transparent">
                    Appointment ({{ $activities->where('type', 'appointment')->count() }})
                </button>
                <button type="button" data-filter-activity="pesanan" id="tab-pesanan"
                    class="activity-tab-btn px-3 py-1.5 rounded-lg text-[#667085] hover:text-[#101828] cursor-pointer border-0 bg-transparent">
                    Pesanan ({{ $activities->where('type', 'pesanan')->count() }})
                </button>
            </div>
        </div>

        @if($activities->isEmpty())
        <div class="bg-white border border-[#E4E7EC] rounded-2xl p-8 text-center shadow-xs">
            <svg class="w-10 h-10 text-[#667085]/50 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p class="text-sm font-semibold text-[#101828] m-0">Belum ada aktivitas transaksi</p>
            <p class="text-xs text-[#667085] mt-1 m-0">Pilih salah satu layanan di atas untuk membuat janji temu atau pesanan baru.</p>
        </div>
        @else
        <div class="space-y-3" id="activity-container">
            @foreach($activities as $act)
            <a href="{{ $act->url }}" data-type="{{ $act->type }}"
                class="activity-card block bg-white border border-[#E4E7EC] hover:border-[#062072]/30 rounded-2xl p-4 shadow-xs hover:shadow-sm transition-all no-underline">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $act->type === 'appointment' ? 'bg-blue-50 text-[#062072]' : 'bg-indigo-50 text-indigo-700' }}">
                            @if($act->type === 'appointment')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                            @endif
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold text-[#101828] m-0">{{ $act->service_name }}</h3>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold {{ $act->type === 'appointment' ? 'bg-blue-50 text-[#062072]' : 'bg-indigo-50 text-indigo-700' }}">
                                    {{ $act->type_label }}
                                </span>
                            </div>
                            <p class="text-xs text-[#667085] mt-0.5 m-0">
                                {{ $act->branch_name }} &bull; {{ $act->date }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 self-end sm:self-center">
                        <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-neutral-100 text-[#344054] border border-[#E4E7EC]">
                            {{ $act->status_label }}
                        </span>
                        @if($act->payment_label)
                        <span class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ $act->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                            {{ $act->payment_label }}
                        </span>
                        @endif
                        <svg class="w-4 h-4 text-[#667085]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        @endif
    </section>

    {{-- D & E. Membership Ringkas & Promo Valid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        {{-- Member & Poin --}}
        <div class="bg-white border border-[#E4E7EC] rounded-2xl p-5 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-semibold text-[#062072] uppercase tracking-wide">Member & Poin</span>
                <p class="text-xl font-bold text-[#101828] m-0">
                    {{ number_format($membership?->points ?? 0) }} <span class="text-xs font-normal text-[#667085]">Poin</span>
                </p>
                <p class="text-xs text-[#667085] m-0">
                    Tier: <span class="font-semibold text-[#101828]">{{ ucfirst($membership?->tier ?? 'Reguler') }}</span>
                </p>
            </div>
            <a href="{{ route('customer.member') }}" class="px-3.5 py-2 rounded-xl bg-[#F5F8FC] hover:bg-[#E4E7EC] text-[#062072] text-xs font-semibold no-underline transition-colors">
                Detail Member
            </a>
        </div>

        {{-- Promo Relevan --}}
        <div class="bg-white border border-[#E4E7EC] rounded-2xl p-5 shadow-xs flex items-center justify-between">
            @if($activePromos->isNotEmpty())
                @php $promo = $activePromos->first(); @endphp
                <div class="space-y-1">
                    <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Promo Aktif</span>
                    <p class="text-base font-bold text-[#101828] m-0">{{ $promo->name ?? 'Diskon Spesial' }}</p>
                    <p class="text-xs text-[#667085] m-0">Gunakan kode <span class="font-mono font-bold text-[#062072]">{{ $promo->code }}</span> saat checkout</p>
                </div>
                <a href="{{ route('customer.promo') }}" class="px-3.5 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold no-underline transition-colors">
                    Lihat Promo
                </a>
            @else
                <div class="space-y-1">
                    <span class="text-xs font-semibold text-[#667085] uppercase tracking-wide">Promo & Diskon</span>
                    <p class="text-sm font-semibold text-[#101828] m-0">Hemat lebih banyak dengan voucher</p>
                    <p class="text-xs text-[#667085] m-0">Kupon dapat diaplikasikan langsung saat checkout</p>
                </div>
                <a href="{{ route('customer.promo') }}" class="px-3.5 py-2 rounded-xl bg-[#F5F8FC] hover:bg-[#E4E7EC] text-[#062072] text-xs font-semibold no-underline transition-colors">
                    Lihat Promo
                </a>
            @endif
        </div>
    </div>
</div>

@endsection
