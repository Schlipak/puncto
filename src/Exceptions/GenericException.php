<?php

namespace Puncto\Exceptions;

use Puncto\Interfaces\IException;
use \Exception;

class GenericException extends Exception implements IException
{
    public function __toString()
    {
        $klass = get_class($this);

        return "$klass '{$this->message}' in {$this->file}({$this->line})\n{$this->getTraceAsString()}";
    }
}
