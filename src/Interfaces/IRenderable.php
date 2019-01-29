<?php

namespace Puncto\Interfaces;

interface IRenderable
{
    public function render($template);

    public function getContext();

    public function appendContext($newContext);

    public function hasContext($name);
}
