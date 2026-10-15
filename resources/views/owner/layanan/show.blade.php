@extends('layouts.app', ['title' => 'Detail Layanan ' . $service->name])

@section('content')
<div class="max-w-4xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-xl font-bold text-[#101828] m-0">{{ $service->name }}</h3>
            <p class="text-xs text-[#667085] mt-1 m-0">Detail spesifikasi dan tarif layanan.</p>
        </div>
        <div class="flex items-center gap-3">
            <x-buttons.btn variant="outline" href="{{ route('catalog.services.index') }}">
                Kembali
            </x-buttons.btn>
            <x-buttons.btn variant="secondary" href="{{ route('catalog.services.edit', $service) }}">
                Edit Layanan
            </x-buttons.btn>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 space-y-6">
            <x-cards.card title="Spesifikasi Layanan" subtitle="Informasi rincian katalog">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-xs font-semibold text-[#667085] uppercase tracking-wider">Nama Layanan</dt>
                        <dd class="mt-1 font-medium text-[#101828]">{{ $service->name }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold text-[#667085] uppercase tracking-wider">Status Layanan</dt>
                        <dd class="mt-1">
                            @if ($service->is_active)
                                <x-badges.badge variant="success">Aktif Beroperasi</x-badges.badge>
                            @else
                                <x-badges.badge variant="neutral">Non-Aktif</x-badges.badge>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold text-[#667085] uppercase tracking-wider">Kategori</dt>
                        <dd class="mt-1 font-medium text-[#101828]">{{ $service->category?->name ?? '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold text-[#667085] uppercase tracking-wider">Unit Usaha Berlaku</dt>
                        <dd class="mt-1 font-medium text-[#101828]">{{ $service->branch?->display_name ?? 'Semua Usaha (Global)' }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold text-[#667085] uppercase tracking-wider">Tarif Harga</dt>
                        <dd class="mt-1 font-bold text-[#062072] text-lg">
                            Rp {{ number_format($service->price, 0, ',', '.') }}{{ $service->unit ? ' / ' . $service->unit : '' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold text-[#667085] uppercase tracking-wider">Satuan Unit</dt>
                        <dd class="mt-1 font-medium text-[#101828]">{{ $service->unit ?? '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold text-[#667085] uppercase tracking-wider">Estimasi Durasi</dt>
                        <dd class="mt-1 font-medium text-[#101828]">{{ $service->estimated_duration ? $service->estimated_duration . ' Menit' : 'Belum diatur' }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold text-[#667085] uppercase tracking-wider">Tipe Layanan</dt>
                        <dd class="mt-1 capitalize font-medium text-[#101828]">{{ $service->service_type }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold text-[#667085] uppercase tracking-wider">Komisi Karyawan</dt>
                        <dd class="mt-1">
                            @if ($service->hasCommissionConfigured())
                                <x-badges.badge variant="primary">
                                    {{ $service->formatted_commission }} ({{ $service->commission_type === \App\Enums\CommissionType::Percentage ? 'Persentase' : 'Nominal Tetap' }})
                                </x-badges.badge>
                            @else
                                <span class="text-xs text-[#667085] italic">Belum diatur</span>
                            @endif
                        </dd>
                    </div>

                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold text-[#667085] uppercase tracking-wider">Deskripsi Lengkap</dt>
                        <dd class="mt-1 font-medium text-[#101828]">{{ $service->description ?? 'Tidak ada deskripsi.' }}</dd>
                    </div>
                </dl>
            </x-cards.card>
        </div>

        <div class="space-y-4">
            <x-cards.card title="Foto Layanan">
                @if ($service->image_url)
                    <img src="{{ $service->image_url }}" alt="{{ $service->name }}" class="w-full h-48 object-cover rounded-lg border border-[#E4E7EC]">
                @else
                    <div class="w-full h-48 bg-[#F5F8FC] border border-[#E4E7EC] rounded-lg flex items-center justify-center text-[#667085] text-xs">
                        Tidak ada foto
                    </div>
                @endif
            </x-cards.card>

            <x-cards.card>
                <form method="POST" action="{{ route('catalog.services.toggle-status', $service) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="w-full py-2 px-4 rounded-lg font-medium text-sm text-center cursor-pointer {{ $service->is_active ? 'bg-rose-50 text-rose-600 hover:bg-rose-100 border border-rose-200' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100 border border-emerald-200' }}">
                        {{ $service->is_active ? 'Nonaktifkan Layanan Ini' : 'Aktifkan Layanan Ini' }}
                    </button>
                </form>
            </x-cards.card>
        </div>
    </div>

    {{-- Ulasan / Rating Summary --}}
    <x-cards.card title="Rating & Ulasan" subtitle="Penilaian pelanggan untuk layanan ini">
        <div class="flex items-center gap-6 mb-4">
            <div class="text-center">
                <div class="text-4xl font-bold text-[#062072]">{{ number_format($ratingStats['average'], 1) }}</div>
                <div class="mt-1">@include('owner.ulasan.partials.stars', ['rating' => round($ratingStats['average'])])</div>
                <div class="text-xs text-[#667085] mt-1">{{ $ratingStats['count'] }} ulasan</div>
            </div>
            <div class="flex-1">
                @if ($ratingStats['count'] === 0)
                    <p class="text-sm text-[#667085]">Belum ada ulasan untuk layanan ini.</p>
                @else
                    <a href="{{ route('ulasan.by-service', $service->id) }}"
                        class="inline-flex items-center gap-1.5 text-sm text-[#062072] font-medium hover:underline">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        Lihat Semua Ulasan
                    </a>
                    <div class="mt-3 space-y-2">
                        @foreach ($latestUlasans as $ulasan)
                            <div class="flex items-start gap-2 text-sm">
                                <div class="w-7 h-7 rounded-full bg-[#DCE8FF] text-[#062072] flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ strtoupper(substr($ulasan->user?->name ?? 'U', 0, 2)) }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-[#101828]">{{ $ulasan->user?->name }}</span>
                                        @include('owner.ulasan.partials.stars', ['rating' => $ulasan->rating])
                                    </div>
                                    @if ($ulasan->komentar)
                                        <p class="text-xs text-[#667085] mt-0.5 line-clamp-2">{{ $ulasan->komentar }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </x-cards.card>
</div>
@endsection
