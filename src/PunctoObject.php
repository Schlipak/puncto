<?php

namespace Puncto;

abstract class PunctoObject
{
    public function __toString()
    {
        $name = get_class($this);
        return "<#$name>";
    }
}
