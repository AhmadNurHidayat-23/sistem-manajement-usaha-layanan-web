{{-- Kolom Kanan: Form Edit Profil --}}
<div class="lg:col-span-2">
    <div class="bg-white border border-[#E4E7EC] rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-[#E4E7EC] bg-white">
            <h3 class="text-base font-bold text-[#101828] m-0">Edit Informasi Profil</h3>
            <p class="text-xs text-[#667085] mt-1 m-0">Perbarui nama dan nomor kontak yang terhubung dengan akun Anda</p>
        </div>

        <form action="{{ route('customer.profile.update') }}" method="POST" id="form-edit-profil">
            @csrf
            @method('PUT')

            <div class="p-6 space-y-5">
                {{-- Nama Lengkap --}}
                <div>
                    <label for="name" class="block text-sm font-semibold text-[#101828] mb-1.5">
                        Nama Lengkap <span class="text-rose-500">*</span>
                    </label>
                    <input type="text"
                        name="name"
                        id="name"
                        value="{{ old('name', $user->name) }}"
                        required
                        maxlength="255"
                        class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('name') ? 'border-rose-300 ring-2 ring-rose-100' : 'border-[#D0D5DD]' }} text-sm text-[#101828] focus:ring-2 focus:ring-[#062072]/20 focus:border-[#062072] outline-none transition-all bg-white"
                        placeholder="Masukkan nama lengkap Anda">
                    @error('name')
                        <p class="text-xs text-rose-600 mt-1 m-0">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email (Read-only) --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="email" class="block text-sm font-semibold text-[#101828]">
                            Alamat Email
                        </label>
                        <span class="text-[11px] text-[#98A2B3]">Tidak dapat diubah</span>
                    </div>
                    <div class="relative">
                        <input type="email"
                            id="email"
                            value="{{ $user->email }}"
                            disabled
                            readonly
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm text-[#667085] bg-slate-50 cursor-not-allowed select-none">
                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-[#98A2B3]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-[11px] text-[#667085] mt-1.5 m-0 leading-relaxed">
                        Alamat email digunakan sebagai identitas login utama Anda di SIMAUL dan tidak dapat diubah secara mandiri demi keamanan akun.
                    </p>
                </div>

                {{-- Nomor Telepon --}}
                <div>
                    <label for="phone" class="block text-sm font-semibold text-[#101828] mb-1.5">
                        Nomor Telepon / WhatsApp <span class="text-xs text-[#667085] font-normal">(opsional)</span>
                    </label>
                    <div class="relative">
                        <input type="tel"
                            name="phone"
                            id="phone"
                            value="{{ old('phone', $user->phone) }}"
                            maxlength="20"
                            class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('phone') ? 'border-rose-300 ring-2 ring-rose-100' : 'border-[#D0D5DD]' }} text-sm text-[#101828] focus:ring-2 focus:ring-[#062072]/20 focus:border-[#062072] outline-none transition-all bg-white"
                            placeholder="Contoh: 081234567890">
                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-[#98A2B3]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </div>
                    </div>
                    @error('phone')
                        <p class="text-xs text-rose-600 mt-1 m-0">{{ $message }}</p>
                    @enderror
                    <p class="text-[11px] text-[#667085] mt-1.5 m-0 leading-relaxed">
                        Digunakan untuk konfirmasi jadwal reservasi janji temu dan informasi status pengerjaan pesanan via WhatsApp/SMS.
                    </p>
                </div>
            </div>

            {{-- Submit Bar --}}
            <div class="px-6 py-4 bg-[#F8F9FC] border-t border-[#E4E7EC] flex items-center justify-end gap-3">
                <a href="{{ route('customer.dashboard') }}"
                    class="px-4 py-2.5 text-sm font-medium text-[#667085] hover:text-[#101828] transition-colors no-underline">
                    Kembali ke Beranda
                </a>
                <button type="submit"
                    id="btn-save-profile"
                    class="px-6 py-2.5 bg-[#062072] hover:bg-[#04184F] text-white rounded-xl text-sm font-semibold transition-all shadow-sm cursor-pointer active:scale-[0.98]">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
