<?php

namespace Puncto\Test;

use PHPUnit\Framework\TestCase;

class HeadersTestCase extends TestCase
{
    public static $headers;

    protected function ensureHttpStatus($expected)
    {
        foreach (self::$headers as $header) {
            $headerText = $header[0];

            if (strpos($headerText, "HTTP/") === 0) {
                $matches = [];

                preg_match("/^HTTP\/\d\.?\d?\s*(\d+).*$/", $headerText, $matches);

                $actual = intval($matches[1]);
                return self::assertSame($expected, $actual);
            }
        }

        self::assertSame('Missing HTTP status header', 'CRASH');
    }

    public static function setUpBeforeClass()
    {
        if (!extension_loaded('runkit')) {
            phpinfo();
            return;
        }

        runkit_function_rename('header', 'header_old');
        runkit_function_add(
            'header',
            '$string, $replace=true, $http_response_code = null',
            'Puncto\Test\HeadersTestCase::$headers[] = [$string, $replace, $http_response_code];'
        );
    }

    public static function tearDownAfterClass()
    {
        if (!extension_loaded('runkit')) {
            return;
        }

        runkit_function_remove('header');
        runkit_function_rename('header_old', 'header');
    }

    protected function setUp()
    {
        self::$headers = [];
    }
}
