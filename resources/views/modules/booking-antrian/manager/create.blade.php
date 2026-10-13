@extends('layouts.app', ['title' => 'Buat Order Drop-off'])

@section('content')
<div class="max-w-3xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-xl font-bold text-[#101828] m-0">Buat Order Drop-off</h3>
            <p class="text-xs text-[#667085] mt-1 m-0">Daftarkan pakaian atau cucian Anda untuk layanan drop-off laundry.</p>
        </div>
        <x-buttons.btn variant="outline" href="{{ route('orders.index') }}">Kembali</x-buttons.btn>
    </div>

    <x-cards.card title="Form Order Drop-off" subtitle="Isi detail cucian yang akan diproses">
        <form method="POST" action="{{ route('orders.store') }}" class="space-y-4" id="form-order">
            @csrf

            {{-- Cabang --}}
            <div class="mb-4">
                <label for="branch_id" class="block text-sm font-medium text-[#101828] mb-1">
                    Cabang <span class="text-rose-500">*</span>
                </label>
                <select id="branch_id" name="branch_id" required
                    class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    <option value="">— Pilih Cabang —</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->display_name }}</option>
                    @endforeach
                </select>
                @error('branch_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            {{-- Layanan --}}
            <div class="mb-4">
                <label for="service_id" class="block text-sm font-medium text-[#101828] mb-1">
                    Layanan <span class="text-rose-500">*</span>
                </label>
                <select id="service_id" name="service_id" required
                    class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    <option value="">— Pilih Layanan —</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
                            {{ $service->name }}
                            @if($service->estimated_duration) — ~{{ $service->estimated_duration }} menit @endif
                            — Rp{{ number_format($service->price, 0, ',', '.') }}
                        </option>
                    @endforeach
                </select>
                @error('service_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            {{-- Estimasi Kuantitas --}}
            <div class="mb-4">
                <label for="estimated_quantity" class="block text-sm font-medium text-[#101828] mb-1">
                    Estimasi Kuantitas
                    <span class="text-[#667085] font-normal text-xs ml-1">(opsional, misal: berat cucian dalam kg)</span>
                </label>
                <input type="number" id="estimated_quantity" name="estimated_quantity"
                    value="{{ old('estimated_quantity') }}" min="0.01" step="0.01"
                    placeholder="Contoh: 3.5"
                    class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] placeholder-[#667085] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                @error('estimated_quantity')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            {{-- Pickup option --}}
            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" id="pickup_option" name="pickup_option" value="1"
                    {{ old('pickup_option') ? 'checked' : '' }}
                    class="w-4 h-4 text-[#062072] border-[#E4E7EC] rounded focus:ring-[#062072]">
                <label for="pickup_option" class="text-sm font-medium text-[#101828]">
                    Minta layanan antar-jemput / pickup
                </label>
            </div>

            {{-- Catatan --}}
            <div class="mb-4">
                <label for="notes" class="block text-sm font-medium text-[#101828] mb-1">Catatan</label>
                <textarea id="notes" name="notes" rows="3"
                    placeholder="Kondisi khusus, instruksi tambahan…"
                    class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] placeholder-[#667085] focus:outline-none focus:ring-2 focus:ring-[#062072]">{{ old('notes') }}</textarea>
                @error('notes')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="pt-4 border-t border-[#E4E7EC] flex justify-end gap-3">
                <x-buttons.btn variant="outline" href="{{ route('orders.index') }}">Batal</x-buttons.btn>
                <x-buttons.btn variant="primary" type="submit" id="btn-simpan-order">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Buat Order
                </x-buttons.btn>
            </div>
        </form>
    </x-cards.card>
</div>
@endsection
