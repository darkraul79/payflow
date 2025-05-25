<div class="flex basis-1/2 flex-col">
    <span class="text-md mb-2 font-semibold">Datos de envío</span>
    <span class="mb-2 text-sm font-semibold">
        {{ $record->full_name }}
    </span>
    <span class="mb-2 text-sm font-semibold">
        Dirección:
        <br />
        <span class="ms-2 block font-normal">
            {{ $record->address }}
            <br />
            {{ $record->cp_envio }} - {{ $record->poblacion_envio }}.
            <br />
            {{ $record->provincia_envio }}.
            {{ $record->pais_envio != 'España' ? $record->pais_envio : '' }}
        </span>
    </span>

    <span class="mb-2 text-sm font-semibold">
        Email:
        <br />
        <span class="ms-2 font-normal">
            {{ $record->email_envio }}
        </span>
    </span>
    <span class="text-sm font-semibold">
        Teléfono:
        <br />
        <span class="ms-2 font-normal">
            {{ $record->telefono_envio }}
        </span>
    </span>
</div>
