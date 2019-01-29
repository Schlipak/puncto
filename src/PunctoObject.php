<?php

namespace Puncto;

abstract class PunctoObject
{
    /** @codeCoverageIgnore */
    public function __toString()
    {
        $name = get_class($this);
        return "<#$name>";
    }
}
