<?php

use App\Models\Donation;
use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

if (! function_exists('getUrlDownloads')) {
    function getUrlDownloads(string $file): string
    {
        return "/storage/$file";
    }

}

if (! function_exists('hasQuotes')) {
    /**
     * Returns true if the content has quotes
     */
    function hasQuotes(string $type): bool
    {
        return in_array($type, ['Page', 'Product']);
    }

}

if (! function_exists('hasTitleSection')) {
    /**
     * Returns true if the content has a title
     */
    function hasTitleSection(string $type): bool
    {
        return in_array($type, ['Page', 'Product']);
    }

}

if (! function_exists('hasActivityTitle')) {
    /**
     * Returns true if the content has a title
     */
    function hasActivityTitle(string $type): bool
    {
        return in_array($type, ['Activity', 'News', 'Proyect']);
    }

}

if (! function_exists('getTypeContent')) {
    function getTypeContent($class): string
    {
        return class_basename($class);
    }
}
if (! function_exists('convertPrice')) {
    function convertPrice($price, $items_number = 1): string
    {
        $price = convertPriceNumber($price);
        if (! $items_number) {
            $price = 0;
        }

        return Number::currency($price, 'EUR', locale: 'es-ES', precision: 2);
    }
}
if (! function_exists('convertPriceNumber')) {
    function convertPriceNumber($price): float
    {
        if (is_string($price)) {
            // Reemplaza la coma por un punto para convertirlo a un formato numérico válido
            $price = str_replace(',', '.', $price);
        }

        // Asegúrate de que sea un número flotante
        return round((float) $price, 2);
    }
}
if (! function_exists('convertNumberToRedSys')) {
    function convertNumberToRedSys($price): int
    {
        // Reemplaza la coma por un punto para convertirlo a un formato numérico válido
        $price = str_replace(',', '.', $price);

        // Asegúrate de que sea un número flotante con dos decimales
        $price = number_format((float) $price, 2, '.', '');

        // Elimina el punto decimal y devuelve el número como entero
        return (int) str_replace('.', '', $price);
    }
}

if (! function_exists('calculoImpuestos')) {
    function calculoImpuestos(float $subtotal): float
    {
        $impuesto = 1.21;

        return round($subtotal - ($subtotal / $impuesto), 2);
    }

}

if (! function_exists('getProvincias')) {
    function getProvincias(): array
    {
        return [
            'Alava', 'Albacete', 'Alicante', 'Almería', 'Asturias', 'Avila', 'Badajoz', 'Barcelona', 'Burgos',
            'Cáceres',
            'Cádiz', 'Cantabria', 'Castellón', 'Ciudad Real', 'Córdoba', 'La Coruña', 'Cuenca', 'Gerona', 'Granada',
            'Guadalajara',
            'Guipúzcoa', 'Huelva', 'Huesca', 'Islas Baleares', 'Jaén', 'León', 'Lérida', 'Lugo', 'Madrid', 'Málaga',
            'Murcia', 'Navarra',
            'Orense', 'Palencia', 'Las Palmas', 'Pontevedra', 'La Rioja', 'Salamanca', 'Segovia', 'Sevilla', 'Soria',
            'Tarragona',
            'Santa Cruz de Tenerife', 'Teruel', 'Toledo', 'Valencia', 'Valladolid', 'Vizcaya', 'Zamora', 'Zaragoza',
        ];
    }

}

if (! function_exists('generateOrderNumber')) {

    function generateOrderNumber(): string
    {
        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        do {
            $orderNumber = Str::upper(Str::random(8));
        } while (Order::where('number', $orderNumber)->exists());

        return $orderNumber;
    }
}

/**
 * Genera número de pedido, 4 primeros caracteres numéricos y 8 caracteres alfanuméricos
 *
 * @return string
 */
if (! function_exists('estado_redsys')) {
    function estado_redsys($codigo): string
    {
        $codigoOriginal = $codigo;
        $codigo = intval($codigo);

        return match ($codigo) {
            101 => 'Tarjeta Caducada',
            102 => 'Tarjeta en excepción transitoria o bajo sospecha de fraude',
            106 => 'Intentos de PIN excedidos',
            125 => 'Tarjeta no efectiva',
            129 => 'Código de seguridad (CVV2/CVC2) incorrecto',
            9915 => 'A petición del usuario se ha cancelado el pago',
            9093 => 'Tarjeta no existente',
            9078 => 'Tipo de operación no permitida para esa tarjeta',
            default => 'Error RedSys - '.$codigoOriginal,
        };
    }
}
if (! function_exists('convertPriceFromRedsys')) {
    function convertPriceFromRedsys($price): float
    {
        // Convertir el precio de redsys a float
        return (float) $price / 100 ?? 0.0;

    }
}
if (! function_exists('generateDonationNumber')) {
    function generateDonationNumber(): string
    {

        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        do {
            $orderNumber = Str::upper(Str::random(8));
        } while (Donation::where('number', $orderNumber)->exists());

        return $orderNumber;
    }
}

if (! function_exists('generatePaymentNumber')) {
    function generatePaymentNumber(Model $model): string
    {
        $suffix = '';
        if ($model->payments()->count()) {
            $suffix = '_'.($model->payments()->count() + 1);
        }

        return $model->number.$suffix; // Incrementar en 1
    }
}

if (! function_exists('getTitlePageSEO')) {
    /**
     * Returns the SEO title for a page
     */
    function getTitlePageSEO($content): string
    {
        if (! empty($content->title)) {
            $titulo_seo = $content->title.' - '.config('app.name');
        } else {
            $titulo_seo = config('app.name');
        }

        return $titulo_seo;
    }

}
