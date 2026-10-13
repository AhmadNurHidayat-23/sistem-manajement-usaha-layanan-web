@extends('layouts.app', ['title' => 'Tambah Akun Internal'])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-[#101828] m-0">Tambah Akun Internal Baru</h2>
            <p class="text-xs text-[#667085] mt-1 m-0">Buat akun untuk Kasir atau Manager dan tetapkan cabang penugasannya.</p>
        </div>
        <x-buttons.btn variant="outline" href="{{ route('owner.internal-users.index') }}" class="gap-1.5 text-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali
        </x-buttons.btn>
    </div>

    <!-- Form Card -->
    <x-cards.card class="p-6">
        <form method="POST" action="{{ route('owner.internal-users.store') }}" class="space-y-5">
            @csrf

            <!-- Name -->
            <div>
                <label for="name" class="block text-xs font-semibold text-[#101828] mb-1">
                    Nama Lengkap <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                    placeholder="Contoh: Ahmad Fauzi"
                    class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                @error('name')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-semibold text-[#101828] mb-1">
                        Alamat Email <span class="text-rose-500">*</span>
                    </label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                        placeholder="contoh@simaul.com"
                        class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    @error('email')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-xs font-semibold text-[#101828] mb-1">
                        Nomor WhatsApp / Telepon
                    </label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                        placeholder="Contoh: 08123456789"
                        class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    @error('phone')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Role Selection -->
            <div>
                <label for="role_id" class="block text-xs font-semibold text-[#101828] mb-1">
                    Peran Akun <span class="text-rose-500">*</span>
                </label>
                <select id="role_id" name="role_id" required
                    class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    <option value="">— Pilih Peran Akun —</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                            {{ ucfirst($role->name) }}
                            @if($role->name === 'manager') (Pengawas Operasional Cabang)
                            @elseif($role->name === 'cashier') (Kasir & Layanan POS)
                            @endif
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-[11px] text-[#667085]">Pilih Manager atau Kasir. Untuk staf lapangan/teknisi, gunakan menu Karyawan.</p>
                @error('role_id')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Branch Assignment (Multi-Checkbox) -->
            <div>
                <label class="block text-xs font-semibold text-[#101828] mb-1">
                    Penugasan Cabang <span class="text-rose-500">*</span>
                </label>
                <p class="text-[11px] text-[#667085] mb-2">Tentukan cabang tempat akun ini berwenang mengelola data dan operasional (dapat memilih lebih dari 1).</p>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 p-3.5 bg-slate-50/80 rounded-lg border border-[#E4E7EC]">
                    @foreach ($branches as $branch)
                        <label class="flex items-start gap-2.5 p-2 bg-white rounded-md border border-[#E4E7EC] hover:border-[#062072] cursor-pointer transition-colors">
                            <input type="checkbox" name="branch_ids[]" value="{{ $branch->id }}"
                                {{ in_array($branch->id, old('branch_ids', [])) ? 'checked' : '' }}
                                class="mt-0.5 rounded text-[#062072] focus:ring-[#062072]">
                            <div class="text-xs">
                                <span class="font-semibold text-[#101828] block">{{ $branch->display_name }}</span>
                                <span class="text-[11px] text-[#667085] block">{{ $branch->address }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('branch_ids')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-[#E4E7EC]">
                <!-- Password -->
                <div>
                    <label for="password" class="block text-xs font-semibold text-[#101828] mb-1">
                        Kata Sandi <span class="text-rose-500">*</span>
                    </label>
                    <input type="password" id="password" name="password" required minlength="8"
                        placeholder="Minimal 8 karakter"
                        class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    @error('password')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Confirmation -->
                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-[#101828] mb-1">
                        Konfirmasi Kata Sandi <span class="text-rose-500">*</span>
                    </label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8"
                        placeholder="Ulangi kata sandi"
                        class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-[#E4E7EC]">
                <x-buttons.btn variant="outline" href="{{ route('owner.internal-users.index') }}">
                    Batal
                </x-buttons.btn>
                <x-buttons.btn variant="primary" type="submit">
                    Simpan Akun
                </x-buttons.btn>
            </div>
        </form>
    </x-cards.card>
</div>
@endsection
