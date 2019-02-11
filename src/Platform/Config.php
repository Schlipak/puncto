<?php

namespace Puncto\Platform;

class Config
{
    private $app;
    private $router;

    public function __construct($app, $router)
    {
        $this->app = $app;
        $this->router = $router;
    }

    public function __call($name, $args)
    {
        $this->router->$name(...$args);
    }

    public function serveStatic($path)
    {
        $this->router->serveStatic($path);
    }

    public function loadRoutes($file)
    {
        $this->router->load($file);
    }

    public function resource($name)
    {
        $this->router->registerResource($name);
    }
}
