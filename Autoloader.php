<?php

namespace Puncto;

use \RecursiveDirectoryIterator;
use \RecursiveIteratorIterator;

abstract class Autoloader
{
    private static function cleanPath($path, $trimStart = false)
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

    public static function register($base, $app = 'app')
    {
        define('__ROOT__', self::cleanPath($base, false));
        define('__APP__', self::cleanPath($app));

        spl_autoload_register(function ($fqcn) {
            $segments = explode('\\', $fqcn);

            $namespace = $segments[0];
            $klass = array_values(array_slice($segments, -1))[0];

            if ($namespace === 'Puncto') {
                require __DIR__ . DIRECTORY_SEPARATOR . "classes" . DIRECTORY_SEPARATOR . "$klass.php";
            } else {
                $appRoot = __ROOT__ . DIRECTORY_SEPARATOR . __APP__;
                $filename = "$klass.php";

                $directory = new RecursiveDirectoryIterator($appRoot, RecursiveDirectoryIterator::SKIP_DOTS);
                $iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::LEAVES_ONLY);

                foreach ($iterator as $file) {
                    if (strtolower($file->getFilename()) === strtolower($filename)) {
                        if ($file->isReadable()) {
                            require $file->getPathname();
                        }
                        break;
                    }
                }
            }
        });
    }
}
