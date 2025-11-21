@props([
    'isGrouped' => false,
    'options' => [],
    'name' => 'radio',
    'default' => -1,
    'prefix' => '',
])

<div
    class="radioboolean flex w-full flex-row items-center justify-center gap-1"
>
    @foreach ($options as $index => $option)
        @php
            $id = $name . '-' . Str::slug($option['text']);
            $class = $isGrouped ? 'group' : '';
            $classWidth = 'w-1/' . count($options);
        @endphp

        <div class="{{ $classWidth }} block text-center">
            <label
                for="{{ $prefix . '-' . $id }}"
                class="btn btn-primary-outline mx-auto flex h-8 w-8 cursor-pointer flex-nowrap items-center justify-center border-2! p-0! text-sm"
                data-test="{{ $prefix . '-' . $id }}"
            >
                <input
                    class="peer hidden"
                    type="radio"
                    wire:model.live="{{ $name }}"
                    id="{{ $prefix . '-' . $id }}"
                    name="{{ $name }}"
                    value="{{ $option['value'] }}"
                    {{ $default == $option['value'] ? 'checked' : '' }}

                />

                <x-bi-check class="me-1 hidden h-6 w-6 peer-checked:block" />
            </label>

            {{ $option['text'] }}
        </div>
    @endforeach
</div>
