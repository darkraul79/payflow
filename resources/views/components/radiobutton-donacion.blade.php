@props([
    'isGrouped' => false,
    'options' => [],
    'name' => 'radio',
    'default' => -1,
    'prefix' => '',
])

<div class="flex w-full flex-row  items-center justify-between {{$isGrouped ? 'gap-0':'gap-1'}} radio-buttons">
    @foreach ($options as $index => $option)
        @php
            $id = $name . '-' . Str::slug($option['text']);
            $class = $isGrouped ? 'group' : '';
            $classWidth = 'w-full  w-1/' . count($options);
        @endphp

        <div class="{{ $classWidth }} block h-full">
            <label
                for="{{ $prefix . '-' . $id }}"
                class="btn btn-primary-outline flex w-full flex-wrap sm:flex-nowrap cursor-pointer h-full  items-end justify-center text-sm {{ $isGrouped ? 'collapsed':'' }}"
                data-test="{{ $prefix . '-' . $id }}"
            >
                <input
                    class="hidden"
                    type="radio"
                    id="{{ $prefix . '-' . $id }}"
                    name="{{ $name }}"
                    wire:model.live="{{ $name == 'amount_select' ? $name . '_' . $option['value'] : $name }}"
                    value="{{ $option['value'] }}"
                    {{ $default == $option['value'] ? 'checked' : '' }}
                />

                <img
                    alt="{{ $option['text'] }}"
                    src="{{ asset('images/icons/heart-checked.svg') }}"
                    class="invisible me-1 h-6 w-6"
                />
                <span class="text-nowrap">{{ $option['text'] }}</span>
            </label>
        </div>
    @endforeach
</div>
