<?php

namespace Puncto\View;

interface IRenderable
{
    public function render($template);

    public function getContext();

    public function appendContext($newContext);

    public function hasContext($name);
}
