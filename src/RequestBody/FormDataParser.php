<?php

namespace Puncto\RequestBody;

use Puncto\RequestBody\IParser;

class FormDataParser implements IParser
{
    public function __construct($boundary)
    {
        $this->boundary = $boundary;
    }

    public function parse($input)
    {
        $params = [];
        $sections = array_filter(explode($this->boundary, $input));

        foreach ($sections as $section) {
            $sectionParams = [];
            $parts = explode("\r\n\r\n", $section);

            if (count($parts) !== 2) {
                continue;
            }

            list($headers, $content) = array_map(function ($part) {
                return trim($part);
            }, $parts);

            $sectionParams['headers'] = [];
            $sectionParams['content'] = $content;

            $name = '';
            $headers = explode("\r\n", $headers);
            foreach ($headers as $header) {
                $matches = [];

                if (preg_match("/Content-Disposition: form-data/", $header)) {
                    preg_match_all("/([\w\-_]+)=\"([^\"]+)\"/", $header, $matches);
                    $headerParams = array_combine($matches[1], $matches[2]);

                    $sectionParams['headers'] = array_merge($sectionParams['headers'], $headerParams);
                    $name = $headerParams['name'];
                } else {
                    list($headerName, $value) = array_map(function ($value) {
                        return trim($value);
                    }, explode(':', $header));

                    $sectionParams['headers'][$headerName] = $value;
                }
            }

            $params[$name] = $sectionParams;
        }

        return $params;
    }

    /** @codeCoverageIgnore */
    public function getType()
    {
        return 'multipart/form-data';
    }
}
