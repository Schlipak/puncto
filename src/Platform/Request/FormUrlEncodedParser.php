<?php

namespace Puncto\Platform\Request;

use Puncto\Platform\Request\IParser;

class FormUrlEncodedParser implements IParser
{
    public function parse($input)
    {
        $result = [];
        parse_str($input, $result);

        return $result;
    }

    /** @codeCoverageIgnore */
    public function getType()
    {
        return 'application/x-www-form-urlencoded';
    }
}
