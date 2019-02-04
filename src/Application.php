<?php

namespace Puncto;

use Puncto\Autoloader;
use Puncto\Config;
use Puncto\Env;
use Puncto\Request;
use Puncto\Router;

class Application
{
    private $name;

    private $request;
    private $env;
    private $router;

    public function __construct($name = 'app')
    {
        $this->name = $name;

        $this->request = new Request();
        $this->env = new Env();
        $this->router = new Router($this->request, $this->env);

        $this->register();
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
}
