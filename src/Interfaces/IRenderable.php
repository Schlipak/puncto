<?php

namespace Puncto\Interfaces;

interface IRenderable
{
    public function render(...$args);

    public function getContext();

    public function appendContext(...$args);

    public function hasContext(...$args);
}
