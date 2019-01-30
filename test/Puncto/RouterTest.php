<?php

namespace Puncto\Test;

use Puncto\Router;
use Puncto\Test\HeadersTestCase;

class RouterTest extends HeadersTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $_ENV['PUNCTO_ENV'] = 'production';
        $_ENV['PUNCTO_VERBOSITY'] = 'NONE';

        $_SERVER['REMOTE_ADDR'] = '0.0.0.0';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['HTTP_ACCEPT'] = 'text/html';
    }

    /** @test */
    public function resolvesSimplePath()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';

        $expected = 'TEST_OUTPUT';

        $router = new Router(true);
        $router->get(['/test', 'TestHandler'], function () use ($expected) {
            return $expected;
        });

        ob_start();
        $router->resolve();
        $output = ob_get_clean();

        $this->ensureHttpStatus(200);
        self::assertSame($expected, $output);
    }

    /** @test */
    public function resolvesHeadWithoutOuput()
    {
        $_SERVER['REQUEST_METHOD'] = 'HEAD';
        $_SERVER['REQUEST_URI'] = '/test';

        $router = new Router(true);
        $router->get(['/test', 'TestHandler'], function () {
            return 'Something';
        });

        ob_start();
        $router->resolve();
        $output = ob_get_clean();

        $this->ensureHttpStatus(200);
        self::assertSame('', $output);
    }

    /** @test */
    public function resolvesNotFound()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/nothing';

        $router = new Router(true);
        $router->get(['/test', 'TestHandler'], function () {
            return 'Something';
        });
        $router->onError(function ($req, $env, $params, $renderer) {
            $ctx = $renderer->getContext();
            $code = $ctx['errorCode'];

            return "ERROR $code";
        });

        ob_start();
        $router->resolve();
        $output = ob_get_clean();

        $this->ensureHttpStatus(404);
        self::assertSame('ERROR 404', $output);
    }

    /** @test */
    public function errorsOnInvalidMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'PHONY';
        $_SERVER['REQUEST_URI'] = '/test';

        $router = new Router(true);
        $router->get(['/test', 'TestHandler'], function () {
            return 'Something';
        });
        $router->onError(function ($req, $env, $params, $renderer) {
            $ctx = $renderer->getContext();
            $code = $ctx['errorCode'];

            return "ERROR $code";
        });

        ob_start();
        $router->resolve();
        $output = ob_get_clean();

        $this->ensureHttpStatus(405);
        self::assertSame('ERROR 405', $output);
    }

    /** @test */
    public function errorsOnInvalidRouteMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/other';

        $router = new Router(true);
        $router->get(['/other', 'TestHandler'], function () {
            return 'Valid';
        });
        $router->invalid(['/invalid', 'TestHandler'], function () {
            return 'Invalid';
        });
        $router->onError(function ($req, $env, $params, $renderer) {
            $ctx = $renderer->getContext();
            $code = $ctx['errorCode'];

            return "ERROR $code";
        });

        ob_start();
        $router->resolve();
        $output = ob_get_clean();

        $this->ensureHttpStatus(200);
        self::assertSame('Valid', $output);
    }

    /** @test */
    public function returnsDefaultErrorHander()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/nothing';

        $router = new Router(true);
        $router->get(['/test', 'TestHandler'], function () {
            return 'Something';
        });

        ob_start();
        $router->resolve();
        $output = ob_get_clean();

        $this->ensureHttpStatus(404);
        self::assertSame('404 Not Found', $output);
    }

    /** @test */
    public function rendersDevelopmentHomepage()
    {
        $_ENV['PUNCTO_ENV'] = 'development';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';

        $router = new Router(true);

        ob_start();
        $router->resolve();
        $output = ob_get_clean();

        $this->ensureHttpStatus(200);
        self::assertNotEmpty($output);
    }

    /** @test */
    public function showsDevelopmentErrorPageOnInvalidMethod()
    {
        $_ENV['PUNCTO_ENV'] = 'development';

        $router = new Router(true);

        ob_start();
        $router->onError(function ($req, $env, $params, $renderer) {
            $ctx = $renderer->getContext();
            $code = $ctx['errorCode'];

            return "ERROR $code";
        });
        $router->void(['/', 'InvalidMethod'], function () {
            return 'VOID';
        });
        $output = ob_get_clean();

        $this->ensureHttpStatus(405);
        self::assertNotSame('ERROR 405', $output);
    }
}
