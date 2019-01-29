<?php

namespace Puncto;

use Puncto\Kolor;
use \Error;

class StaticHandler
{
    private $route;
    private $serveBuiltin;

    public function __construct($route, $serveBuiltin)
    {
        $this->route = $route;
        $this->serveBuiltin = $serveBuiltin;
    }

    private static function getMimeType($path)
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
        $path = __APP__ . $base . DIRECTORY_SEPARATOR . $params['*'];

        if ($this->serveBuiltin) {
            $base = explode('/PUNCTO_DEV', $base)[1];
            $dir = explode('/src', __DIR__)[0];

            $path = $dir . $base . DIRECTORY_SEPARATOR . $params['*'];
        }

        if (file_exists($path)) {
            $mtime = filemtime($path);
            $gmtMtime = gmdate('D, d M Y H:i:s', $mtime) . ' GMT';
            $etag = sprintf('%08x-%08x', crc32($path), $mtime);

            if ($request->httpIfModifiedSince || $request->httpIfNoneMatch) {
                $ifMod = $request->httpIfModifiedSince;
                $ifNone = $request->httpIfNoneMatch;

                if ($ifMod == $gmtMtime || str_replace('"', '', stripslashes($ifNone)) == $etag) {
                    error_log(Kolor::color("  Completed 304 Not Modified", 'magenta'));

                    header("{$request->serverProtocol} 304 Not Modified");
                    die();
                }
            }

            $size = filesize($path);
            $mime = self::getMimeType($path);

            session_cache_limiter('none');

            header("Content-Type: $mime");
            header("Content-Length: $size");
            header("ETag: \"$etag\"");
            header("Last-Modified: $gmtMtime");
            header('Cache-Control: max-age=' . (60 * 60 * 24));

            $output = file_get_contents($path);

            $end = round(microtime(true) * 1000);
            $dt = $end - $start;
            error_log(Kolor::color("  Processed in ${dt}ms", 'green'));

            return $output;
        }

        throw new Error('Not Found');
    }
}
