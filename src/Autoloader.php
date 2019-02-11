<?php

namespace Puncto;

use Puncto\Exceptions\FatalException;
use Puncto\Utils\Logger;
use Puncto\Utils\StringHelper;
use \RecursiveDirectoryIterator;
use \RecursiveIteratorIterator;

abstract class Autoloader extends PunctoObject
{
    private static function validateAppNameFormat($appName)
    {
        return preg_match("/^[a-zA-Z][a-zA-Z_\-]*$/", $appName);
    }

    private static function appNameToNamespace($appName)
    {
        $clean = StringHelper::toCleanPath($appName);
        $clean = StringHelper::toClassCase($clean);

        return $clean;
    }

    public static function register($base, $appName = 'app')
    {
        if (!self::validateAppNameFormat($appName)) {
            throw new FatalException("Invalid application name format");
        }

        $rootDirectory = StringHelper::toCleanPath($base, false);
        $appNamespace = self::appNameToNamespace($appName);

        if (defined('__ROOT__') &&
            defined('__APP__') &&
            defined('__APPNAMESPACE__') &&
            defined('__PLATFORM_ROOT__')) {
            return;
        }

        define('__ROOT__', $rootDirectory);
        define('__APP__', $appName);
        define('__APPNAMESPACE__', $appNamespace);
        define('__PLATFORM_ROOT__', dirname(__DIR__));

        Logger::debug("Registered application '$appName' with namespace '$appNamespace'");

        spl_autoload_register(function ($fqcn) {
            $segments = explode('\\', $fqcn);

            $namespace = $segments[0];
            $klass = array_values(array_slice($segments, -1))[0];

            if ($namespace === __APPNAMESPACE__) {
                $appRoot = __ROOT__ . DIRECTORY_SEPARATOR . 'app';
                $filename = "$klass.php";

                $directory = new RecursiveDirectoryIterator($appRoot, RecursiveDirectoryIterator::SKIP_DOTS);
                $iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::LEAVES_ONLY);

                foreach ($iterator as $file) {
                    if (strtolower($file->getFilename()) === strtolower($filename)) {
                        if ($file->isReadable()) {
                            require_once $file->getPathname();
                        }
                        break;
                    }
                }
            }
        });
    }
}
