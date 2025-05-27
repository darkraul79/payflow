<form
    action="{{ $data['Url'] }}"
    id="redsys_form"
    method="post"
    name="redsys_form"
    style="display: none"
>
    <input
        name="Ds_MerchantParameters"
        id="Ds_MerchantParameters"
        type="hidden"
        value="{{ $data['Ds_MerchantParameters'] }}"
    />
    <input
        name="Ds_Signature"
        id="Ds_Signature"
        type="hidden"
        value="{{ $data['Ds_Signature'] }}"
    />
    <input
        name="Ds_SignatureVersion"
        id="Ds_SignatureVersion"
        type="hidden"
        value="{{ $data['Ds_SignatureVersion'] }}"
    />
</form>
<script>
    const form = document.getElementById('redsys_form');
    form.submit();
</script>
