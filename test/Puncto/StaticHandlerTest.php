<?php

namespace Puncto\Test;

use Puncto\Router;
use Puncto\StaticHandler;
use Puncto\Test\HeadersTestCase;

class StaticHandlerTest extends HeadersTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $_ENV['PUNCTO_ENV'] = 'production';
        $_ENV['PUNCTO_VERBOSITY'] = 'NONE';

        $this->base = __DIR__ . '/app/assets/';
    }

    private function prepareEnv($path)
    {
        $_SERVER['REMOTE_ADDR'] = '0.0.0.0';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['HTTP_ACCEPT'] = '*/*';

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = $path;

        $router = new Router(true);
        $router->register(__DIR__);
        $router->serveStatic('/assets/*');

        return $router;
    }

    /** @test */
    public function getsMimeType()
    {
        self::assertSame('text/plain', StaticHandler::getMimeType($this->base . 'test.txt'));
        self::assertSame('text/html', StaticHandler::getMimeType($this->base . 'test.html'));
        self::assertSame('text/css', StaticHandler::getMimeType($this->base . 'test.css'));
        self::assertSame('application/javascript', StaticHandler::getMimeType($this->base . 'test.js'));
        self::assertSame('image/jpeg', StaticHandler::getMimeType($this->base . 'test.jpg'));
        self::assertSame('image/png', StaticHandler::getMimeType($this->base . 'test.png'));
    }

    /** @test */
    public function servesAssets()
    {
        $router = $this->prepareEnv('/assets/test.txt');

        ob_start();
        $router->resolve();
        $output = ob_get_clean();

        $this->ensureHttpStatus(200);
        self::assertSame('This is a plain text file', $output);
    }

    /** @test */
    public function returnsNotFoundOnMissingAsset()
    {
        $router = $this->prepareEnv('/assets/missing.txt');

        ob_start();
        $router->resolve();
        $output = ob_get_clean();

        $this->ensureHttpStatus(404);
        self::assertSame('404 Not Found', $output);
    }

    /** @test */
    public function servesBuiltinAssets()
    {
        $_ENV['PUNCTO_ENV'] = 'development';

        $router = $this->prepareEnv('/assets/test.txt');
        $handler = new StaticHandler('/PUNCTO_DEV/assets/*', true);

        $handler->render($router->getRequest(), $router->getEnv(), ['*' => 'favicon.ico']);

        $this->ensureContentType('image/x-icon');
        $this->ensureHttpStatus(200);
    }

    /** @test */
    public function respectsCacheHttpIfModifiedSince()
    {
        $mtime = filemtime(__DIR__ . '/app/assets/test.png');
        $gmtMtime = gmdate('D, d M Y H:i:s', $mtime) . ' GMT';

        $_SERVER['HTTP_IF_MODIFIED_SINCE'] = $gmtMtime;
        $router = $this->prepareEnv('/assets/test.png');

        ob_start();
        $router->resolve();
        $output = ob_get_clean();

        $this->ensureHttpStatus(304);
        self::assertEmpty($output);
    }

    /** @test */
    public function respectsCacheHttpIfNoneMatch()
    {
        $mtime = filemtime(__DIR__ . '/app/assets/test.png');
        $gmtMtime = gmdate('D, d M Y H:i:s', $mtime) . ' GMT';
        $etag = sprintf('%08x-%08x', crc32($path), $mtime);

        $_SERVER['HTTP_IF_NONE_MATCH'] = $etag;
        $router = $this->prepareEnv('/assets/test.png');

        ob_start();
        $router->resolve();
        $output = ob_get_clean();

        $this->ensureHttpStatus(304);
        self::assertEmpty($output);
    }
}
