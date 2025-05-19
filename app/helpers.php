<?php

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
