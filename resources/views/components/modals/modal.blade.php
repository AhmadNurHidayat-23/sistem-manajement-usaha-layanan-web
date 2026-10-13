@props([
    'id',
    'title' => null,
    'subtitle' => null,
    'maxWidth' => 'max-w-lg',
    'actions' => null,
])

<div id="{{ $id }}" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/40 backdrop-blur-[2px] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-title" data-backdrop-close>
    <div class="relative w-full {{ $maxWidth }} bg-white rounded-2xl shadow-2xl border border-[#E7ECF3] overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-[#E7ECF3] bg-white sticky top-0 z-10">
            <div>
                @if ($title)
                    <h3 id="{{ $id }}-title" class="text-base sm:text-lg font-bold text-[#101828] m-0">{{ $title }}</h3>
                @endif
                @if ($subtitle)
                    <p class="text-xs text-[#667085] mt-0.5 m-0">{{ $subtitle }}</p>
                @endif
            </div>
            <button type="button" data-modal-close="{{ $id }}" class="text-[#667085] hover:text-[#101828] p-1.5 rounded-lg hover:bg-[#F5F8FC] transition-colors cursor-pointer" aria-label="Tutup Modal">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div class="p-6">
            {{ $slot }}
        </div>

        @if ($actions)
            <div class="px-6 py-3 bg-[#F5F8FC] border-t border-[#E4E7EC] flex justify-end gap-3">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
