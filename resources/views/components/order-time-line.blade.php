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
                    <x-info-bullet-collapsible :info="$estado->info" />

                    @if ($estado->message && $estado->message != '')
                        <div
                            class="items-top bg-azul-wave/20 text-secondary my-2 me-10 flex w-4/5 gap-2 rounded-lg p-2 text-xs italic"
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
