@extends('layouts.app', ['title' => 'Tambah Cabang Baru'])

@section('content')
<div class="max-w-3xl space-y-6">
    <div class="flex items-center justify-between">
        <h3 class="text-xl font-bold text-[#101828] m-0">Tambah Cabang Baru</h3>
        <x-buttons.btn variant="outline" href="{{ route('branches.index') }}">
            Kembali
        </x-buttons.btn>
    </div>

    <x-cards.card title="Form Informasi Cabang" subtitle="Isi detail informasi cabang dengan lengkap">
        <form method="POST" action="{{ route('branches.store') }}" class="space-y-4">
            @csrf

            <x-forms.input name="name" label="Nama Cabang" placeholder="Contoh: Cabang Utama Central" required />

            <div class="mb-4">
                <label for="address" class="block text-sm font-medium text-[#101828] mb-1">
                    Alamat Lengkap <span class="text-rose-500">*</span>
                </label>
                <textarea id="address" name="address" rows="3" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] placeholder-[#667085] focus:outline-none focus:ring-2 focus:ring-[#062072] focus:border-[#062072]">{{ old('address') }}</textarea>
                @error('address')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <x-forms.input name="phone" label="Nomor Telepon" placeholder="08123456789" />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-forms.input type="time" name="opening_time" label="Jam Buka" />
                <x-forms.input type="time" name="closing_time" label="Jam Tutup" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label for="service_mode" class="block text-sm font-medium text-[#101828] mb-1">
                        Mode Layanan <span class="text-rose-500">*</span>
                    </label>
                    <select id="service_mode" name="service_mode" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                        <option value="both" {{ old('service_mode') === 'both' ? 'selected' : '' }}>Keduanya (Appointment & Drop-off)</option>
                        <option value="appointment" {{ old('service_mode') === 'appointment' ? 'selected' : '' }}>Appointment Only</option>
                        <option value="drop_off" {{ old('service_mode') === 'drop_off' ? 'selected' : '' }}>Drop-off Only</option>
                    </select>
                    @error('service_mode')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <x-forms.input type="number" name="max_slot_capacity" label="Kapasitas Maksimal / Slot" value="5" required min="1" />
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-[#101828] mb-2">
                    Hari Operasional
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    @foreach(\App\Models\Branch::DAYS_OF_WEEK as $label => $val)
                        <label class="inline-flex items-center gap-2 p-2 border border-[#E4E7EC] rounded-lg text-xs cursor-pointer hover:bg-[#F5F8FC]">
                            <input type="checkbox" name="operating_days[]" value="{{ $val }}"
                                {{ in_array($val, old('operating_days', ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'])) ? 'checked' : '' }}
                                class="w-3.5 h-3.5 text-[#062072] rounded">
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="text-[11px] text-[#667085] mt-1">Kosongkan bila buka setiap hari (Senin - Minggu).</p>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="w-4 h-4 text-[#062072] border-[#E4E7EC] rounded focus:ring-[#062072]">
                <label for="is_active" class="text-sm font-medium text-[#101828]">Status Cabang Aktif</label>
            </div>

            <div class="pt-4 border-t border-[#E4E7EC] flex justify-end gap-3">
                <x-buttons.btn variant="outline" href="{{ route('branches.index') }}">
                    Batal
                </x-buttons.btn>
                <x-buttons.btn variant="primary" type="submit">
                    Simpan Cabang
                </x-buttons.btn>
            </div>
        </form>
    </x-cards.card>
</div>
@endsection
