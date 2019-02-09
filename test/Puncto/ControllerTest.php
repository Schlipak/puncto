<?php

namespace Puncto\Test;

use PunctoUnit\DummyController;
use Puncto\Application;
use Puncto\Exceptions\RenderException;
use Puncto\Renderer;
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

        $this->app = new Application('puncto-unit', true);

        $this->request = $this->app->getRequest();
        $this->env = $this->app->getEnv();
        $this->renderer = $this->app->getRenderer();

        $this->instance = new DummyController($this->request, $this->env, [], $this->renderer);
    }

    /**
     * @test
     * @runInSeparateProcess
     * */
    public function getsContextFromRenderer()
    {
        $this->instance->appendContext(['test' => 123]);
        self::assertSame(123, $this->instance->getContext()['test']);
    }

    /**
     * @test
     * @runInSeparateProcess
     * */
    public function appendsContextToRenderer()
    {
        $this->instance->appendContext(['test' => 444]);
        self::assertSame(444, $this->instance->getContext()['test']);
    }

    /**
     * @test
     * @runInSeparateProcess
     * */
    public function checksContextFromRenderer()
    {
        $this->instance->appendContext(['newContext' => 4321]);
        self::assertTrue($this->instance->hasContext('newContext'));
    }

    /**
     * @test
     * @runInSeparateProcess
     * */
    public function forwardsRenderToRenderer()
    {
        $output = $this->instance->render('basic', true, 'html');
        self::assertSame('This is a basic template', $output);
    }

    /**
     * @test
     * @runInSeparateProcess
     * */
    public function forwardsRenderToRendererWithContext()
    {
        $this->instance->appendContext(['value' => 123]);
        $output = $this->instance->render('value');

        self::assertSame('The context value is 123', $output);
    }

    /**
     * @test
     * @runInSeparateProcess
     * */
    public function rendersPartial()
    {
        $output = $this->instance->render('partial');

        self::assertSame('This is a partial', $output);
    }

    /**
     * @test
     * @runInSeparateProcess
     * */
    public function catchesCircularDependencies()
    {
        $this->expectException(RenderException::class);

        $this->instance->render('circle_one');
    }

    /**
     * @test
     * @todo Update to test the real default actions once Models are implemented
     */
    public function hasDefaultResourceActions()
    {
        self::assertSame('Unimplemented PunctoUnit\\DummyController#index', $this->instance->index());
        self::assertSame('Unimplemented PunctoUnit\\DummyController#show', $this->instance->show());
        self::assertSame('Unimplemented PunctoUnit\\DummyController#create', $this->instance->create());
        self::assertSame('Unimplemented PunctoUnit\\DummyController#update', $this->instance->update());
        self::assertSame('Unimplemented PunctoUnit\\DummyController#delete', $this->instance->delete());
    }
}
