@php
    use App\Helpers\RedsysAPI;
    use App\Models\Donation;
@endphp

<div class="card mb-6 h-auto w-full p-6">
    @include('livewire.donation.step1')

    @include('livewire.donation.step2')
    @include('livewire.donation.step3')

    @include('components.formRedSys')

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
