<?php

namespace Puncto;

use Puncto\Autoloader;
use Puncto\Platform\Config;
use Puncto\Platform\Env;
use Puncto\Platform\Request;
use Puncto\Platform\Router;
use Puncto\PunctoObject;

class Application extends PunctoObject
{
    private $name;

    private $request;
    private $env;
    private $router;

    public function __construct($name = 'app', $skipTestModeCode = false)
    {
        error_reporting($skipTestModeCode ? E_ALL : 0);
        ini_set('display_errors', $skipTestModeCode ? 1 : 0);

        $this->name = $name;

        $this->register();

        $this->env = new Env();
        $this->request = new Request($this->env);
        $this->router = new Router($this->request, $this->env, $skipTestModeCode);
    }

    public function register()
    {
        $trace = debug_backtrace();
        $caller = $trace[1]['file'];
        $base = dirname($caller);

        Autoloader::register($base, $this->name);
    }

    public function configure($callback)
    {
        $config = new Config($this, $this->router);
        $callback($config);
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getEnv()
    {
        return $this->env;
    }

    public function getRenderer()
    {
        return $this->router->getRenderer();
    }

    public function getRouter()
    {
        return $this->router;
    }
}
