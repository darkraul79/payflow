<?php
if (!function_exists('getUrlDownloads')) {
    function getUrlDownloads(string $file): string
    {
        return "/storage/$file";
    }
}
