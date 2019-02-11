<?php

namespace Puncto\Platform;

use Puncto\Exceptions\GenericException;
use Puncto\Utils\Logger;
use Puncto\PunctoObject;

class StaticHandler extends PunctoObject
{
    private $route;
    private $serveBuiltin;

    public function __construct($route, $serveBuiltin)
    {
        $this->route = $route;
        $this->serveBuiltin = $serveBuiltin;
    }

    public static function getMimeType($path)
    {
        $mime = mime_content_type($path);
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        switch ($ext) {
            case 'html':
                return 'text/html';
            case 'css':
                return 'text/css';
            case 'js':
                return 'application/javascript';
            default:
                return $mime;
        }
    }

    public function render($request, $env, $params)
    {

        $start = round(microtime(true) * 1000);

        $base = explode('/*', $this->route)[0];
        $path = 'app' . $base . DIRECTORY_SEPARATOR . $params['*'];

        if ($this->serveBuiltin) {
            $base = explode('/PUNCTO_DEV', $base)[1];
            $dir = explode('/src', __DIR__)[0];

            $path = $dir . $base . DIRECTORY_SEPARATOR . $params['*'];
        } else {
            $path = __ROOT__ . "/$path";
        }

        if (file_exists($path)) {
            $mtime = filemtime($path);
            $gmtMtime = gmdate('D, d M Y H:i:s', $mtime) . ' GMT';
            $etag = sprintf('%08x-%08x', crc32($path), $mtime);

            if ($request->httpIfModifiedSince || $request->httpIfNoneMatch) {
                $ifMod = $request->httpIfModifiedSince;
                $ifNone = $request->httpIfNoneMatch;

                if ($ifMod == $gmtMtime || str_replace('"', '', stripslashes($ifNone)) == $etag) {
                    Logger::log("  Completed 304 Not Modified", 'magenta');

                    header("{$request->serverProtocol} 304 Not Modified");
                    return;
                }
            }

            $size = filesize($path);
            $mime = self::getMimeType($path);

            header("{$request->serverProtocol} 200 OK");
            header("Content-Type: $mime");
            header("Content-Length: $size");
            header("ETag: \"$etag\"");
            header("Last-Modified: $gmtMtime");
            header('Cache-Control: max-age=' . (60 * 60 * 24));

            $output = file_get_contents($path);

            $end = round(microtime(true) * 1000);
            $dt = $end - $start;
            Logger::log("  Processed in ${dt}ms", 'green');

            return $output;
        }

        throw new GenericException('Not Found');
    }
}
