<?php

namespace Puncto;

class Context extends PunctoObject
{
    public $request;
    public $env;
    public $params;
    public $renderer;

    public function __construct($request, $env, $params, $renderer)
    {
        $this->request = $request;
        $this->env = $env;
        $this->params = $params;
        $this->renderer = $renderer;
    }
}
