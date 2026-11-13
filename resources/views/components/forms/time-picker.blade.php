@props([
    'name',
    'label' => null,
    'value' => null,
    'placeholder' => 'HH:mm',
    'required' => false,
    'id' => null,
    'helpText' => null,
])

@php
    $id = $id ?? $name;
    $val = old($name, $value);
    if ($val instanceof \DateTimeInterface) {
        $val = $val->format('H:i');
    } elseif (is_string($val) && strlen($val) > 5) {
        $val = substr($val, 0, 5);
    }
@endphp

<div>
    @if ($label)
        <label for="{{ $id }}" class="block text-xs font-semibold text-[#101828] mb-1">
            {{ $label }}
            @if ($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative w-full custom-time-wrapper">
        <input
            type="time"
            id="{{ $id }}"
            name="{{ $name }}"
            value="{{ $val }}"
            data-custom-time-picker="true"
            {{ $required ? 'required' : '' }}
            {{ $attributes->merge(['class' => 'w-full h-10 px-3 py-2 bg-white border border-[#D0D5DD] rounded-lg text-xs font-semibold text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] focus:border-[#062072] transition-colors']) }}
        >
    </div>

    @if ($helpText)
        <p class="mt-1 text-[11px] text-[#667085] m-0">{{ $helpText }}</p>
    @endif

    @error($name)
        <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
    @enderror
</div>

