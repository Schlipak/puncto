<?php

namespace Puncto\Exceptions;

use Puncto\Exceptions\IException;
use \Exception;

class FatalException extends Exception implements IException
{
    public function __toString()
    {
        $klass = get_class($this);

        return "!! Fatal Exception: '{$this->message}' in {$this->file}({$this->line})\n{$this->getTraceAsString()}";
    }
}
