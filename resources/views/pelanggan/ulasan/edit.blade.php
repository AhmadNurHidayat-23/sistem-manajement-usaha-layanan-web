@extends('layouts.pelanggan', ['title' => 'Edit Ulasan'])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-[#667085]">
        <a href="{{ route('customer.ulasan.index') }}" class="hover:text-[#062072] no-underline text-[#667085]">Ulasan Saya</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-[#101828] font-medium">Edit Ulasan</span>
    </nav>

    <!-- Header -->
    <div>
        <h2 class="text-xl font-bold text-[#101828] m-0">Edit Ulasan</h2>
        <p class="text-xs text-[#667085] mt-1 m-0">Perbarui ulasan dan rating kepuasan yang pernah Anda berikan</p>
    </div>

    <!-- Error Alert -->
    @if (session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <!-- Service Summary Card -->
    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-xs p-5">
        <h3 class="text-xs font-bold uppercase tracking-wider text-[#667085] mb-3">Layanan yang Diulas</h3>
        <div class="flex items-start justify-between gap-4">
            <div>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-[#DCE8FF] text-[#062072] mb-1">
                    {{ $ulasan->service?->category?->name ?? 'Layanan' }}
                </span>
                <h4 class="text-base font-bold text-[#101828] m-0">{{ $ulasan->service?->name ?? 'Layanan' }}</h4>
                <p class="text-xs text-[#667085] mt-1 m-0">
                    Usaha: <span class="font-medium text-[#101828]">
                        {{ $ulasan->transaction?->branch?->display_name ?? $ulasan->transaction?->order?->branch?->display_name ?? 'Usaha' }}
                    </span>
                    @if ($ulasan->transaction?->order_id)
                        • Order #{{ $ulasan->transaction->order_id }}
                    @endif
                </p>
            </div>
            <div class="text-right">
                <span class="text-xs text-[#667085]">Diulas pada:</span>
                <p class="text-xs font-medium text-[#101828] mt-0.5 m-0">{{ $ulasan->created_at->format('d M Y') }}</p>
            </div>
        </div>
    </div>

    <!-- Review Edit Form Card -->
    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-xs p-5 sm:p-6">
        <form method="POST" action="{{ route('customer.ulasan.update', $ulasan) }}" id="form-edit-ulasan" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Interactive Star Rating -->
            <div>
                <label class="block text-sm font-medium text-[#101828] mb-2">
                    Penilaian Kepuasan <span class="text-rose-500">*</span>
                </label>
                
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1" id="star-rating-container">
                        @for ($i = 1; $i <= 5; $i++)
                            <button type="button" data-rating="{{ $i }}"
                                class="star-btn p-1 text-gray-300 hover:text-amber-400 focus:outline-none transition-colors cursor-pointer bg-transparent border-0"
                                aria-label="Beri bintang {{ $i }}">
                                <svg class="w-8 h-8 fill-current" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </button>
                        @endfor
                    </div>
                    <span id="rating-label" class="text-sm font-semibold text-[#062072] ml-2">Pilih Rating</span>
                </div>

                <input type="hidden" name="rating" id="rating-input" value="{{ old('rating', $ulasan->rating) }}">
                @error('rating')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Comment Textarea -->
            <div>
                <label for="komentar" class="block text-sm font-medium text-[#101828] mb-1.5">
                    Komentar & Ulasan
                    <span class="text-[#667085] font-normal text-xs ml-1">(opsional)</span>
                </label>
                <textarea id="komentar" name="komentar" rows="4" maxlength="2000"
                    placeholder="Ceritakan kebersihan cucian, ketepatan waktu, keramahan staf, atau hal lainnya..."
                    class="w-full px-3.5 py-2.5 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] placeholder-[#667085] focus:outline-none focus:ring-2 focus:ring-[#062072] resize-none">{{ old('komentar', $ulasan->komentar) }}</textarea>
                @error('komentar')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
                <p class="text-[11px] text-[#667085] mt-1 m-0">Maksimum 2.000 karakter</p>
            </div>

            <!-- Actions -->
            <div class="pt-4 border-t border-[#E4E7EC] flex items-center justify-between gap-3">
                <a href="{{ route('customer.ulasan.index') }}"
                    class="inline-flex items-center px-4 py-2.5 border border-[#E4E7EC] rounded-lg text-sm font-medium text-[#667085] hover:text-[#101828] hover:bg-[#F5F8FC] transition-colors no-underline">
                    Batal
                </a>
                <button type="submit" id="btn-update-ulasan"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#062072] hover:bg-[#04184F] text-white rounded-lg text-sm font-semibold transition-colors cursor-pointer shadow-sm border-0"
                    onclick="this.disabled=true; this.form.submit();">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
