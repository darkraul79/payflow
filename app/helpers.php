<?php

use Illuminate\Support\Number;

if (!function_exists('getUrlDownloads')) {
    function getUrlDownloads(string $file): string
    {
        return "/storage/$file";
    }

}

if (!function_exists('hasQuotes')) {
    /**
     * Returns true if the content has quotes
     */
    function hasQuotes(string $type): bool
    {
        return in_array($type, ['Page', 'Product']);
    }

}

if (!function_exists('hasTitleSection')) {
    /**
     * Returns true if the content has title
     */
    function hasTitleSection(string $type): bool
    {
        return in_array($type, ['Page', 'Product']);
    }

}

if (!function_exists('hasActivityTitle')) {
    /**
     * Returns true if the content has title
     */
    function hasActivityTitle(string $type): bool
    {
        return in_array($type, ['Activity', 'News', 'Proyect']);
    }

}

if (!function_exists('getTypeContent')) {
    function getTypeContent($class)
    {
        return class_basename($class);
    }
}
if (!function_exists('convertPrice')) {
    function convertPrice($price): string
    {
        return Number::currency($price, 'EUR', locale: 'es-ES', precision: 2);
    }
}
if (!function_exists('convertPriceNumber')) {
    function convertPriceNumber($price): float
    {
        if (is_string($price)) {
            // Reemplaza la coma por un punto para convertirlo a un formato numérico válido
            $price = str_replace(',', '.', $price);
        }

        // Asegúrate de que sea un número flotante
        return round((float)$price, 2);
    }
}


if (!function_exists('calculoImpuestos')) {
    function calculoImpuestos(float $subtotal): float
    {
        $impuesto = 1.21;

        return round($subtotal - ($subtotal / $impuesto), 2);
    }

}
