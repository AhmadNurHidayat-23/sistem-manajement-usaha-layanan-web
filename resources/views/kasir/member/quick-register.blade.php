@extends('layouts.app', ['title' => 'Pendaftaran Member - Kasir'])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-xl font-bold text-[#101828] m-0 flex items-center gap-2">
                <svg class="w-6 h-6 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                </svg>
                Pendaftaran Member Cepat
            </h3>
            <p class="text-xs text-[#667085] mt-1 m-0">Daftarkan pelanggan baru ke program loyalitas langsung dari meja kasir.</p>
        </div>

        <x-buttons.btn variant="outline" href="{{ route('pos.index') }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke POS
        </x-buttons.btn>
    </div>

    <!-- Flash notifications -->
    @if (session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <x-cards.card title="Formulir Member Baru" subtitle="Input data dasar pelanggan untuk aktivasi kartu membership">
        <form method="POST" action="{{ route('members.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="reg_name" class="block text-xs font-semibold text-[#101828] mb-1">Nama Lengkap Pelanggan <span class="text-rose-500">*</span></label>
                <input type="text" id="reg_name" name="name" required placeholder="Contoh: Budi Santoso" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
            </div>

            <div>
                <label for="reg_phone" class="block text-xs font-semibold text-[#101828] mb-1">Nomor WhatsApp / HP Aktif <span class="text-rose-500">*</span></label>
                <input type="text" id="reg_phone" name="phone" required placeholder="081234567890" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                <span class="text-[11px] text-[#667085] mt-1 block">Nomor ini akan digunakan untuk mengirimkan struk digital dan info poin.</span>
            </div>

            <div>
                <label for="reg_email" class="block text-xs font-semibold text-[#101828] mb-1">Alamat Email (Opsional)</label>
                <input type="email" id="reg_email" name="email" placeholder="customer@example.com" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
            </div>

            <div class="pt-3 border-t border-[#E4E7EC] flex items-center justify-end gap-3">
                <a href="{{ route('pos.index') }}" class="px-4 py-2 bg-[#F5F8FC] hover:bg-[#E4E7EC] text-xs font-semibold text-[#667085] rounded-lg transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 bg-[#062072] hover:bg-[#0B3A9E] text-xs font-bold text-white rounded-lg shadow-sm transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Simpan & Aktifkan Member
                </button>
            </div>
        </form>
    </x-cards.card>
</div>
@endsection
