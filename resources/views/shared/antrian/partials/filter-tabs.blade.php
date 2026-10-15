<div class="flex items-center gap-2 overflow-x-auto pb-1">
    @php
        $filtersList = [
            'semua' => ['label' => 'Semua', 'count' => $counts['semua']],
            'menunggu' => ['label' => 'Menunggu', 'count' => $counts['menunggu']],
            'dikerjakan' => ['label' => 'Dikerjakan', 'count' => $counts['dikerjakan']],
            'selesai' => ['label' => 'Selesai', 'count' => $counts['selesai']],
        ];
    @endphp
    @foreach($filtersList as $key => $item)
        @php
            $isActive = ($currentFilter ?? 'semua') === $key;
        @endphp
        <a href="{{ route('queue.index', $key === 'semua' ? [] : ['status' => $key]) }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors shrink-0 {{ $isActive ? 'bg-[#062072] text-white shadow-2xs' : 'bg-white border border-[#E4E7EC] text-[#475467] hover:bg-[#F5F8FC] hover:text-[#101828]' }}">
            <span>{{ $item['label'] }}</span>
            <span class="inline-flex items-center justify-center px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ $isActive ? 'bg-white/20 text-white' : 'bg-[#F2F4F7] text-[#475467]' }}">
                {{ $item['count'] }}
            </span>
        </a>
    @endforeach
</div>
