<?php

namespace Puncto;

use Puncto\Inflector;

abstract class StringHelper extends PunctoObject
{
    public static function toCamelCase($string)
    {
        $result = strtolower($string);

        preg_match_all('/[\s\-_][a-z]/', $result, $matches);
        foreach ($matches[0] as $match) {
            $replaced = preg_replace("/[\s\-_]/", '', strtoupper($match));
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

    public static function toSnakeCase($string)
    {
        return implode(
            '_',
            array_map(function ($word) {
                return strtolower(trim($word, " \t\n\r\0\x0B-_"));
            },
                array_filter(
                    preg_split("/(?=[A-Z\s\-_][^A-Z]*)/", $string)
                )
            )
        );
    }

    public static function toURL($string)
    {
        return str_replace('_', '-', self::toSnakeCase($string));
    }

    public static function toSingular($string)
    {
        return Inflector::singularize($string);
    }
}
