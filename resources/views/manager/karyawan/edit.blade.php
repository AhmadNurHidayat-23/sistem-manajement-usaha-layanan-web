@extends('layouts.app', ['title' => 'Edit Karyawan — ' . $employee->name])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-[#101828] m-0">Edit Data Karyawan</h2>
            <p class="text-xs text-[#667085] mt-1 m-0">Perbarui informasi profil {{ $employee->name }}, cabang penempatan, dan spesialisasi keahlian.</p>
        </div>
        <x-buttons.btn variant="outline" href="{{ route('employees.show', $employee) }}" class="gap-1.5 text-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Detail
        </x-buttons.btn>
    </div>

    <!-- Form Card -->
    <x-cards.card class="p-6">
        <form method="POST" action="{{ route('employees.update', $employee) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div>
                <label for="name" class="block text-xs font-semibold text-[#101828] mb-1">
                    Nama Lengkap Karyawan <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', $employee->name) }}" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                @error('name')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Phone -->
            <div>
                <label for="phone" class="block text-xs font-semibold text-[#101828] mb-1">
                    Nomor WhatsApp / Telepon <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="phone" name="phone" value="{{ old('phone', $employee->phone) }}" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                @error('phone')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Branch -->
                <div>
                    <label for="branch_id" class="block text-xs font-semibold text-[#101828] mb-1">
                        Cabang Penempatan <span class="text-rose-500">*</span>
                    </label>
                    <select id="branch_id" name="branch_id" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id', $employee->branch_id) == $branch->id ? 'selected' : '' }}>
                                {{ $branch->display_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('branch_id')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-xs font-semibold text-[#101828] mb-1">
                        Status Karyawan <span class="text-rose-500">*</span>
                    </label>
                    <select id="status" name="status" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                        <option value="active" {{ old('status', $employee->status) === 'active' ? 'selected' : '' }}>Aktif (Siap Tugas & Shift)</option>
                        <option value="inactive" {{ old('status', $employee->status) === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Linked User Account -->
            <div>
                <label for="user_id" class="block text-xs font-semibold text-[#101828] mb-1">
                    Hubungkan Akun Login Sistem (Opsional)
                </label>
                <select id="user_id" name="user_id" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    <option value="">-- Tanpa Akun Login (Hanya Data Profil) --</option>
                    @foreach ($availableUsers as $u)
                        <option value="{{ $u->id }}" {{ old('user_id', $employee->user_id) == $u->id ? 'selected' : '' }}>
                            {{ $u->name }} ({{ $u->email }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-[#667085] mt-1 m-0">Menghubungkan akun login memungkinkan staf masuk ke dashboard karyawan.</p>
                @error('user_id')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Skills Input -->
            @php
                $existingSkills = $employee->skills->pluck('skill_name')->implode(', ');
            @endphp
            <div>
                <label for="skills" class="block text-xs font-semibold text-[#101828] mb-1">
                    Keahlian & Spesialisasi (Pisahkan dengan koma)
                </label>
                <input type="text" id="skills" name="skills" value="{{ old('skills', $existingSkills) }}" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">

                <!-- Quick Skill Chips Suggestions -->
                <div class="flex flex-wrap gap-1.5 mt-2">
                    @php
                        $suggestedSkills = [
                            'Cuci Kiloan Reguler',
                            'Setrika Uap & Lipat Rapi',
                            'Dry Cleaning Satuan',
                            'Spotting & Treatment Noda',
                            'Cuci Bed Cover & Selimut',
                            'Cuci Karpet & Perlengkapan',
                            'Perawatan Bahan Sutra & Jas',
                            'Operator Mesin Cuci Industri',
                            'Quality Control Laundry',
                            'Packing & Sealing Rapi',
                        ];
                    @endphp
                    @foreach ($suggestedSkills as $skill)
                        <button type="button" onclick="addSkillChip('{{ $skill }}')" class="px-2 py-1 bg-slate-100 hover:bg-[#DCE8FF] hover:text-[#062072] text-[11px] font-medium text-slate-700 rounded-md transition-colors">
                            + {{ $skill }}
                        </button>
                    @endforeach
                </div>
                @error('skills')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Buttons -->
            <div class="pt-4 border-t border-[#E4E7EC] flex items-center justify-end gap-3">
                <x-buttons.btn variant="outline" href="{{ route('employees.show', $employee) }}" class="text-xs">
                    Batal
                </x-buttons.btn>
                <x-buttons.btn variant="primary" type="submit" class="text-xs">
                    Simpan Perubahan
                </x-buttons.btn>
            </div>
        </form>
    </x-cards.card>
</div>
@endsection
