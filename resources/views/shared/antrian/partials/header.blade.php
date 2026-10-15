<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-1">
    <div>
        <h2 class="text-xl font-bold text-[#101828] m-0">{{ $isHistory ? 'Riwayat Tugas' : 'Antrean & Tugas' }}</h2>
        <p class="text-xs text-[#667085] mt-1 m-0">
            @if($isHistory)
                Daftar tugas layanan yang telah Anda selesaikan sebelumnya.
            @else
                Pekerjaan yang menjadi tanggung jawab Anda hari ini &bull;
                <span class="font-medium text-[#101828]">{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</span>
            @endif
        </p>
    </div>
    <div class="flex items-center gap-2">
        @if($isHistory)
            <a href="{{ route('queue.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-white border border-[#D0D5DD] text-[#062072] hover:bg-[#F5F8FC] transition-colors shadow-2xs">
                Antrean Hari Ini
            </a>
        @else
            <a href="{{ route('queue.index', ['view' => 'riwayat']) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-white border border-[#D0D5DD] text-[#475467] hover:text-[#062072] hover:bg-[#F5F8FC] transition-colors shadow-2xs">
                Riwayat Tugas
            </a>
        @endif
    </div>
</div>
