@props([
    /** @var array[] */
    'payment_methods',
    'prefix' => '',
])

<div {{ $attributes->class(['mx-1 flex flex-row gap-6 items-center justify-start' ]) }}>
    @foreach ($payment_methods as $index=>$method)
        <div class="flex items-center gap-x-2  flex-row item-radio">
            <x-input
                type="radio"
                name="payment_method"
                id="payment_method_{{ $method['code'].$prefix ?? '' }}"
                wire:model="payment_method"
                value="{{ $method['code'] }}"
            ></x-input>

            <label
                for="payment_method_{{ $method['code'].$prefix ?? '' }}"
                class=" capitalize m-0! cursor-pointer"
            >
                {{ $method['code'] }}
            </label>
        </div>
    @endforeach</div>
<x-error
    class="text-error/80 w-full text-[11px]"
    field="payment_method"
/>
