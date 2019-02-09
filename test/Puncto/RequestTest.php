<?php

namespace Puncto\Test;

use PHPUnit\Framework\TestCase;
use Puncto\Env;
use Puncto\Request;
use Puncto\StringHelper;

class RequestTest extends TestCase
{
    private function createRequest($contentType = 'application/json')
    {
        $_ENV['PUNCTO_VERBOSITY'] = 'NONE';
        $_SERVER['HTTP_ACCEPT'] = 'text/html, application/json';
        $_SERVER['CONTENT_TYPE'] = $contentType;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['value'] = '123';

        return new Request(new Env());
    }

    /** @test */
    public function setsServerVariables()
    {
        $request = $this->createRequest();

        foreach ($_SERVER as $key => $value) {
            $key = StringHelper::toCamelCase($key);
            $actual = $request->$key;

            self::assertSame($value, $actual);
        }
    }

    /** @test */
    public function acceptFormatVerified()
    {
        $request = $this->createRequest();

        self::assertFalse($request->accepts('dummy/mime'));
        self::assertTrue($request->accepts('text/html'));
    }

    /** @test */
    public function getsBodyJson()
    {
        $_ENV[Request::TEST_KEY] = '{"demo": 444, "list": [1, 2, 3], "obj": {"foo": "bar"}}';

        $request = $this->createRequest();
        $body = $request->getBody();

        self::assertIsArray($body);
        self::assertSame('123', $body['value']);
        self::assertSame(444, $body['demo']);

        self::assertIsArray($body['list']);
        self::assertSame(1, $body['list'][0]);
        self::assertSame(2, $body['list'][1]);
        self::assertSame(3, $body['list'][2]);

        self::assertIsArray($body['obj']);
        self::assertArrayHasKey('foo', $body['obj']);
        self::assertSame('bar', $body['obj']['foo']);
    }

    /** @test */
    public function getsBodyFormUrlEncoded()
    {
        $_ENV[Request::TEST_KEY] = 'demo=444&more=other&list[]=test&list[]=more&user[name]=toto&user[age]=25';

        $request = $this->createRequest('application/x-www-form-urlencoded');
        $body = $request->getBody();

        self::assertIsArray($body);
        self::assertSame('123', $body['value']);
        self::assertSame('444', $body['demo']);
        self::assertSame('other', $body['more']);

        self::assertIsArray($body['list']);
        self::assertSame('test', $body['list'][0]);
        self::assertSame('more', $body['list'][1]);

        self::assertIsArray($body['user']);
        self::assertSame('toto', $body['user']['name']);
        self::assertSame('25', $body['user']['age']);
    }

    /** @test */
    public function getsBodyFormData()
    {
        $boundary = '----PunctoUnitTestBoundary1234567';
        $headers = "Content-Disposition: form-data; name=\"other\"\r\nX-Other: data";

        $_ENV[Request::TEST_KEY] = "--{$boundary}\r\n{$headers}\r\n\r\n42\r\n--{$boundary}--\r\n";

        $request = $this->createRequest("multipart/form-data; boundary=$boundary");
        $body = $request->getBody();

        self::assertIsArray($body);
        self::assertSame('123', $body['value']);

        self::assertIsArray($body['other']);
        self::assertIsArray($body['other']['headers']);
        self::assertSame('42', $body['other']['content']);
        self::assertSame('data', $body['other']['headers']['X-Other']);
    }

    /** @test */
    public function failsAndLogsOnInvalidBodyFormat()
    {
        $_ENV[Request::TEST_KEY] = 'invalid data';

        ob_start();
        $request = $this->createRequest();
        $body = $request->getBody();
        $output = ob_get_clean();

        self::assertIsArray($body);
        self::assertSame(1, count($body));
    }

    /** @test */
    public function failsOnInvalidFormat()
    {
        $_ENV[Request::TEST_KEY] = 'invalid data';

        $request = $this->createRequest('invalid/format');
        $body = $request->getBody();

        self::assertIsArray($body);
        self::assertSame(1, count($body));
    }

    /** @test */
    public function jsonFailsOnMaxStackDepth()
    {
        $json = '';

        for ($i = 0; $i < 1024; $i++) {
            $json .= '[';
        }

        for ($i = 0; $i < 1024; $i++) {
            $json .= ']';
        }

        $_ENV[Request::TEST_KEY] = $json;

        $request = $this->createRequest();
        $body = $request->getBody();

        self::assertIsArray($body);
        self::assertSame(1, count($body));
    }

    /** @test */
    public function jsonFailsOnStateMismatch()
    {
        $_ENV[Request::TEST_KEY] = '{"j": 1 ] }';

        $request = $this->createRequest();
        $body = $request->getBody();

        self::assertIsArray($body);
        self::assertSame(1, count($body));
    }

    /** @test */
    public function jsonFailsOnUnexpectedControlChar()
    {
        $_ENV[Request::TEST_KEY] = '�Xf��nR';

        $request = $this->createRequest();
        $body = $request->getBody();

        self::assertIsArray($body);
        self::assertSame(1, count($body));
    }

    /** @test */
    public function jsonFailsOnInvalidUtf8()
    {
        $value = "\xe2\x28\xa1";
        $_ENV[Request::TEST_KEY] = "{\"test\": \"{$value}\"}";

        $request = $this->createRequest();
        $body = $request->getBody();

        self::assertIsArray($body);
        self::assertSame(1, count($body));
    }
}
