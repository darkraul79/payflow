@php
    use App\Helpers\RedsysAPI;
    use App\Models\Donation;
@endphp

<div class="card mb-6 h-auto w-full sm:p-6 p-2">
    @include('livewire.donation.step1')

    @include('livewire.donation.step2')
    @include('livewire.donation.step3')

    @include('components.formRedSys')

    <img
        src="{{ asset('images/icons/ssl-cards.png') }}"
        alt=""
        class="mx-auto my-6 w-full max-w-fit"
    />
    <div>
        <div
            class="text-primary font-teacher before:border-azul-cobalt after:border-azul-cobalt relative flex items-center justify-between text-center text-[11px] font-thin tracking-widest before:flex-1 before:border-t before:content-[''] after:block after:flex-1 after:border-t after:content-['']  mb-4"
        >
            <span class="z-10 mx-auto flex items-center px-2 leading-5 font-medium">
                CONOCE OTRAS FORMAS DE DONAR
            </span>

        </div>
        <div class="bg-azul-sky text-azul-sea text-center text-sm rounded-md p-4 space-y-6 leading-4">
            <div class="text-balance">
                <h3>Transferencia bancaria</h3>
                Haz una transferencia a la cuenta de Fundación Elena Tertre Caixabank.<br />
                <strong class="font-medium">ES90 2100 1446 9202 0075 7642</strong>
            </div>
            <div>
                <h3>Bizum ONG</h3>
                <p>Dona al <strong class="font-medium">código 06701</strong> - Fundación Elena Tertre.</p>
            </div>
            <div>
                <h3>¿Necesitas tu certificado de donación?</h3>
                <p>
                    Escríbenos a <a
                        href="mailto:ayuda@fundacionelenatertre.es?subject=Necesito un certificado de donación"
                        target="_blank" class="underline"
                        title="Escríbenos y te enviamos el certificado de donación">ayuda@fundacionelenatertre.es</a>
                    y te lo enviamos.</p>
            </div>
        </div>
    </div>
</div>
