<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <h3 class="text-xl font-bold text-[#101828] m-0">Log Notifikasi</h3>
        <p class="text-xs text-[#667085] mt-1 m-0">Riwayat pengiriman pesan WhatsApp ke pelanggan.</p>
    </div>
    @if (config('services.whatsapp.driver') === 'log')
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-amber-50/70 border border-amber-200/70 text-amber-700 text-[11px] font-medium self-start sm:self-auto">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
            Mode: LOG (Sandbox)
        </span>
    @endif
</div>
