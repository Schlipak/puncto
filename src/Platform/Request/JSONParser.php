<?php

namespace Puncto\Platform\Request;

use Puncto\Exceptions\ParserException;
use Puncto\Platform\Request\IParser;

class JSONParser implements IParser
{
    public function parse($input)
    {
        $output = @json_decode($input, true, 512);
        $error = $this->getErrorMessage();

        if (is_null($output) && $error) {
            throw new ParserException("Unable to parse application/json: $error");
        }

        return $output;
    }

    private function getErrorMessage()
    {
        switch (json_last_error()) {
            case JSON_ERROR_DEPTH:
                return 'Maximum stack depth exceeded';
            case JSON_ERROR_STATE_MISMATCH:
                return 'Underflow or modes mismatch';
            case JSON_ERROR_CTRL_CHAR:
                return 'Unexpected control character found';
            case JSON_ERROR_SYNTAX:
                return 'Malformed data, syntax error';
            case JSON_ERROR_UTF8:
                return 'Malformed UTF-8 characters, possibly incorrectly encoded';
            default:
                return 'Unknown error';
        }

        return null;
    }

    /** @codeCoverageIgnore */
    public function getType()
    {
        return 'application/json';
    }
}
