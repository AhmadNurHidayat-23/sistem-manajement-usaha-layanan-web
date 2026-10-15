@extends('layouts.app', ['title' => 'Ulasan & Rating'])

@section('content')
<div class="space-y-5">
    {{-- Header Halaman --}}
    <div>
        <h3 class="text-xl font-bold text-[#101828] m-0">Ulasan & Rating</h3>
        <p class="text-xs text-[#667085] mt-1 m-0">Daftar seluruh ulasan pelanggan untuk semua layanan.</p>
    </div>

    @if (session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg text-xs flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Filter Inline (Compact, Tanpa Card Besar) --}}
    <form method="GET" action="{{ route('ulasan.index') }}" class="flex flex-wrap items-center gap-2.5">
        <label for="rating" class="text-[13px] font-medium text-[#101828] whitespace-nowrap">Rating Bintang</label>
        <div class="w-full sm:w-[260px] md:w-[280px]">
            <select id="rating" name="rating" onchange="this.form.submit()"
                class="w-full h-10 px-3 pr-8 bg-white border border-[#E4E7EC] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072] cursor-pointer">
                <option value="">Semua Rating</option>
                @for ($i = 5; $i >= 1; $i--)
                    <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>
                        {{ $i }} Bintang
                    </option>
                @endfor
            </select>
        </div>
        <div class="inline-flex items-center gap-1.5 shrink-0">
            <button type="submit"
                class="h-10 px-3.5 bg-[#062072] hover:bg-[#04184F] text-white rounded-lg text-[13px] font-semibold transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs whitespace-nowrap">
                <x-icons.icon name="search" class="w-3.5 h-3.5 shrink-0" />
                <span>Filter</span>
            </button>
            <a href="{{ route('ulasan.index') }}"
                class="h-10 w-10 bg-white border border-[#E4E7EC] hover:bg-[#F5F8FC] text-[#667085] hover:text-[#101828] rounded-lg transition-colors flex items-center justify-center cursor-pointer shadow-2xs shrink-0"
                title="Reset Filter">
                <x-icons.icon name="refresh" class="w-4 h-4 shrink-0" />
            </a>
        </div>
    </form>

    {{-- Table Card --}}
    <x-cards.card class="overflow-hidden">
        @if ($ulasans->isEmpty())
            <div class="py-12 text-center text-[#667085]">
                <svg class="w-12 h-12 mx-auto mb-3 text-[#E4E7EC]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <p class="text-sm font-medium">Belum ada ulasan.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-[#E4E7EC]">
                            <th class="pb-3 text-left text-xs font-semibold text-[#667085] uppercase tracking-wider">Pelanggan</th>
                            <th class="pb-3 text-left text-xs font-semibold text-[#667085] uppercase tracking-wider">Layanan</th>
                            <th class="pb-3 text-left text-xs font-semibold text-[#667085] uppercase tracking-wider">Rating</th>
                            <th class="pb-3 text-left text-xs font-semibold text-[#667085] uppercase tracking-wider">Komentar</th>
                            <th class="pb-3 text-left text-xs font-semibold text-[#667085] uppercase tracking-wider">Karyawan</th>
                            <th class="pb-3 text-left text-xs font-semibold text-[#667085] uppercase tracking-wider">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F5F8FC]">
                        @foreach ($ulasans as $ulasan)
                            <tr class="hover:bg-[#F5F8FC] transition-colors">
                                <td class="py-3 pr-4">
                                    <div class="font-medium text-[#101828]">{{ $ulasan->user?->name ?? '-' }}</div>
                                </td>
                                <td class="py-3 pr-4">
                                    <a href="{{ route('ulasan.by-service', $ulasan->service_id) }}"
                                        class="text-[#062072] hover:underline font-medium">
                                        {{ $ulasan->service?->name ?? '-' }}
                                    </a>
                                </td>
                                <td class="py-3 pr-4">
                                    @include('owner.ulasan.partials.stars', ['rating' => $ulasan->rating])
                                </td>
                                <td class="py-3 pr-4 max-w-xs">
                                    <p class="text-[#667085] truncate">{{ $ulasan->komentar ?? '—' }}</p>
                                </td>
                                <td class="py-3 pr-4">
                                    <span class="text-[#667085]">{{ $ulasan->employee?->name ?? '—' }}</span>
                                </td>
                                <td class="py-3 whitespace-nowrap text-[#667085] tabular-nums">
                                    {{ $ulasan->created_at->format('d M Y') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($ulasans->hasPages())
                <div class="mt-4 pt-4 border-t border-[#E4E7EC]">
                    {{ $ulasans->links() }}
                </div>
            @endif
        @endif
    </x-cards.card>
</div>
@endsection

