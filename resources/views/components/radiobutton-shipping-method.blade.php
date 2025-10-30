<div class="flex w-full flex-col items-start space-y-2 text-xs">
    @foreach ($options as $option)
        <label
            for="{{ $prefix ?? '' }}{{ $name }}_{{ $option->id }}"
            class="has-checked:text-azul-sea mb-0 inline-flex w-full cursor-pointer items-center justify-between border-b border-b-gray-200 p-3 text-gray-500 last:border-b-0 has-checked:rounded-md"
        >
            <div class="inline-flex items-center gap-3">
                <input
                    type="radio"
                    name="{{ $name }}"
                    id="{{ $prefix ?? '' }}{{ $name }}_{{ $option->id }}"
                    value="{{ $option->id }}"
                    wire:model.live="{{ $prefix ?? '' }}{{ $name }}"
                    class="form-radio text-azul-mist border-azul-gray focus:ring-azul-gray h-3 w-3 ring-offset-white"
                    {{ isset($default) && $default == $option->id ? 'checked' : '' }}
                />
                {{ $option->name }}
            </div>
            <span class="block font-medium">
                {{ $option->getFormatedPrice() }}
            </span>
        </label>
    @endforeach
</div>
