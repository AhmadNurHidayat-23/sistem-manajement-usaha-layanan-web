@extends('layouts.app', ['title' => 'Tulis Ulasan'])

@section('content')
<div class="max-w-2xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-xl font-bold text-[#101828] m-0">Tulis Ulasan</h3>
            <p class="text-xs text-[#667085] mt-1 m-0">Bagikan pengalaman Anda setelah menggunakan layanan ini.</p>
        </div>
        <x-buttons.btn variant="outline" href="{{ route('bookings.index') }}">Kembali</x-buttons.btn>
    </div>

    @if (session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Transaction summary --}}
    <x-cards.card title="Transaksi" subtitle="Detail transaksi yang akan Anda ulas">
        <dl class="grid grid-cols-2 gap-3 text-sm">
            <div>
                <dt class="text-xs font-semibold text-[#667085] uppercase tracking-wider">Kode Transaksi</dt>
                <dd class="mt-1 font-medium text-[#101828]">{{ $transaction->transaction_code }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold text-[#667085] uppercase tracking-wider">Tanggal</dt>
                <dd class="mt-1 font-medium text-[#101828]">{{ $transaction->transaction_date->format('d M Y') }}</dd>
            </div>
            <div class="col-span-2">
                <dt class="text-xs font-semibold text-[#667085] uppercase tracking-wider">Layanan</dt>
                @php
                    $detail = $transaction->details->first();
                @endphp
                <dd class="mt-1 font-medium text-[#101828]">
                    {{ $detail?->service?->name ?? ($transaction->booking?->service?->name ?? '-') }}
                </dd>
            </div>
        </dl>
    </x-cards.card>

    {{-- Review form --}}
    <x-cards.card title="Beri Ulasan" subtitle="Rating 1 = sangat buruk, 5 = sangat baik">
        <form method="POST" action="{{ route('ulasan.store') }}" class="space-y-5" id="form-ulasan">
            @csrf
            <input type="hidden" name="transaction_id" value="{{ $transaction->id }}">
            <input type="hidden" name="service_id" id="hidden-service-id"
                value="{{ $transaction->booking?->service_id ?? $transaction->details->first()?->service_id }}">

            {{-- Rating picker --}}
            <div>
                <label class="block text-sm font-medium text-[#101828] mb-2">
                    Rating <span class="text-rose-500">*</span>
                </label>
                <div class="flex items-center gap-2" id="star-picker">
                    @for ($i = 1; $i <= 5; $i++)
                        <button type="button" data-value="{{ $i }}"
                            class="star-btn w-10 h-10 rounded-lg flex items-center justify-center border border-[#E4E7EC] bg-white hover:bg-amber-50 hover:border-amber-300 transition-colors"
                            aria-label="Rating {{ $i }}">
                            <svg class="w-6 h-6 text-[#D0D5DD]" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </button>
                    @endfor
                    <span id="rating-label" class="ml-2 text-sm font-medium text-[#667085]">Belum dipilih</span>
                </div>
                <input type="hidden" name="rating" id="rating-input" value="{{ old('rating') }}">
                @error('rating')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Komentar --}}
            <div>
                <label for="komentar" class="block text-sm font-medium text-[#101828] mb-1">
                    Komentar <span class="text-[#667085] text-xs">(opsional)</span>
                </label>
                <textarea id="komentar" name="komentar" rows="4"
                    placeholder="Ceritakan pengalaman Anda menggunakan layanan ini..."
                    class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] placeholder-[#667085] focus:outline-none focus:ring-2 focus:ring-[#062072] resize-none">{{ old('komentar') }}</textarea>
                @error('komentar')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-4 border-t border-[#E4E7EC] flex justify-end gap-3">
                <x-buttons.btn variant="outline" href="{{ route('bookings.index') }}">Batal</x-buttons.btn>
                <x-buttons.btn variant="primary" type="submit" id="btn-kirim-ulasan">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Kirim Ulasan
                </x-buttons.btn>
            </div>
        </form>
    </x-cards.card>
</div>
@endsection
