<?php

namespace Puncto;

use Puncto\Interfaces\IRenderable;

abstract class Controller extends PunctoObject implements IRenderable
{
    protected $request;
    protected $env;
    protected $params;
    private $renderer;

    public function __construct($request, $env, $params, $renderer)
    {
        $this->request = $request;
        $this->env = $env;
        $this->params = $params;
        $this->renderer = $renderer;
    }

    public function render(...$args)
    {
        return $this->renderer->render(...$args);
    }

    public function getContext()
    {
        return $this->renderer->getContext();
    }

    public function appendContext(...$args)
    {
        $this->renderer->appendContext(...$args);
    }

    public function hasContext(...$args)
    {
        return $this->renderer->hasContext(...$args);
    }
}
