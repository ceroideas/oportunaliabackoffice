<?php

namespace App\Services;

class FotocasaMapper
{
    const CATEGORY_MAPPING = [
        
        24 => ['type' => 1, 'subtype' => 9],   
        25 => ['type' => 7, 'subtype' => null], 
        26 => ['type' => 8, 'subtype' => 70],   
        27 => ['type' => 12, 'subtype' => null], 
        28 => ['type' => 3, 'subtype' => null],  
        34 => ['type' => 6, 'subtype' => 56],   
        40 => ['type' => 4, 'subtype' => null],  
        42 => ['type' => 6, 'subtype' => 91],   
        'default' => ['type' => 1, 'subtype' => 9] 
    ];

    public static function mapCategory(int $categoryId): array
    {
        return self::CATEGORY_MAPPING[$categoryId] ?? self::CATEGORY_MAPPING['default'];
    }


}