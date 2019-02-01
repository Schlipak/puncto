<?php

namespace Puncto\Test;

use Puncto\Env;
use Puncto\Renderer;
use Puncto\Request;
use Puncto\Router;
use Puncto\Test\DummyController;
use Puncto\Test\PunctoTestCase;

class ControllerTest extends PunctoTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $_ENV['PUNCTO_ENV'] = 'production';
        $_ENV['PUNCTO_VERBOSITY'] = 'NONE';

        $_SERVER['REMOTE_ADDR'] = '0.0.0.0';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['HTTP_ACCEPT'] = 'text/html';

        $this->request = new Request();
        $this->env = new Env();
        $this->renderer = new Renderer([]);

        $this->instance = new DummyController($this->request, $this->env, [], $this->renderer);
    }

    /** @test */
    public function getsContextFromRenderer()
    {
        $this->renderer->appendContext([
            'test' => 123,
        ]);

        self::assertSame(123, $this->instance->getContext()['test']);
    }

    /** @test */
    public function appendsContextToRenderer()
    {
        $this->instance->appendContext(['test' => 444]);

        self::assertSame(444, $this->renderer->getContext()['test']);
    }

    /** @test */
    public function checksContextFromRenderer()
    {
        $this->renderer->appendContext(['newContext' => 4321]);

        self::assertTrue($this->instance->hasContext('newContext'));
    }

    /** @test */
    public function forwardsRenderToRenderer()
    {
        $router = new Router(true);
        $router->register(__DIR__);

        $output = $this->instance->render(__DIR__ . '/templates/basic', false);
        self::assertSame('This is a basic template', $output);
    }

    /** @test */
    public function forwardsRenderToRendererWithContext()
    {
        $router = new Router(true);
        $router->register(__DIR__);

        $this->instance->appendContext(['value' => 123]);
        $output = $this->instance->render(__DIR__ . '/templates/value', false);

        self::assertSame('The context value is 123', $output);
    }
}
