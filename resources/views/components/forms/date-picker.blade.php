@props([
    'name',
    'label' => null,
    'value' => null,
    'placeholder' => 'dd/mm/yyyy',
    'required' => false,
    'id' => null,
])

@php
    $id = $id ?? $name;
    $val = old($name, $value);
    if ($val instanceof \DateTimeInterface) {
        $val = $val->format('Y-m-d');
    }
@endphp

<div class="mb-4">
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-[#101828] mb-1">
            {{ $label }}
            @if ($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif

    <input
        type="date"
        id="{{ $id }}"
        name="{{ $name }}"
        value="{{ $val }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'w-full h-11 px-3.5 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] focus:border-[#062072] transition-colors']) }}
    >

    @error($name)
        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
    @enderror
</div>
