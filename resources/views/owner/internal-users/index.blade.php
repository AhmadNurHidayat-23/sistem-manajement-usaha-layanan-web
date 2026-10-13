@extends('layouts.app', ['title' => 'Manajemen Akun Internal'])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-[#101828] m-0">Akun & Hak Akses Pengguna</h2>
            <p class="text-xs text-[#667085] mt-1 m-0">Kelola akun dan penetapan cabang untuk pengguna level operasional (Manajer, Kasir, dan Staf).</p>
        </div>
        <x-buttons.btn variant="primary" href="{{ route('owner.internal-users.create') }}" data-modal-open="modal-create-user" class="gap-1.5 text-xs shadow-2xs">
            <x-icons.icon name="plus" class="w-4 h-4" />
            Tambah Akun Internal
        </x-buttons.btn>
    </div>

    <!-- Filter Toolbar -->
    <form method="GET" action="{{ route('owner.internal-users.index') }}" class="flex flex-wrap items-center gap-2.5">
        <div class="flex-1 min-w-[220px]">
            <div class="relative">
                <input type="text" id="q" name="q" value="{{ request('q') }}"
                    placeholder="Cari nama, email, atau nomor telepon..."
                    class="w-full h-10 pl-9 pr-3.5 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] placeholder-[#98A2B3] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                <x-icons.icon name="search" class="w-4 h-4 text-[#667085] absolute left-3 top-3 pointer-events-none" />
            </div>
        </div>

        <div class="w-full sm:w-44 shrink-0">
            <select id="role" name="role" onchange="this.form.submit()"
                class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                <option value="">Semua Peran</option>
                @foreach ($roles as $r)
                    <option value="{{ $r->name }}" {{ request('role') === $r->name ? 'selected' : '' }}>
                        {{ ucfirst($r->name) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="w-full sm:w-48 shrink-0">
            <select id="branch_id" name="branch_id" onchange="this.form.submit()"
                class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                <option value="">Semua Cabang</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                        {{ $branch->display_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <button type="submit"
                class="h-10 px-4 bg-[#062072] hover:bg-[#04184F] text-white text-[13px] font-semibold rounded-lg transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                <x-icons.icon name="search" class="w-4 h-4 shrink-0" />
                <span>Filter</span>
            </button>
            @if(request()->anyFilled(['q', 'role', 'branch_id']))
                <a href="{{ route('owner.internal-users.index') }}"
                    class="h-10 px-3 bg-white hover:bg-slate-50 border border-[#D0D5DD] text-[#344054] text-[13px] font-medium rounded-lg transition-colors flex items-center justify-center cursor-pointer shadow-2xs">
                    Reset
                </a>
            @endif
        </div>
    </form>

    <!-- Table Card -->
    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-[#E4E7EC] text-[#475467] font-semibold">
                        <th class="py-3 px-4">Nama Pengguna</th>
                        <th class="py-3 px-4">Kontak</th>
                        <th class="py-3 px-4">Peran (Role)</th>
                        <th class="py-3 px-4">Cabang Penugasan</th>
                        <th class="py-3 px-4">Terdaftar</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E4E7EC] text-[#344054]">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    @if ($user->avatar_url)
                                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-8 h-8 rounded-full object-cover shrink-0 shadow-2xs border border-[#E4E7EC]">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-[#0B3A9E] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-semibold text-[#101828] text-xs m-0">{{ $user->name }}</p>
                                        <p class="text-[11px] text-[#667085] m-0">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-[#475467]">
                                {{ $user->phone ?? '-' }}
                            </td>
                            <td class="py-3 px-4">
                                @php
                                    $roleName = $user->role?->name;
                                    $badgeClass = match($roleName) {
                                        'owner' => 'bg-purple-50 text-purple-700 border-purple-200',
                                        'manager' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'cashier' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'employee' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        default => 'bg-slate-50 text-slate-700 border-slate-200',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border {{ $badgeClass }}">
                                    {{ ucfirst($roleName ?? '-') }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                @if($roleName === 'owner')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                        Semua Cabang
                                    </span>
                                @elseif($user->branches->isNotEmpty())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($user->branches as $b)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-50 text-slate-700 border border-slate-200">
                                                {{ $b->display_name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-[11px] text-[#98A2B3] italic">Belum ditugaskan</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-[#667085] text-[11px]">
                                {{ $user->created_at?->format('d M Y') ?? '-' }}
                            </td>
                            <td class="py-3 px-4 text-right">
                                <x-buttons.btn size="sm" variant="outline" href="{{ route('owner.internal-users.edit', $user) }}"
                                    data-action="edit-user"
                                    data-payload="{{ json_encode([
                                        'id' => $user->id,
                                        'name' => $user->name,
                                        'email' => $user->email,
                                        'phone' => $user->phone,
                                        'role_id' => $user->role_id,
                                        'branch_ids' => $user->branches->pluck('id')->toArray(),
                                    ]) }}"
                                    class="gap-1 text-[11px] py-1 px-2.5">
                                    <x-icons.icon name="edit" class="w-3.5 h-3.5" />
                                    Edit
                                </x-buttons.btn>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-[#667085]">
                                <p class="text-xs font-medium">Tidak ada data akun internal yang ditemukan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-[#E4E7EC]">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Tambah Akun Blur -->
    @include('owner.internal-users.partials.modal-create')

    <!-- Modal Edit Akun Blur -->
    @include('owner.internal-users.partials.modal-edit')
</div>
@endsection
