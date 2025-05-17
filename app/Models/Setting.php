<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'property',
        'value',
    ];

    public static function getFormated($property): ?string
    {
        $data = self::get($property);
        if (is_null($data)) {
            return null;
        }

        // Capitalize the first letter of each word
        return Str::title($property) . ': ' . $data;
    }

    public static function get($property)
    {
        return self::where('property', $property)->first()->value ?? null;
    }

    public static function getRss(): array
    {
        $array = ['facebook', 'x', 'instagram', 'youtube'];
        $data = [];
        foreach ($array as $item) {
            $itemData = self::get($item);
            if (is_null($itemData)) {
                continue;
            }
            $data[$item] = $itemData;
        }

        return $data;
    }
}
