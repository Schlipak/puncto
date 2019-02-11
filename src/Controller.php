<?php

namespace Puncto;

use Puncto\View\IRenderable;

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

    public function render($template, $expandPath = true, $ext = 'html.php')
    {
        return $this->renderer->render($template, $expandPath, $ext);
    }

    public function getContext()
    {
        return $this->renderer->getContext();
    }

    public function appendContext($newContext)
    {
        $this->renderer->appendContext($newContext);
    }

    public function hasContext($name)
    {
        return $this->renderer->hasContext($name);
    }

    public function index()
    {
        $klass = get_class($this);
        return "Unimplemented {$klass}#index";
    }

    public function show()
    {
        $klass = get_class($this);
        return "Unimplemented {$klass}#show";
    }

    public function create()
    {
        $klass = get_class($this);
        return "Unimplemented {$klass}#create";
    }

    public function update()
    {
        $klass = get_class($this);
        return "Unimplemented {$klass}#update";
    }

    public function delete()
    {
        $klass = get_class($this);
        return "Unimplemented {$klass}#delete";
    }
}
