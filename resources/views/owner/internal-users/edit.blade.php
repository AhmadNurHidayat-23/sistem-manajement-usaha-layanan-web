@extends('layouts.app', ['title' => 'Edit Akun Internal'])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-[#101828] m-0">Edit Akun Internal</h2>
            <p class="text-xs text-[#667085] mt-1 m-0">Perbarui profil akun, peran, dan penugasan cabang operasional.</p>
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
        <form method="POST" action="{{ route('owner.internal-users.update', $user) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div>
                <label for="name" class="block text-xs font-semibold text-[#101828] mb-1">
                    Nama Lengkap <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
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
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
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
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
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
                @if($user->role?->name === 'owner')
                    <input type="hidden" name="role_id" value="{{ $user->role_id }}">
                    <div class="px-3 py-2 bg-slate-100 border border-[#E4E7EC] rounded-lg text-sm text-[#475467] flex items-center justify-between">
                        <span class="font-medium">Owner (Pemilik Sistem)</span>
                        <span class="text-[11px] text-[#667085] italic">Peran Owner tidak dapat diubah</span>
                    </div>
                @else
                    <select id="role_id" name="role_id" required
                        class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                {{ ucfirst($role->name) }}
                                @if($role->name === 'manager') (Pengawas Operasional Cabang)
                                @elseif($role->name === 'cashier') (Kasir & Layanan POS)
                                @endif
                            </option>
                        @endforeach
                    </select>
                @endif
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
                
                @php
                    $assignedBranchIds = old('branch_ids', $user->branches->pluck('id')->toArray());
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 p-3.5 bg-slate-50/80 rounded-lg border border-[#E4E7EC]">
                    @foreach ($branches as $branch)
                        <label class="flex items-start gap-2.5 p-2 bg-white rounded-md border border-[#E4E7EC] hover:border-[#062072] cursor-pointer transition-colors">
                            <input type="checkbox" name="branch_ids[]" value="{{ $branch->id }}"
                                {{ in_array($branch->id, $assignedBranchIds) ? 'checked' : '' }}
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
                <!-- Password (Optional) -->
                <div>
                    <label for="password" class="block text-xs font-semibold text-[#101828] mb-1">
                        Kata Sandi Baru <span class="text-[#667085] font-normal text-xs">(Opsional)</span>
                    </label>
                    <input type="password" id="password" name="password" minlength="8"
                        placeholder="Kosongkan jika tidak ingin mengubah"
                        class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    @error('password')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Confirmation -->
                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-[#101828] mb-1">
                        Konfirmasi Kata Sandi Baru
                    </label>
                    <input type="password" id="password_confirmation" name="password_confirmation" minlength="8"
                        placeholder="Ulangi kata sandi baru"
                        class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-[#E4E7EC]">
                <x-buttons.btn variant="outline" href="{{ route('owner.internal-users.index') }}">
                    Batal
                </x-buttons.btn>
                <x-buttons.btn variant="primary" type="submit">
                    Perbarui Akun
                </x-buttons.btn>
            </div>
        </form>
    </x-cards.card>
</div>
@endsection
