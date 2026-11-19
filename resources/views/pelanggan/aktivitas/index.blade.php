@extends('layouts.pelanggan', ['title' => __('public.navbar.my_activity') . ' - SIMAUL'])

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
    {{-- Header & Tabs Filter --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[#101828] dark:text-white m-0">
                {{ __('public.customer.my_activity') }}
            </h1>
            <p class="text-xs sm:text-sm text-[#667085] dark:text-[#94A3B8] mt-1 m-0">
                {{ __('public.customer.my_activity_desc') }}
            </p>
        </div>

        {{-- Tabs Filter --}}
        <div class="inline-flex p-1 bg-[#F5F8FC] dark:bg-[#0E172B] border border-[#E4E7EC] dark:border-[#1E2D4A] rounded-xl text-xs font-semibold shrink-0">
            <button type="button" data-filter-activity="semua" id="tab-semua"
                class="activity-tab-btn px-3 py-1.5 rounded-lg bg-white dark:bg-[#1E2D4A] text-[#062072] dark:text-white shadow-xs cursor-pointer border-0">
                {{ __('public.customer.filter_all') }} ({{ $activities->count() }})
            </button>
            <button type="button" data-filter-activity="appointment" id="tab-appointment"
                class="activity-tab-btn px-3 py-1.5 rounded-lg text-[#667085] dark:text-[#94A3B8] hover:text-[#101828] dark:hover:text-white cursor-pointer border-0 bg-transparent">
                {{ __('public.customer.filter_appointment') }} ({{ $activities->where('type', 'appointment')->count() }})
            </button>
            <button type="button" data-filter-activity="pesanan" id="tab-pesanan"
                class="activity-tab-btn px-3 py-1.5 rounded-lg text-[#667085] dark:text-[#94A3B8] hover:text-[#101828] dark:hover:text-white cursor-pointer border-0 bg-transparent">
                {{ __('public.customer.filter_dropoff') }} ({{ $activities->where('type', 'pesanan')->count() }})
            </button>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @foreach(['success', 'error', 'info'] as $msg)
        @if(session($msg))
        <div class="{{ $msg === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300' : ($msg === 'error' ? 'bg-rose-50 border-rose-200 text-rose-700 dark:bg-rose-950/40 dark:border-rose-800 dark:text-rose-300' : 'bg-blue-50 border-blue-200 text-blue-700 dark:bg-blue-950/40 dark:border-blue-800 dark:text-blue-300') }} border px-4 py-3 rounded-xl text-sm flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session($msg) }}
        </div>
        @endif
    @endforeach

    {{-- Daftar Aktivitas atau Empty State --}}
    @if($activities->isEmpty())
    <div class="bg-white dark:bg-[#111C35] border border-[#E4E7EC] dark:border-[#1E2D4A] rounded-2xl p-8 sm:p-12 text-center shadow-xs">
        <svg class="w-12 h-12 text-[#667085]/50 dark:text-[#94A3B8]/50 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <p class="text-base font-semibold text-[#101828] dark:text-[#F8FAFC] m-0">
            {{ __('public.customer.empty_activity_title') }}
        </p>
        <p class="text-xs sm:text-sm text-[#667085] dark:text-[#94A3B8] mt-1.5 max-w-md mx-auto m-0">
            {{ __('public.customer.empty_activity_desc') }}
        </p>
        <div class="mt-6">
            <a href="{{ route('customer.dashboard') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#062072] hover:bg-[#04184F] text-white rounded-xl text-xs sm:text-sm font-semibold transition-colors no-underline shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Pilih Layanan di Beranda
            </a>
        </div>
    </div>
    @else
    <div class="space-y-3" id="activity-container">
        @foreach($activities as $act)
        <a href="{{ $act->url }}" data-type="{{ $act->type }}"
            class="activity-card block bg-white dark:bg-[#111C35] border border-[#E4E7EC] dark:border-[#1E2D4A] hover:border-[#062072]/30 dark:hover:border-blue-500/40 rounded-2xl p-4 sm:p-5 shadow-xs hover:shadow-sm transition-all no-underline">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
                <div class="flex items-start gap-3 sm:gap-4 min-w-0">
                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl flex items-center justify-center shrink-0 {{ $act->type === 'appointment' ? 'bg-blue-50 dark:bg-blue-900/40 text-[#062072] dark:text-blue-300' : 'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300' }}">
                        @if($act->type === 'appointment')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-sm sm:text-base font-bold text-[#101828] dark:text-white m-0 truncate">{{ $act->service_name }}</h3>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold {{ $act->type === 'appointment' ? 'bg-blue-50 dark:bg-blue-900/40 text-[#062072] dark:text-blue-300' : 'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300' }}">
                                {{ $act->type_label }}
                            </span>
                        </div>
                        <p class="text-xs sm:text-sm text-[#667085] dark:text-[#94A3B8] mt-1 m-0">
                            {{ $act->branch_name }} &bull; {{ $act->date }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-neutral-100 dark:bg-[#1E2D4A] text-[#344054] dark:text-[#E2E8F0] border border-[#E4E7EC] dark:border-[#2D4166]">
                        {{ $act->status_label }}
                    </span>
                    @if($act->payment_label)
                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ $act->payment_status === 'paid' ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800' }}">
                        {{ $act->payment_label }}
                    </span>
                    @endif
                    <svg class="w-4 h-4 text-[#667085] dark:text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </div>
        </a>
        @endforeach
    </div>
    @endif
</div>
@endsection
