<div class="col-6">
    <label for="{{$suffix.'_'.$prefix}}_name">Nombre</label>
    <x-input
        placeholder="Nombre"
        type="text"
        name="{{$suffix.'_'.$prefix}}_name"
        id="{{$suffix.'_'.$prefix}}_name"
        data-test="{{$suffix.'_'.$prefix}}_name"
        wire:model="{{$prefix}}.name"
    />
    <x-error class="form-error" field="{{$prefix}}.name" class="form-error" />
</div>
<div class="col-3">
    <label for="{{$suffix.'_'.$prefix}}_last_name">Primer Apellido</label>
    <x-input
        placeholder="Primer apellido"
        type="text"
        name="{{$suffix.'_'.$prefix}}_last_name"
        id="{{$suffix.'_'.$prefix}}_last_name"
        wire:model="{{$prefix}}.last_name"
    />
    <x-error class="form-error" field="{{$prefix}}.last_name" />
</div>
<div class="col-3">
    <label for="{{$suffix.'_'.$prefix}}_last_name2">Segundo Apellidos</label>
    <x-input
        placeholder="Segundo apellido"
        type="text"
        name="{{$suffix.'_'.$prefix}}_last_name2"
        id="{{$suffix.'_'.$prefix}}_last_name2"
        wire:model="{{$prefix}}.last_name2"
    />
    <x-error class="form-error" field="{{$prefix}}.last_name2" />
</div>
<div class="col-4">
    <label for="{{$suffix.'_'.$prefix}}_company">
        Nombre de empresa
        <span>(opcional)</span>
    </label>
    <x-input
        placeholder="Empresa"
        type="text"
        name="{{$suffix.'_'.$prefix}}_company"
        id="{{$suffix.'_'.$prefix}}_company"
        wire:model="{{$prefix}}.company"
    />
    <x-error class="form-error" field="{{$prefix}}.company" />
</div>
<div class="col-2">
    <label for="{{$suffix.'_'.$prefix}}_nif">NIF/CIF/NIE</label>
    <x-input
        placeholder="NIF/CIF/NIE"
        type="text"
        name="{{$suffix.'_'.$prefix}}_nif"
        id="{{$suffix.'_'.$prefix}}_nif"
        wire:model="{{$prefix}}.nif"
    />
    <x-error class="form-error" field="{{$prefix}}.nif" />
</div>
<div class="col-6">
    <label for="{{$suffix.'_'.$prefix}}_address">Dirección</label>
    <x-input
        placeholder="Dirección"
        type="text"
        name="{{$suffix.'_'.$prefix}}_address"
        id="{{$suffix.'_'.$prefix}}_address"
        wire:model="{{$prefix}}.address"
    />
    <x-error class="form-error" field="{{$prefix}}.address" />
</div>

<div class="col-2">
    <label for="{{$suffix.'_'.$prefix}}_cp">C.P.</label>
    <x-input
        placeholder="Código Postal"
        type="text"
        name="{{$suffix.'_'.$prefix}}_cp"
        id="{{$suffix.'_'.$prefix}}_cp"
        wire:model="{{$prefix}}.cp"
    />
    <x-error class="form-error" field="{{$prefix}}.cp" />
</div>

<div class="col-2">
    <label for="{{$suffix.'_'.$prefix}}_province">Provincia</label>
    <flux:select
        wire:model="{{ $prefix }}province"
        placeholder="Provincia"
        name="{{ $prefix }}province"
        id="{{$suffix.'_'.$prefix}}_province"
    >
        @foreach (getProvincias() as $province)
            <flux:select.option value="{{ $province }}">
                {{ $province }}
            </flux:select.option>
        @endforeach
    </flux:select>

    <x-error class="form-error" field="{{$prefix}}.province" />
</div>

<div class="col-2">
    <label for="{{$suffix.'_'.$prefix}}_city">Población</label>

    <x-input
        placeholder="Población"
        type="text"
        name="{{$suffix.'_'.$prefix}}_city"
        id="{{$suffix.'_'.$prefix}}_city"
        wire:model="{{$prefix}}.city"
    />
    <x-error class="form-error" field="{{$prefix}}.city" />
</div>

<div class="col-3">
    <label for="{{$suffix.'_'.$prefix}}_email">Correo electrónico</label>
    <x-input
        placeholder="email"
        type="email"
        name="{{$suffix.'_'.$prefix}}_email"
        id="{{$suffix.'_'.$prefix}}_email"
        wire:model="{{$prefix}}.email"
    />
    <x-error class="form-error" field="{{$prefix}}.email" />
</div>

<div class="col-3">
    <label for="{{$suffix.'_'.$prefix}}_phone">
        Teléfono
        <span>(opcional)</span>
    </label>
    <x-input
        placeholder="Teléfono"
        type="tel"
        name="{{$suffix.'_'.$prefix}}_phone"
        id="{{$suffix.'_'.$prefix}}_phone"
        wire:model="{{$prefix}}.phone"
    />
    <x-error class="form-error" field="{{$prefix}}.phone" />
</div>
