<?php

namespace App\Enums;

use Konekt\Enum\Enum;

class ProductAssetType extends Enum
{
    const __DEFAULT = self::IMAGE;

    const IMAGE = 'image';
    const MODEL = '3d-model';
}
