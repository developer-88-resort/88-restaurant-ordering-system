<?php

namespace App\Support;

class AvailableLocales
{
    public const CODES = ['en', 'ko'];

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            'en' => 'English',
            'ko' => '한국어',
        ];
    }
}
