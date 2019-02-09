<?php

namespace Puncto\Test;

use PHPUnit\Framework\Error\Error;
use Puncto\Application;
use Puncto\Exceptions\FatalException;
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

    private function createApplication($name = 'puncto-unit')
    {
        $this->app = new Application($name, true);
        $this->router = $this->app->getRouter();
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function resolvesSimplePath()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';

        $expected = 'TEST_OUTPUT';

        $this->createApplication();
        $this->app->configure(function ($config) use ($expected) {
            $config->get(['/test', 'TestHandler'], function () use ($expected) {
                return $expected;
            });
        });

        ob_start();
        $this->router->resolve();
        $output = ob_get_clean();

        $this->ensureHttpStatus(200);
        self::assertSame($expected, $output);
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function resolvesHeadWithoutOuput()
    {
        $_SERVER['REQUEST_METHOD'] = 'HEAD';
        $_SERVER['REQUEST_URI'] = '/test';

        $this->createApplication();
        $this->app->configure(function ($config) {
            $config->get(['/test', 'TestHandler'], function () {
                return 'Something';
            });
        });

        ob_start();
        $this->router->resolve();
        $output = ob_get_clean();

        $this->ensureHttpStatus(200);
        self::assertSame('', $output);
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function resolvesNotFound()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/nothing';

        $this->createApplication();
        $this->app->configure(function ($config) {
            $config->get(['/test', 'TestHandler'], function () {
                return 'Something';
            });

            $config->onError(function ($req, $env, $params, $renderer) {
                $ctx = $renderer->getContext();
                $code = $ctx['errorCode'];

                return "ERROR $code";
            });
        });

        ob_start();
        $this->router->resolve();
        $output = ob_get_clean();

        $this->ensureHttpStatus(404);
        self::assertSame('ERROR 404', $output);
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function resolvesNotFoundAsJson()
    {
        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/nothing';

        $this->createApplication();
        $this->app->configure(function ($config) {
            $config->get(['/test', 'TestHandler'], function () {
                return 'Something';
            });
        });

        ob_start();
        $this->router->resolve();
        $output = ob_get_clean();

        $this->ensureHttpStatus(404);
        self::assertSame('{"status":"error","message":"Not Found","code":404}', $output);
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function errorsOnInvalidMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'PHONY';
        $_SERVER['REQUEST_URI'] = '/test';

        $this->createApplication();
        $this->app->configure(function ($config) {
            $config->get(['/test', 'TestHandler'], function () {
                return 'Something';
            });

            $config->onError(function ($req, $env, $params, $renderer) {
                $ctx = $renderer->getContext();
                $code = $ctx['errorCode'];

                return "ERROR $code";
            });
        });

        ob_start();
        $this->router->resolve();
        $output = ob_get_clean();

        $this->ensureHttpStatus(405);
        self::assertSame('ERROR 405', $output);
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function errorsOnInvalidRouteMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/other';

        $this->createApplication();
        $this->app->configure(function ($config) {
            $config->get(['/other', 'TestHandler'], function () {
                return 'Valid';
            });

            $config->invalid(['/invalid', 'TestHandler'], function () {
                return 'Invalid';
            });

            $config->onError(function ($req, $env, $params, $renderer) {
                $ctx = $renderer->getContext();
                $code = $ctx['errorCode'];

                return "ERROR $code";
            });
        });

        ob_start();
        $this->router->resolve();
        $output = ob_get_clean();

        $this->ensureHttpStatus(200);
        self::assertSame('Valid', $output);
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function returnsDefaultErrorHander()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/nothing';

        $this->createApplication();
        $this->app->configure(function ($config) {
            $config->get(['/test', 'TestHandler'], function () {
                return 'Something';
            });
        });

        ob_start();
        $this->router->resolve();
        $output = ob_get_clean();

        $this->ensureHttpStatus(404);
        self::assertSame('404 Not Found', $output);
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function rendersDevelopmentHomepage()
    {
        $_ENV['PUNCTO_ENV'] = 'development';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';

        $this->createApplication();

        ob_start();
        $this->router->resolve();
        $output = ob_get_clean();

        $this->ensureHttpStatus(200);
        self::assertNotEmpty($output);
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function showsDevelopmentErrorPageOnInvalidMethod()
    {
        $_ENV['PUNCTO_ENV'] = 'development';

        $this->createApplication();

        ob_start();
        $this->app->configure(function ($config) {
            $config->onError(function ($req, $env, $params, $renderer) {
                $ctx = $renderer->getContext();
                $code = $ctx['errorCode'];

                return "ERROR $code";
            });

            $config->void(['/', 'InvalidMethod'], function () {
                return 'VOID';
            });
        });
        $output = ob_get_clean();

        $this->ensureHttpStatus(405);
        self::assertNotSame('ERROR 405', $output);
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function registerRejectsMalformedAppName()
    {
        $this->assertApplicationInitFatalError('malformed\\appName');
        $this->assertApplicationInitFatalError('app-name-with-numbers-123');
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function loadsRoutesFromJson()
    {
        $_ENV['PUNCTO_ENV'] = 'development';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';

        $this->createApplication();
        $this->app->configure(function ($config) {
            $config->loadRoutes(__DIR__ . '/app/routes.json');
        });

        ob_start();
        $this->router->resolve();
        $output = ob_get_clean();

        $this->ensureHttpStatus(200);
        self::assertSame('Index', $output);
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function failsOnMissingController()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/missing-controller';

        $this->createApplication();
        $this->app->configure(function ($config) {
            $config->loadRoutes(__DIR__ . '/app/routes.json');
        });

        ob_start();
        $this->router->resolve();
        $output = ob_get_clean();

        $this->ensureHttpStatus(501);
        self::assertSame('501 Not Implemented', $output);
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function failsOnMissingAction()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/missing-action';

        $this->createApplication();
        $this->app->configure(function ($config) {
            $config->loadRoutes(__DIR__ . '/app/routes.json');
        });

        ob_start();
        $this->router->resolve();
        $output = ob_get_clean();

        $this->ensureHttpStatus(501);
        self::assertSame('501 Not Implemented', $output);
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function failsOnControllerError()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/controller-error';

        $this->createApplication();
        $this->app->configure(function ($config) {
            $config->loadRoutes(__DIR__ . '/app/routes.json');
        });

        ob_start();
        $this->router->resolve();
        $output = ob_get_clean();

        $this->ensureHttpStatus(500);
        self::assertSame('500 Internal Server Error', $output);
    }

    /**
     * @test
     */
    public function registersResource()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/users/123';

        $this->createApplication();
        $this->app->configure(function ($config) {
            $config->resource('users');
        });

        ob_start();
        $this->router->resolve();
        $output = ob_get_clean();

        $this->ensureHttpStatus(200);
        self::assertSame('User #123', $output);
    }

    private function assertApplicationInitFatalError($name)
    {
        try {
            $this->createApplication($name);
        } catch (FatalException $err) {
            return self::assertSame(FatalException::class, get_class($err));
        }

        throw new Error("Failed to assert exception FatalException", -1, __FILE__, __LINE__);
    }
}
