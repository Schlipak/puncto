<?php

namespace Puncto;

abstract class Bootstrapable extends PunctoObject
{
    public function __construct()
    {
        $this->bootstrapSelf();
    }

    abstract protected function bootstrapSelf();

    /**
     * Catch property accesses and return null when they don't exist.
     * Avoids having to use isset() before accessing any property
     */
    public function __get($name)
    {
        return null;
    }
}
