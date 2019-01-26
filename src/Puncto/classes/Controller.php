<?php

namespace Puncto;

abstract class Controller
{
    protected $request;
    protected $env;
    protected $params;
    protected $renderer;

    public function __construct($request, $env, $params, $renderer)
    {
        $this->request = $request;
        $this->env = $env;
        $this->params = $params;
        $this->renderer = $renderer;
    }

    protected function render(...$args)
    {
        return $this->renderer->render(...$args);
    }
}
