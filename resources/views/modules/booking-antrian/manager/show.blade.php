@php
    $isEmployee = auth()->user()?->role?->name === 'employee';
@endphp
@extends('layouts.app', ['title' => $isEmployee ? 'Detail Tugas | SIMAUL' : 'Detail Order #' . $order->id . ' | SIMAUL'])

@section('content')
<div class="w-full space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            @if($isEmployee)
                <h2 class="text-xl font-bold text-[#101828] m-0">Detail Tugas</h2>
                <p class="text-xs text-[#667085] mt-1 m-0">Pekerjaan yang ditugaskan kepada Anda &bull; Terakhir diperbarui {{ $order->updated_at->diffForHumans() }}</p>
            @else
                <h3 class="text-xl font-bold text-[#101828] m-0">Detail Order #{{ $order->id }}</h3>
                <p class="text-xs text-[#667085] mt-1 m-0">Dibuat {{ $order->created_at->diffForHumans() }}</p>
            @endif
        </div>
        <div class="flex items-center gap-2.5">
            @if($isEmployee)
                <a href="{{ route('queue.index') }}" class="inline-flex items-center justify-center px-3.5 py-2 text-xs font-semibold rounded-lg bg-white border border-[#D0D5DD] text-[#344054] hover:bg-[#F5F8FC] hover:text-[#062072] transition-colors shadow-2xs">
                    Kembali ke Antrean
                </a>
            @else
                <x-buttons.btn variant="outline" href="{{ route('orders.index') }}">
                    Kembali
                </x-buttons.btn>
            @endif
        </div>
    </div>

    {{-- Flash Alerts --}}
    @foreach(['success', 'error'] as $msg)
        @if(session($msg))
        <div class="{{ $msg === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-rose-50 border-rose-200 text-rose-700' }} border px-4 py-3 rounded-lg text-sm flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $msg === 'success' ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' }}"/>
            </svg>
            <span>{{ session($msg) }}</span>
        </div>
        @endif
    @endforeach

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Info Utama --}}
        <div class="md:col-span-2 space-y-4">
            <x-cards.card :title="$isEmployee ? 'Informasi Tugas' : 'Informasi Order'">
                <dl class="divide-y divide-[#E4E7EC]">
                    @php
                        $rows = [
                            ['Pelanggan',          $order->user?->name ?? '-'],
                            ['Nama Usaha',         $order->branch?->display_name ?? '-'],
                            ['Jenis Pekerjaan',    'Drop-off (Titipan Layanan)'],
                            ['Layanan',            $order->service?->name ?? '-'],
                            ['Petugas',            $order->employee?->name ?? 'Belum ditentukan'],
                            ['Est. Kuantitas',     $order->estimated_quantity ? number_format($order->estimated_quantity, 2) : '-'],
                            ['Antar-Jemput',       $order->pickup_option ? 'Ya' : 'Tidak'],
                            ['Est. Selesai',       $order->estimated_finish_at ? $order->estimated_finish_at->translatedFormat('d F Y, H:i') . ' WIB' : '-'],
                            ['Catatan',            $order->notes ?? '-'],
                        ];
                    @endphp
                    @foreach($rows as [$label, $value])
                    <div class="py-3 flex justify-between gap-4">
                        <dt class="text-sm text-[#667085] shrink-0">{{ $label }}</dt>
                        <dd class="text-sm font-medium text-[#101828] text-right">{{ $value }}</dd>
                    </div>
                    @endforeach
                </dl>
            </x-cards.card>

            @if($order->details->count())
            <x-cards.card title="Layanan Tambahan">
                <ul class="divide-y divide-[#E4E7EC]">
                    @foreach($order->details as $detail)
                    <li class="py-2 flex justify-between text-sm">
                        <span class="text-[#101828]">{{ $detail->service?->name }}</span>
                        <span class="text-[#667085]">Rp{{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                    </li>
                    @endforeach
                </ul>
            </x-cards.card>
            @endif
        </div>

        {{-- Status & Aksi --}}
        <div class="space-y-4">
            @if($isEmployee)
                {{-- Status Card Khusus Karyawan --}}
                @php
                    $stepIndex = match($order->status->value) {
                        'received' => 1,
                        'processing' => 2,
                        'ready', 'completed' => 3,
                        default => 0,
                    };
                @endphp
                <x-cards.card title="Status Pengerjaan">
                    {{-- Progres Visual Sederhana Tiga Tahapan SRS --}}
                    <div class="mb-5 pb-4 border-b border-[#E4E7EC]">
                        <p class="text-[11px] font-semibold text-[#667085] uppercase tracking-wider mb-2">Progres Pengerjaan</p>
                        <div class="grid grid-cols-3 gap-1.5 text-center text-xs">
                            <div class="py-2 px-1 rounded-lg {{ $stepIndex >= 1 ? 'bg-[#DCE8FF] text-[#062072] font-bold border border-[#062072]/20' : 'bg-[#F2F4F7] text-[#98A2B3]' }}">
                                1. Menunggu
                            </div>
                            <div class="py-2 px-1 rounded-lg {{ $stepIndex >= 2 ? 'bg-[#DCE8FF] text-[#062072] font-bold border border-[#062072]/20' : 'bg-[#F2F4F7] text-[#98A2B3]' }}">
                                2. Dikerjakan
                            </div>
                            <div class="py-2 px-1 rounded-lg {{ $stepIndex === 3 ? 'bg-emerald-100 text-emerald-800 font-bold border border-emerald-300' : 'bg-[#F2F4F7] text-[#98A2B3]' }}">
                                3. Selesai
                            </div>
                        </div>
                    </div>

                    {{-- Aksi Kontekstual Berdasarkan Status --}}
                    @if($order->status->value === 'received')
                        <div class="space-y-3">
                            <div class="p-3 bg-amber-50 rounded-lg border border-amber-200 text-xs text-amber-800">
                                Pesanan drop-off telah diterima dan siap untuk mulai dikerjakan.
                            </div>
                            <form method="POST" action="{{ route('orders.update-status', $order) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="processing">
                                <button type="submit" class="w-full py-2.5 px-4 bg-[#062072] hover:bg-[#04184F] text-white text-xs font-bold rounded-lg transition-colors cursor-pointer shadow-2xs">
                                    Mulai Pengerjaan
                                </button>
                            </form>
                        </div>
                    @elseif($order->status->value === 'processing')
                        <div class="space-y-3">
                            <div class="p-3 bg-blue-50 rounded-lg border border-blue-200 text-xs text-blue-800">
                                Pesanan drop-off sedang Anda kerjakan. Tandai selesai jika tuntas.
                            </div>
                            <form method="POST" action="{{ route('orders.update-status', $order) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition-colors cursor-pointer shadow-2xs">
                                    Tandai Selesai
                                </button>
                            </form>
                        </div>
                    @elseif(in_array($order->status->value, ['completed', 'ready'], true))
                        <div class="p-3.5 bg-emerald-50 rounded-lg border border-emerald-200 text-xs text-emerald-800 font-medium text-center">
                            Pesanan drop-off telah selesai. Status pengerjaan bersifat read-only.
                        </div>
                    @elseif($order->status->value === 'pending')
                        <div class="p-3.5 bg-slate-100 rounded-lg border border-slate-200 text-xs text-slate-700">
                            <strong>Status: Menunggu Konfirmasi.</strong><br>
                            Pesanan belum dapat dikerjakan karena belum dikonfirmasi oleh pengelola.
                        </div>
                    @else
                        <div class="p-3.5 bg-rose-50 rounded-lg border border-rose-200 text-xs text-rose-800">
                            Status: {{ $order->status->label() }}
                        </div>
                    @endif
                </x-cards.card>
            @else
                {{-- Status Card untuk Manager / Owner / Cashier --}}
                @php
                    $statusConfig = [
                        'pending'    => ['label' => 'Pending',   'class' => 'bg-amber-100 text-amber-800', 'dot' => 'bg-amber-500'],
                        'received'   => ['label' => 'Diterima',  'class' => 'bg-slate-100 text-slate-800', 'dot' => 'bg-slate-500'],
                        'processing' => ['label' => 'Diproses',  'class' => 'bg-[#DCE8FF] text-[#062072]', 'dot' => 'bg-[#0B3A9E]'],
                        'ready'      => ['label' => 'Siap',      'class' => 'bg-emerald-50 text-emerald-800', 'dot' => 'bg-emerald-500'],
                        'completed'  => ['label' => 'Selesai',   'class' => 'bg-emerald-100 text-emerald-800', 'dot' => 'bg-emerald-500'],
                        'cancelled'  => ['label' => 'Dibatalkan', 'class' => 'bg-rose-100 text-rose-800', 'dot' => 'bg-rose-400'],
                    ];
                    $sc = $statusConfig[$order->status->value] ?? ['label' => $order->status->value, 'class' => 'bg-slate-100 text-slate-700', 'dot' => 'bg-slate-400'];
                @endphp
                <x-cards.card title="Status">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="inline-block w-2 h-2 rounded-full {{ $sc['dot'] }}"></span>
                        <span class="text-sm font-semibold {{ $sc['class'] }} px-2 py-0.5 rounded-full">{{ $sc['label'] }}</span>
                    </div>

                    <form method="POST" action="{{ route('orders.update-status', $order) }}" class="space-y-2">
                        @csrf @method('PATCH')
                        <select name="status" id="order-status-select"
                            class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#062072]">
                            @php
                                $orderStatusOptions = [
                                    'pending'    => 'Menunggu (Pending)',
                                    'received'   => 'Menunggu (Diterima)',
                                    'processing' => 'Sedang Dikerjakan (Diproses)',
                                    'ready'      => 'Siap Diambil',
                                    'completed'  => 'Selesai',
                                ];
                            @endphp
                            @foreach($orderStatusOptions as $val => $lbl)
                                <option value="{{ $val }}" {{ $order->status->value === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                        <x-buttons.btn variant="primary" type="submit" class="w-full justify-center" id="btn-update-status-order">
                            Update Status
                        </x-buttons.btn>
                    </form>
                </x-cards.card>

                @can('cancel', $order)
                <x-cards.card title="Aksi Administratif">
                    <div class="space-y-2">
                        @if(in_array($order->status->value, ['pending', 'received']))
                        <form method="POST" action="{{ route('orders.cancel', $order) }}">
                            @csrf @method('PATCH')
                            <button type="submit" id="btn-batalkan-order"
                                class="w-full flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer">
                                Batalkan Order
                            </button>
                        </form>
                        @endif
                    </div>
                </x-cards.card>
                @endcan
            @endif
        </div>
    </div>
</div>
@endsection
