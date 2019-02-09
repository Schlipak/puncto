<?php

namespace Puncto\RequestBody;

use Puncto\Exceptions\ParserException;
use Puncto\RequestBody\FormDataParser;
use Puncto\RequestBody\FormUrlEncodedParser;
use Puncto\RequestBody\JSONParser;
use Puncto\RequestBody\XMLParser;
use Puncto\Logger;

abstract class ParserFactory
{
    /**
     * @throws Puncto\Exceptions\ParserException
     */
    public static function create($request)
    {
        $contentType = $request->contentTypeFormat();
        $format = $contentType['format'] ?: '(unknown)';

        Logger::debug("  Parsing input format $format");

        switch ($format) {
            case 'multipart/form-data':
                return new FormDataParser($contentType['boundary']);
            case 'application/x-www-form-urlencoded':
                return new FormUrlEncodedParser();
            case 'application/json':
                return new JSONParser();
            default:
                throw new ParserException("Unsupported input format: $format");
        }
    }
}
