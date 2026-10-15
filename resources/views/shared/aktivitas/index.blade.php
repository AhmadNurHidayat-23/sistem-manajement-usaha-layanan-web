@extends('layouts.app', ['title' => 'Log Aktivitas'])

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-xl font-bold text-[#101828] m-0">Log Aktivitas</h3>
            <p class="text-xs text-[#667085] mt-1 m-0">Rekam jejak aktivitas penting yang tercatat di sistem.</p>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    <form method="GET" action="{{ route('aktivitas.index') }}" class="flex flex-wrap items-center gap-2.5" id="form-filter-aktivitas">
        <div class="flex-1 min-w-[200px]">
            <div class="relative">
                <input type="text" id="q" name="q" value="{{ request('q') }}"
                    placeholder="Cari kata kunci aktivitas..."
                    class="w-full h-10 pl-9 pr-3.5 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] placeholder-[#98A2B3] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                <x-icons.icon name="search" class="w-4 h-4 text-[#667085] absolute left-3 top-3 pointer-events-none" />
            </div>
        </div>

        <div class="w-full sm:w-48 shrink-0">
            <select id="modul" name="modul" onchange="this.form.submit()"
                class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                <option value="">Semua Modul</option>
                @foreach ($modulList as $modul)
                    <option value="{{ $modul }}" {{ request('modul') === $modul ? 'selected' : '' }}>
                        {{ ucfirst($modul) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <button type="submit"
                class="h-10 px-4 bg-[#062072] hover:bg-[#04184F] text-white text-[13px] font-semibold rounded-lg transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                <x-icons.icon name="search" class="w-4 h-4 shrink-0" />
                <span>Filter</span>
            </button>
            <a href="{{ route('aktivitas.index') }}"
                class="h-10 w-10 bg-white border border-[#D0D5DD] hover:bg-[#F5F8FC] text-[#667085] hover:text-[#101828] text-[13px] font-medium rounded-lg transition-colors flex items-center justify-center cursor-pointer shadow-2xs" title="Reset Filter">
                <x-icons.icon name="refresh" class="w-4 h-4 shrink-0" />
            </a>
        </div>
    </form>

    {{-- Log Table --}}
    <x-cards.card>
        @if ($logs->isEmpty())
            <div class="py-12 text-center text-[#667085]">
                <svg class="w-12 h-12 mx-auto mb-3 text-[#E4E7EC]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-sm font-medium">Belum ada log aktivitas.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="bg-[#F8F9FC] border-b border-[#E4E7EC]">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-[#667085] uppercase tracking-wider">Waktu</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-[#667085] uppercase tracking-wider">Pengguna</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-[#667085] uppercase tracking-wider">Modul</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-[#667085] uppercase tracking-wider">Aktivitas</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-[#667085] uppercase tracking-wider">Keterangan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-[#667085] uppercase tracking-wider">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E4E7EC]">
                        @foreach ($logs as $log)
                            <tr class="hover:bg-[#F5F8FC]/60 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-[13px] text-[#101828] font-medium">{{ $log->created_at->format('d M Y') }}</div>
                                    <div class="text-xs text-[#667085]">{{ $log->created_at->format('H:i:s') }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if ($log->user)
                                        <div class="font-medium text-[#101828] text-[13px]">{{ $log->user->name }}</div>
                                        <div class="text-xs text-[#667085] capitalize">{{ $log->user->role?->name ?? '—' }}</div>
                                    @else
                                        <span class="text-xs text-[#667085]">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md bg-[#DCE8FF] text-[#062072] text-xs font-semibold capitalize border border-[#0B3A9E]/20">
                                        {{ $log->modul }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="text-[#101828] font-medium text-[13px]">{{ $log->aktivitas }}</span>
                                </td>
                                <td class="px-4 py-3 max-w-xs">
                                    <p class="text-xs text-[#667085] truncate m-0" title="{{ $log->keterangan }}">
                                        {{ $log->keterangan ?? '—' }}
                                    </p>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="text-xs text-[#667085] font-medium">{{ $log->ip_address ?? '—' }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($logs->hasPages())
                <div class="pt-4 border-t border-[#E4E7EC]">
                    {{ $logs->withQueryString()->links() }}
                </div>
            @endif
        @endif
    </x-cards.card>
</div>
@endsection
