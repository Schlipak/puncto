<?php

namespace Puncto\Utils;

use Puncto\PunctoObject;

abstract class Kolor extends PunctoObject
{
    const MODES = [
        'normal' => 0,
        'bold' => 1,
        'underline' => 4,
        'blink' => 5,
        'reverse' => 7,
        'invisible' => 8,
    ];

    const FOREGROUND = [
        'black' => 30,
        'red' => 31,
        'green' => 32,
        'yellow' => 33,
        'blue' => 34,
        'magenta' => 35,
        'cyan' => 36,
        'white' => 37,
    ];

    const BACKGROUND = [
        'black' => 40,
        'red' => 41,
        'green' => 42,
        'yellow' => 44,
        'blue' => 44,
        'magenta' => 45,
        'cyan' => 46,
        'white' => 47,
    ];

    const TEMPLATE = "\033[%sm";
    const RESET = "\033[0m";

    public static function color($message, $foreground, $mode = 'normal', $background = null)
    {
        $parts = [
            self::MODES[$mode],
            self::FOREGROUND[$foreground],
        ];

        if (!is_null($background)) {
            $parts[] = self::BACKGROUND[$background];
        }

        $codes = implode(';', $parts);

        return sprintf(self::TEMPLATE, $codes) . $message . self::RESET;
    }
}
