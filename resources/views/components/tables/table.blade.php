@props([
    'headers' => [],
])

<div class="w-full overflow-x-auto border border-[#E4E7EC] rounded-xl bg-white shadow-2xs">
    <table class="w-full text-left text-[13px] text-[#101828] border-collapse">
        <thead class="bg-[#F5F8FC] border-b border-[#E4E7EC] text-[11px] font-semibold text-[#667085] uppercase tracking-wider">
            <tr>
                @if (isset($headerSlot))
                    {{ $headerSlot }}
                @else
                    @foreach ($headers as $header)
                        <th scope="col" class="px-3.5 sm:px-4 py-3 text-left whitespace-nowrap">{{ $header }}</th>
                    @endforeach
                @endif
            </tr>
        </thead>
        <tbody class="divide-y divide-[#E4E7EC] bg-white text-[12px] sm:text-[13px]">
            {{ $slot }}
        </tbody>
    </table>
</div>
