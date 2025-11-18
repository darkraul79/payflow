@php
    $actionUrl = $form['form_url'] ?? '';
@endphp

@dump($form['Raw'] ?? [])

<form
    action="{{ $actionUrl }}"
    id="redsys_form_donacion"
    method="post"
    name="redsys_form"
>
    <input
        name="Ds_MerchantParameters"
        id="Ds_MerchantParameters"
        readonly
        type="text"
        value="{{ $form['Ds_MerchantParameters'] }}"
    />
    <input
        name="Ds_Signature"
        id="Ds_Signature"
        type="text"
        readonly
        value="{{ $form['Ds_Signature'] }}"
    />
    <input
        name="Ds_SignatureVersion"
        id="Ds_SignatureVersion"
        type="text"
        value="{{ $form['Ds_SignatureVersion'] }}"
        readonly
    />
    <button type="submit" id="redsys_submit_donacion">Enviar</button>
</form>
