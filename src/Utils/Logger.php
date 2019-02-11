<?php

namespace Puncto\Utils;

use Puncto\Utils\Kolor;

abstract class Logger
{
    const LEVELS = [
        'NONE' => 0,
        'FATAL' => 1,
        'ERROR' => 2,
        'WARN' => 3,
        'INFO' => 4,
        'DEBUG' => 5,
    ];

    private static function getLevel()
    {
        $levelName = 'INFO';

        if (isset($_ENV['PUNCTO_VERBOSITY'])) {
            $levelName = strtoupper($_ENV['PUNCTO_VERBOSITY']);
        }

        if (isset(self::LEVELS[$levelName])) {
            return ['name' => $levelName, 'flag' => self::LEVELS[$levelName]];
        }

        return ['name' => 'INFO', 'flag' => 4];
    }

    private static function canLog($level)
    {
        $currentLevel = self::getLevel();
        $requiredFlag = self::LEVELS[$level];

        return $requiredFlag <= $currentLevel['flag'];
    }

    public static function debug($message)
    {
        if (!self::canLog('DEBUG')) {
            return;
        }

        self::printEachLine("[DEBUG] $message", 'black', 'bold');
    }

    public static function log($message, $color = null, ...$args)
    {
        if (!self::canLog('INFO')) {
            return;
        }

        self::printEachLine($message, $color, ...$args);
    }

    public static function error($message)
    {
        if (!self::canLog('ERROR')) {
            return;
        }

        self::printEachLine($message, 'red', 'bold');
    }

    public static function warn($message)
    {
        if (!self::canLog('WARN')) {
            return;
        }

        self::printEachLine($message, 'yellow');
    }

    private static function printEachLine($message, $color = null, ...$args)
    {
        $separator = "\r\n";
        $line = strtok($message, $separator);

        while ($line !== false) {
            if (!is_null($color)) {
                $line = Kolor::color($line, $color, ...$args);
            }

            error_log($line);

            $line = strtok($separator);
        }
    }
}
