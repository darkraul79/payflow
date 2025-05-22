<form action="" class="order-form w-full">
    <div class="grid w-full grid-cols-1 gap-5 lg:mb-20 lg:grid-cols-6">
        <div class="col-span-6 lg:col-span-2">
            <label for="name">Nombre</label>
            <x-input
                placeholder="Nombre"
                type="text"
                name="name"
                id="name"
                wire:model="name"
                required
            />
            <x-error field="name" />
        </div>
        <div class="col-span-6 lg:col-span-2">
            <label for="last_name">Apellidos</label>
            <x-input
                placeholder="Apellidos"
                type="text"
                name="last_name"
                id="last_name"
                wire:model="last_name"
                required
            />
            <x-error field="last_name" />
        </div>
        <div class="col-span-6 lg:col-span-2">
            <label for="company">
                Nombre de empresa
                <span>(opcional)</span>
            </label>
            <x-input
                placeholder="Nombre de empresa"
                type="text"
                name="company"
                id="company"
                wire:model="company"
            />
            <x-error field="company" />
        </div>
        <div class="col-span-6 lg:col-span-5">
            <label for="address">Dirección</label>
            <x-input
                placeholder=""
                type="text"
                name="address"
                id="address"
                wire:model="address"
                required
            />
            <x-error field="address" />
        </div>

        <div class="col-span-6 lg:col-span-1">
            <label for="cp">C.P.</label>
            <x-input
                placeholder=""
                type="text"
                name="cp"
                id="cp"
                wire:model="cp"
                required
            />
            <x-error field="cp" />
        </div>
        <div class="col-span-6 lg:col-span-2">
            <label for="city">Población</label>
            <x-input
                placeholder=""
                type="text"
                name="city"
                id="city"
                wire:model="city"
                required
            />
            <x-error field="city" />
        </div>

        <div class="col-span-6 lg:col-span-2">
            <label for="province">Provincia</label>
            <x-input
                placeholder=""
                type="text"
                name="province"
                id="province"
                wire:model="province"
                required
            />
            <x-error field="province" />
        </div>
        <div class="col-span-6 hidden lg:col-span-2 lg:block"></div>

        <div class="col-span-6 lg:col-span-3">
            <label for="email">Correo electrónico</label>
            <x-input
                placeholder=""
                type="email"
                name="email"
                id="email"
                wire:model="email"
                required
            />
            <x-error field="email" />
        </div>

        <div class="col-span-6 lg:col-span-3">
            <label for="phone">
                Teléfono
                <span>(opcional)</span>
            </label>
            <x-input
                placeholder=""
                type="tel"
                name="phone"
                id="phone"
                wire:model="phone"
                required
            />
            <x-error field="phone" />
        </div>

        <div class="col-span-6 lg:col-span-3">
            <x-checkbox
                name="addSendAddress"
                id="addSendAddress"
                wire:model="addSendAddress"
                required
                :checked="false"
            />
            <label for="phone">
                Enviar a otra dirección
                <span>(opcional)</span>
            </label>
        </div>
    </div>
</form>
