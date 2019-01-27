<?php

namespace Puncto;

abstract class StringHelper extends PunctoObject
{
    public static function toCamelCase($string)
    {
        $result = strtolower($string);

        preg_match_all('/[\s_][a-z]/', $result, $matches);
        foreach ($matches[0] as $match) {
            $replaced = preg_replace("/[\s_]/", '', strtoupper($match));
            $result = str_replace($match, $replaced, $result);
        }

        return $result;
    }

    public static function toClassCase($string)
    {
        return ucfirst(self::toCamelCase($string));
    }

    public static function toCleanPath($path, $trimStart = false)
    {
        $result = rtrim($path, DIRECTORY_SEPARATOR);

        if ($trimStart) {
            $result = ltrim($result, DIRECTORY_SEPARATOR);
        }

        if ($result === '') {
            $result = DIRECTORY_SEPARATOR;
        }

        return $result;
    }
}
