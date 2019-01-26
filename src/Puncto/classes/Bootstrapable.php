<?php

namespace Puncto;

abstract class Bootstrapable
{
    public function __construct()
    {
        $this->bootstrapSelf();
    }

    protected abstract function bootstrapSelf();
}
