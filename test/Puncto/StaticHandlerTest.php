<?php

namespace Puncto\Test;

use Puncto\Application;
use Puncto\Platform\Router;
use Puncto\Platform\StaticHandler;
use Puncto\Test\HeadersTestCase;

/** @runTestsInSeparateProcesses */
class StaticHandlerTest extends HeadersTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $_ENV['PUNCTO_ENV'] = 'production';
        $_ENV['PUNCTO_VERBOSITY'] = 'NONE';

        $this->base = __DIR__ . '/app/assets/';
    }

    private function createApplication($name = 'puncto-unit')
    {
        $this->app = new Application($name, true);
        $this->router = $this->app->getRouter();
    }

    private function prepareEnv($path)
    {
        $_SERVER['REMOTE_ADDR'] = '0.0.0.0';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['HTTP_ACCEPT'] = '*/*';

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = $path;

        $this->createApplication();
        $this->app->configure(function ($config) {
            $config->serveStatic('/assets/*');
        });
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
        $this->prepareEnv('/assets/test.txt');

        ob_start();
        $this->router->resolve();
        $output = ob_get_clean();

        $this->ensureHttpStatus(200);
        self::assertSame('This is a plain text file', $output);
    }

    /** @test */
    public function returnsNotFoundOnMissingAsset()
    {
        $this->prepareEnv('/assets/missing.txt');

        ob_start();
        $this->router->resolve();
        $output = ob_get_clean();

        $this->ensureHttpStatus(404);
        self::assertSame('404 Not Found', $output);
    }

    /** @test */
    public function servesBuiltinAssets()
    {
        $_ENV['PUNCTO_ENV'] = 'development';

        $this->prepareEnv('/assets/test.txt');
        $handler = new StaticHandler('/PUNCTO_DEV/assets/*', true);

        $handler->render($this->router->getRequest(), $this->router->getEnv(), ['*' => 'favicon.ico']);

        $this->ensureContentType('image/x-icon');
        $this->ensureHttpStatus(200);
    }

    /** @test */
    public function respectsCacheHttpIfModifiedSince()
    {
        $mtime = filemtime(__DIR__ . '/app/assets/test.png');
        $gmtMtime = gmdate('D, d M Y H:i:s', $mtime) . ' GMT';

        $_SERVER['HTTP_IF_MODIFIED_SINCE'] = $gmtMtime;
        $this->prepareEnv('/assets/test.png');

        ob_start();
        $this->router->resolve();
        $output = ob_get_clean();

        $this->ensureHttpStatus(304);
        self::assertEmpty($output);
    }

    /** @test */
    public function respectsCacheHttpIfNoneMatch()
    {
        $path = __DIR__ . '/app/assets/test.png';
        $mtime = filemtime($path);
        $gmtMtime = gmdate('D, d M Y H:i:s', $mtime) . ' GMT';
        $etag = sprintf('%08x-%08x', crc32($path), $mtime);

        $_SERVER['HTTP_IF_NONE_MATCH'] = $etag;
        $this->prepareEnv('/assets/test.png');

        ob_start();
        $this->router->resolve();
        $output = ob_get_clean();

        $this->ensureHttpStatus(304);
        self::assertEmpty($output);
    }
}
