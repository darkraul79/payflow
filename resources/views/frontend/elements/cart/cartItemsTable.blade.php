<div class="cartTable">
    <table>
        <thead>
            <tr>
                <th class="">Producto</th>
                <th class="text-center">Precio</th>
                <th class="text-center">Cantidad</th>
                <th class="text-left" colspan="2">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @if (count($items))
                @foreach ($items as $idItem => $item)
                    <tr>
                        <td>
                            <div
                                class="text-azul-sea inline-flex items-center gap-4"
                            >
                                <div class="max-w-[100px]" wire:ignore>
                                    @if (isset($item['image']))
                                        <img
                                            src="{{ $item['image'] }}"
                                            alt="{{ $item['name'] }}"
                                            class="w-full rounded-md object-cover"
                                        />
                                    @else
                                        <div class="no-img">
                                            <x-heroicon-s-photo />
                                        </div>
                                    @endif
                                </div>
                                {{ $item['name'] }}
                            </div>
                        </td>
                        <td class="text-center">
                            <div
                                class="flex h-full flex-row-reverse flex-wrap items-baseline"
                            >
                                {!! $item['price_formated'] !!}
                            </div>
                        </td>
                        <td class="text-center">
                            <livewire:quantity-buttons
                                size="small"
                                :product="\App\Models\Product::find($idItem)"
                                :quantity="$item['quantity']"
                                wire:key="product-quantity-{{ $idItem }}"
                            />
                        </td>
                        <td class="font-semibold">
                            {{ $item['subtotal_formated'] }}
                        </td>
                        <td class="p-0">
                            <button
                                class="cursor-pointer"
                                wire:click="removeItem({{ $idItem }})"
                            >
                                <img
                                    src="{{ asset('images/icons/close.svg') }}"
                                    alt="Eliminar producto"
                                    class="me-3 h-5 w-5"
                                />
                            </button>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="5" class="placeholder text-center">
                        <p>No hay productos en el carrito</p>
                    </td>
                </tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="">
                    <div class="flex items-center justify-between">
                        <a
                            href="/tienda-solidaria"
                            class="btn btn-primary-outline block"
                        >
                            Regresar a tienda
                        </a>

                        <a
                            href="#"
                            wire:click.prevent="clearCart"
                            class="btn btn-primary block cursor-pointer"
                        >
                            Vaciar carrito
                        </a>
                    </div>
                </td>
            </tr>
        </tfoot>
    </table>
</div>
