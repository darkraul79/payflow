@props([
    /** @var array[] */
    'payment_methods',
    'prefix' => '',
])

<div {{ $attributes->class(['mx-1 flex flex-row gap-6 items-center justify-start' ]) }}>
    @foreach ($payment_methods as $index=>$method)
        <div class="flex items-center gap-x-3  flex-row item-radio">


            <x-input
                type="radio"
                name="payment_method"
                id="payment_method_{{ $method['code'].$prefix ?? '' }}"
                wire:model="payment_method"
                value="{{ $method['code'] }}"
            ></x-input>


            <label
                for="payment_method_{{ $method['code'].$prefix ?? '' }}"
                class="flex! capitalize m-0! cursor-pointer"
                data-test="{{$prefix}}-payment-method-{{ $method['code'] }}"
            >

                @if(isset($method['icon']) && $method['icon'])
                    <x-icon name="{{ $method['icon'] }}" class="w-5 h-5 me-1" />
                @endif

                {{ $method['code'] }}

            </label>
        </div>
    @endforeach</div>
<x-error
    class="text-error/80 w-full text-[11px]"
    field="payment_method"
/>
