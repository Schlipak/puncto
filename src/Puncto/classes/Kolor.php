<?php

namespace Puncto;

abstract class Kolor
{
    const modes = [
        'normal' => 0,
        'bold' => 1,
        'underline' => 4,
        'blink' => 5,
        'reverse' => 7,
        'invisible' => 8,
    ];

    const fg = [
        'black' => 30,
        'red' => 31,
        'green' => 32,
        'yellow' => 33,
        'blue' => 34,
        'magenta' => 35,
        'cyan' => 36,
        'white' => 37,
    ];

    const bg = [
        'black' => 40,
        'red' => 41,
        'green' => 42,
        'yellow' => 44,
        'blue' => 44,
        'magenta' => 45,
        'cyan' => 46,
        'white' => 47,
    ];

    const template = "\033[%sm";
    const reset = "\033[0m";

    static function color($message, $fg, $mode = 'normal', $bg = null)
    {
        $parts = [
            self::modes[$mode],
            self::fg[$fg],
        ];

        if (!is_null($bg)) {
            $parts[] = self::bg[$bg];
        }

        $codes = implode(';', $parts);

        return sprintf(self::template, $codes) . $message . self::reset;
    }
}
