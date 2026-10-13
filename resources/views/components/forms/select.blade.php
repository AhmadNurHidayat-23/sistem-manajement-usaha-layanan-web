@props([
    'name',
    'label' => null,
    'options' => [],
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'id' => null,
])

@php
    $id = $id ?? $name;
    $selectedValue = old($name, $value);
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

    <select
        id="{{ $id }}"
        name="{{ $name }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'w-full h-11 px-3.5 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] focus:border-[#062072] transition-colors']) }}
    >
        @if ($placeholder)
            <option value="" {{ $selectedValue === null || $selectedValue === '' ? 'selected' : '' }}>
                {{ $placeholder }}
            </option>
        @endif

        @if (!empty($options))
            @foreach ($options as $optVal => $optLabel)
                @if (is_array($optLabel))
                    <option value="{{ $optLabel['value'] }}" {{ (string)$selectedValue === (string)$optLabel['value'] ? 'selected' : '' }}>
                        {{ $optLabel['label'] }}
                    </option>
                @else
                    <option value="{{ $optVal }}" {{ (string)$selectedValue === (string)$optVal ? 'selected' : '' }}>
                        {{ $optLabel }}
                    </option>
                @endif
            @endforeach
        @else
            {{ $slot }}
        @endif
    </select>

    @error($name)
        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
    @enderror
</div>
