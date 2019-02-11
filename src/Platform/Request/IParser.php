<?php

namespace Puncto\Platform\Request;

interface IParser
{
    /**
     * @throws Puncto\Exceptions\ParserException
     */
    public function parse($input);

    public function getType();
}
