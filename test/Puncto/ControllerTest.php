<?php

namespace Puncto\Test;

use PunctoUnit\DummyController;
use Puncto\Env;
use Puncto\Exceptions\RenderException;
use Puncto\Renderer;
use Puncto\Request;
use Puncto\Router;
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

        $this->$router = new Router(true);
        $this->$router->register(__DIR__, 'puncto-unit');

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
        $output = $this->instance->render('basic', true, 'html');
        self::assertSame('This is a basic template', $output);
    }

    /** @test */
    public function forwardsRenderToRendererWithContext()
    {
        $this->instance->appendContext(['value' => 123]);
        $output = $this->instance->render('value');

        self::assertSame('The context value is 123', $output);
    }

    /** @test */
    public function rendersPartial()
    {
        $output = $this->instance->render('partial');

        self::assertSame('This is a partial', $output);
    }

    /** @test */
    public function catchesCircularDependencies()
    {
        $this->expectException(RenderException::class);

        $this->instance->render('circle_one');
    }
}
