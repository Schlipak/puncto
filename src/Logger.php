<?php

namespace Puncto;

use Puncto\Kolor;

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

        error_log(Kolor::color($message, 'white', 'reverse'));
    }

    public static function log($message, $color = null, ...$args)
    {
        if (!self::canLog('INFO')) {
            return;
        }

        if ($color) {
            $message = Kolor::color($message, $color, ...$args);
        }

        error_log($message);
    }

    public static function error($message)
    {
        if (!self::canLog('ERROR')) {
            return;
        }

        error_log(Kolor::color($message, 'red', 'bold'));
    }

    public static function warn($message)
    {
        if (!self::canLog('WARN')) {
            return;
        }

        error_log(Kolor::color($message, 'yellow'));
    }
}
