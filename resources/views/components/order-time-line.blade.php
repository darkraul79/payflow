<div {{ $attributes->class(['timeline timeline-one-side']) }}>
    @foreach ($pedido->statesWithStateInitial() as $estado)
        <div class="timeline-block {{ $estado->colorEstado() }} mb-3">
            <span class="timeline-step">
                <i class="bullet"></i>
            </span>

            <div class="timeline-content mb-2 flex flex-col">
                <div class="flex items-center gap-2">
                    <div class="texto mt-0 mb-0 flex items-center gap-x-1">
                        @svg($estado->icono(), 'h-4 w-4')
                        <span class="text-sm font-semibold">
                            {{ $estado->name }}
                        </span>
                    </div>
                    <i class="block text-xs text-gray-400">
                        @if (in_array(Route::currentRouteName(), ['dashboard.pedidos.editarEstado']))
                            {{ $estado->created_at }}
                        @else
                            {{ $estado->fechaHumanos() }}
                        @endif
                    </i>
                </div>

                <div class="font-base relative w-full">
                    @if (auth()->user() && $estado->info)
                        @if ($estado->infoIsJson())
                            <div
                                class="bg-primary/10 text-secondary my-2 flex w-full flex-col p-2 text-xs italic"
                                x-data="{ openInfo: false }"
                            >
                                <x-bi-plus-circle
                                    class="h-4 w-4 cursor-pointer"
                                    @click="openInfo = !openInfo"
                                >
                                    Detalle
                                </x-bi-plus-circle>

                                <div
                                    class="mx-auto my-2 rounded-md border border-gray-200 bg-white p-3 text-start text-xs"
                                    x-show="openInfo"
                                    @click.away="openInfo = false"
                                    x-cloak
                                >
                                    <pre
                                        class="text-start text-xs text-gray-500"
                                    >
                                            {{ print_r(json_decode($estado->info, true)) }}
                                        </pre>
                                </div>
                            </div>
                        @else
                            <div
                                class="bg-primary/10 text-secondary flex w-full p-2 text-xs italic"
                            >
                                <x-bi-info-circle class="text-primary me-2" />
                                {{ $estado->info }}
                            </div>
                        @endif
                    @endif

                    @if ($estado->message && $estado->message != '')
                        <div
                            class="items-top bg-primary/10 text-secondary myº-2 flex w-full gap-2 rounded-lg p-2 text-xs italic"
                        >
                            <x-heroicon-o-chat-bubble-left-ellipsis
                                class="text-primary h-5 w-5"
                            />
                            {{ $estado->message }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
