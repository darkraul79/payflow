@php
    use App\Helpers\RedsysAPI;
@endphp

<form
    action="{{ RedsysAPI::getRedsysUrl() }}"
    id="redsys_form"
    method="post"
    name="redsys_form"
    class="hidden"
>
    <input
        name="Ds_MerchantParameters"
        id="Ds_MerchantParameters"
        readonly
        type="text"
        wire:model.live="MerchantParameters"
    />
    <input
        name="Ds_Signature"
        id="Ds_Signature"
        type="text"
        readonly
        wire:model.live="MerchantSignature"
    />
    <input
        name="Ds_SignatureVersion"
        id="Ds_SignatureVersion"
        type="text"
        readonly
        wire:model.live="SignatureVersion"
    />
    <button type="submit" id="redsys_submit_donacion">Enviar</button>
</form>
@script
    <script>
        $wire.on('submit-redsys-form', () => {
            Livewire.hook('morph.updated', ({ el, component }) => {
                if (el.id === 'redsys_form') {
                    el.submit();
                }
            });
        });
    </script>
@endscript
