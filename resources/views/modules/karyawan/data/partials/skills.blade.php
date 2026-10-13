<!-- Skills & Specializations -->
<x-cards.card class="p-5">
    <div class="flex items-center justify-between mb-3">
        <div>
            <h4 class="text-sm font-bold text-[#101828] m-0">Spesialisasi & Keahlian Karyawan</h4>
            <p class="text-xs text-[#667085] mt-0.5 m-0">Keahlian teknis yang dimiliki untuk penugasan layanan pelanggan.</p>
        </div>
        @if (in_array(auth()->user()?->role?->name, ['owner', 'manager']))
            <a href="{{ route('employees.edit', $employee) }}" class="text-xs font-semibold text-[#062072] hover:underline">
                Kelola Keahlian
            </a>
        @endif
    </div>
    <div class="flex flex-wrap gap-2">
        @forelse ($employee->skills as $skill)
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold bg-[#DCE8FF] text-[#062072] border border-[#0B3A9E]/20">
                <x-icons.icon name="check" class="w-3.5 h-3.5 text-[#0B3A9E]" />
                {{ $skill->skill_name }}
            </span>
        @empty
            <p class="text-xs text-[#667085] italic m-0">Belum ada spesialisasi keahlian yang ditambahkan untuk karyawan ini.</p>
        @endforelse
    </div>
</x-cards.card>
