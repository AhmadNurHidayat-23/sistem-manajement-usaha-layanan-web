<!-- Quick Member Management (1 col) -->
<div>
    <x-cards.card class="p-5 h-full flex flex-col justify-between">
        <div>
            <h4 class="text-sm font-bold text-[#101828] mb-3">Status & Data Member</h4>
            
            <div class="space-y-3 text-xs">
                <div class="flex justify-between py-2 border-b border-[#E4E7EC]">
                    <span class="text-[#667085]">Status Keanggotaan</span>
                    <x-badges.badge :variant="$st['badge']">{{ $st['label'] }}</x-badges.badge>
                </div>
                <div class="flex justify-between py-2 border-b border-[#E4E7EC]">
                    <span class="text-[#667085]">Tanggal Bergabung</span>
                    <span class="font-semibold text-[#101828]">{{ \Carbon\Carbon::parse($membership->joined_at)->translatedFormat('d F Y') }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-[#E4E7EC]">
                    <span class="text-[#667085]">Email Terdaftar</span>
                    <span class="font-semibold text-[#101828]">{{ $membership->user->email }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-[#E4E7EC]">
                    <span class="text-[#667085]">Nilai Tukar Poin</span>
                    <span class="font-semibold text-emerald-600">1 Poin = Rp 1.000</span>
                </div>
            </div>
        </div>

        @if (in_array(auth()->user()?->role?->name, ['owner', 'manager']))
            <div class="pt-4 border-t border-[#E4E7EC] mt-4">
                <form method="POST" action="{{ route('members.update', $membership) }}" class="space-y-3">
                    @csrf
                    @method('PUT')
                    <label for="update_status" class="block text-xs font-semibold text-[#101828]">Ubah Status Member</label>
                    <div class="flex items-center gap-2">
                        <div class="flex-1">
                            <select id="update_status" name="status" class="w-full h-11 px-3.5 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072]">
                                <option value="active" {{ $membership->status->value === 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="inactive" {{ $membership->status->value === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                                <option value="suspended" {{ $membership->status->value === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                        </div>
                        <button type="submit" class="h-11 px-4 bg-[#062072] text-white rounded-lg text-xs font-semibold hover:bg-[#04184F] transition-colors shrink-0 cursor-pointer">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </x-cards.card>
</div>
