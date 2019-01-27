<?php

namespace Puncto;

use \RecursiveDirectoryIterator;
use \RecursiveIteratorIterator;

abstract class Autoloader extends PunctoObject
{
    private static function appToNamespace($app)
    {
        $clean = StringHelper::toCleanPath($app);
        $clean = StringHelper::toClassCase($clean);

        return $clean;
    }

    public static function register($base, $app = 'app')
    {
        define('__ROOT__', StringHelper::toCleanPath($base, false));
        define('__APP__', StringHelper::toCleanPath($app));
        define('__APPNAMESPACE__', self::appToNamespace($app));

        spl_autoload_register(function ($fqcn) {
            $segments = explode('\\', $fqcn);

            $namespace = $segments[0];
            $klass = array_values(array_slice($segments, -1))[0];

            if ($namespace === __APPNAMESPACE__) {
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
