<a
    class="border-azul-sea group hover:bg-azul-sea relative w-fit cursor-pointer rounded-full border p-2"
    href="{{ route('cart') }}"
    title="Ir a la cesta"
    wire:ignore.self
>
    <svg
        width="24"
        height="24"
        viewBox="0 0 24 26"
        class="stroke-azul-sea group-hover:stroke-white group-hover:text-white"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
    >
        <path
            d="M10.608 22.5466H8.59584C6.08012 22.5466 4.82226 22.5466 3.95012 21.7902C3.07691 21.0348 2.86369 19.7598 2.43619 17.2098L1.14619 9.51802C0.951194 8.35552 0.853694 7.7748 1.16655 7.39337C1.47834 7.01194 2.05262 7.01194 3.20012 7.01194H19.0873C20.2348 7.01194 20.809 7.01194 21.1208 7.39337C21.4337 7.7748 21.3351 8.35552 21.1412 9.51909L20.8423 11.2977"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
        <path
            d="M17.0357 7.01087C17.0357 5.44799 16.4149 3.94912 15.3097 2.84399C14.2046 1.73887 12.7057 1.11802 11.1429 1.11802C9.57997 1.11802 8.0811 1.73887 6.97598 2.84399C5.87085 3.94912 5.25 5.44799 5.25 7.01087"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
        <path
            d="M16.0513 13.3274C15.147 12.6224 13.6428 12.1814 11.9185 13.2779C9.65559 14.7164 9.14416 19.4654 14.3628 23.4704L14.3643 23.4716C15.3576 24.2343 15.8532 24.6149 16.7156 24.6149C17.5773 24.6149 18.0729 24.2349 19.0659 23.4735L19.0699 23.4704C24.2871 19.4654 23.7757 14.7179 21.5128 13.2779C19.7885 12.1814 18.2842 12.6224 17.3799 13.3274C17.0099 13.6154 16.8242 13.7594 16.7156 13.7594C16.6071 13.7594 16.4213 13.6154 16.0513 13.3274Z"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
    </svg>
    @if ($quantity > 0)
        <div
            id="cart-count-badge"
            class="bg-amarillo text-azul-mist absolute top-2 right-1 flex h-4 w-4 items-center justify-center rounded-full text-xs font-semibold"
            {{-- wire:model="quantity" --}}
        >
            {{ $quantity }}
        </div>
    @endif
</a>
