@props([
    'isGrouped' => false,
    'options' => [],
    'name' => 'radio',
    'default' => -1,
])

<div class="flex w-full flex-row items-center justify-center gap-1">
    @foreach ($options as $index => $option)
        @php
            $id = $name . '-' . Str::slug($option['text']);
            $class = $isGrouped ? 'group' : '';
            $classWidth = 'w-1/' . count($options);
        @endphp

        <div class="{{ $classWidth }} block">
            <label
                for="{{ $id }}"
                class="btn btn-primary-outline flex w-full cursor-pointer flex-nowrap items-center justify-center text-sm"
                wire:model.live="{{ $name }}"
            >
                <input
                    class="hidden"
                    type="radio"
                    id="{{ $id }}"
                    name="{{ $name }}"
                    value="{{ $option['value'] }}"
                    {{ $default == $option['value'] ? 'checked' : '' }}
                />

                <img
                    alt="{{ $option['text'] }}"
                    src="{{ asset('images/icons/heart-checked.svg') }}"
                    class="invisible me-1 h-6 w-6"
                />
                {{ $option['text'] }}
            </label>
        </div>
    @endforeach
</div>
