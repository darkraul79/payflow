<div class="col-3">
    <label for="{{ $prefix }}name">Nombre</label>
    <x-input
        placeholder="Nombre"
        type="text"
        name="{{$prefix}}name"
        id="{{$prefix}}name"
        wire:model="{{$prefix}}name"
    />
    <x-error class="form-error" field="{{$prefix}}name" class="form-error" />
</div>
<div class="col-3">
    <label for="{{ $prefix }}last_name">Apellidos</label>
    <x-input
        placeholder="Apellidos"
        type="text"
        name="{{$prefix}}last_name"
        id="{{$prefix}}last_name"
        wire:model="{{$prefix}}last_name"
    />
    <x-error class="form-error" field="{{$prefix}}last_name" />
</div>
<div class="col-4">
    <label for="{{ $prefix }}company">
        Nombre de empresa
        <span>(opcional)</span>
    </label>
    <x-input
        placeholder="Empresa"
        type="text"
        name="{{$prefix}}company"
        id="{{$prefix}}company"
        wire:model="{{$prefix}}company"
    />
    <x-error class="form-error" field="{{$prefix}}company" />
</div>
<div class="col-2">
    <label for="{{ $prefix }}nif">NIF/CIF/NIE</label>
    <x-input
        placeholder="NIF/CIF/NIE"
        type="text"
        name="{{$prefix}}nif"
        id="{{$prefix}}nif"
        wire:model="{{$prefix}}nif"
    />
    <x-error class="form-error" field="{{$prefix}}nif" />
</div>
<div class="col-6">
    <label for="{{ $prefix }}address">Dirección</label>
    <x-input
        placeholder="Dirección"
        type="text"
        name="{{$prefix}}address"
        id="{{$prefix}}address"
        wire:model="{{$prefix}}address"
    />
    <x-error class="form-error" field="{{$prefix}}address" />
</div>

<div class="col-2">
    <label for="{{ $prefix }}cp">C.P.</label>
    <x-input
        placeholder="Código Postal"
        type="text"
        name="{{$prefix}}cp"
        id="{{$prefix}}cp"
        wire:model="{{$prefix}}cp"
    />
    <x-error class="form-error" field="{{$prefix}}cp" />
</div>

<div class="col-2">
    <label for="{{ $prefix }}province">Provincia</label>
    <flux:select
        wire:model="{{ $prefix }}province"
        placeholder="Provincia"
        name="{{ $prefix }}province"
        id="{{ $prefix }}province"
    >
        @foreach (getProvincias() as $province)
            <flux:select.option value="{{ $province }}">
                {{ $province }}
            </flux:select.option>
        @endforeach
    </flux:select>

    <x-error class="form-error" field="{{$prefix}}province" />
</div>

<div class="col-2">
    <label for="{{ $prefix }}city">Población</label>

    <x-input
        placeholder="Población"
        type="text"
        name="{{$prefix}}city"
        id="{{$prefix}}city"
        wire:model="{{$prefix}}city"
    />
    <x-error class="form-error" field="{{$prefix}}city" />
</div>

<div class="col-3">
    <label for="{{ $prefix }}email">Correo electrónico</label>
    <x-input
        placeholder="email"
        type="email"
        name="{{$prefix}}email"
        id="{{$prefix}}email"
        wire:model="{{$prefix}}email"
    />
    <x-error class="form-error" field="{{$prefix}}email" />
</div>

<div class="col-3">
    <label for="{{ $prefix }}phone">
        Teléfono
        <span>(opcional)</span>
    </label>
    <x-input
        placeholder="Teléfono"
        type="tel"
        name="{{$prefix}}phone"
        id="{{$prefix}}phone"
        wire:model="{{$prefix}}phone"
    />
    <x-error class="form-error" field="{{$prefix}}phone" />
</div>
