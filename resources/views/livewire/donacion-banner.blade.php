<div
    class="card top-0 right-0 mb-6 h-auto w-full p-6 md:sticky md:max-w-[400px]"
>
    <h5 class="text- px-6 text-center text-xl text-pretty">
        !Dona a la fundación ELENA TERTRE!
    </h5>

    <div class="my-6 flex w-full">
        <x-radiobutton-donacion
            name="socio"
            :default="0"
            :options="[
                [
                    'text' => 'Donación única',
                    'value' => false,
                ],
                [
                    'text' => 'Hazte Socio',
                    'value' => true,
                ],
            ]"
        />
    </div>
    <div class="my-6 flex w-full">
        <x-radiobutton-donacion
            name="cantidad"
            :default="1"
            :options="[
                [
                    'text' => '10',
                    'value' => 10,
                ],
                [
                    'text' => '50',
                    'value' => 50,
                ],
                [
                    'text' => '100',
                    'value' => 100,
                ],
            ]"
        />
    </div>

    <div>
        <div
            class="card outline-azul-gray text-azul-mist has-[input:focus-within]:outline-azul-mist flex items-center p-2 shadow-none has-[input:focus-within]:outline-2"
        >
            <input
                type="text"
                name="price"
                id="price"
                class="w-full border-0 p-1 font-semibold shadow-none focus:border-0! focus:ring-0 focus:outline-0!"
            />
            <div
                class="text-azul-mist shrink-0 px-2 text-base font-semibold select-none"
            >
                €
            </div>
        </div>
        <small class="text-azul-gray block w-full text-[11px]">
            O si lo prefieres, puedes escribir otra cantidad
        </small>
    </div>

    <div class="my-6">
        <button
            class="btn bg-amarillo text-azul-mist! hover:bg-amarillo/70 w-full cursor-pointer font-semibold"
        >
            Hacer una donacion
        </button>
    </div>

    <div
        class="text-azul-gray font-teacher before:border-azul-cobalt after:border-azul-cobalt relative flex items-center justify-between text-center text-[11px] font-thin tracking-widest before:flex-1 before:border-t before:content-[''] after:block after:flex-1 after:border-t after:content-['']"
    >
        <span class="z-10 mx-auto flex items-center px-1 leading-5">
            DONA RÁPIDO Y SIN COMPLICACIONES CON BIZUM
        </span>
    </div>
    <img
        src="{{ asset('images/icons/ssl-cards.png') }}"
        alt=""
        class="mx-auto my-6"
    />
</div>
