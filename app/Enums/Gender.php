<?php

namespace App\Enums;

use Konekt\Enum\Enum;

class Gender extends Enum
{
    const __DEFAULT = self::OTHER;

    const MAN = 'man';
    const WOMAN = 'woman';
    const OTHER = 'other';

    protected static $labels = [];

    protected static function boot()
    {
        static::$labels = [
            self::MAN => __('user.gender.man'),
            self::WOMAN => __('user.gender.woman'),
            self::OTHER => __('user.gender.other'),
        ];
    }
}
