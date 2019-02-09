<?php

namespace Puncto\RequestBody;

interface IParser
{
    /**
     * @throws Puncto\Exceptions\ParserException
     */
    public function parse($input);

    public function getType();
}
