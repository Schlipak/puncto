<?php

namespace Puncto\Test;

use Puncto\Request;
use Puncto\StringHelper;

class RequestTest extends PunctoTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $_SERVER['HTTP_ACCEPT'] = 'text/html, application/json';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['value'] = '123';

        $this->instance = new Request();
    }

    /** @test */
    public function setsServerVariables()
    {
        foreach ($_SERVER as $key => $value) {
            $key = StringHelper::toCamelCase($key);
            $actual = $this->instance->$key;

            self::assertSame($value, $actual);
        }
    }

    /** @test */
    public function acceptFormatVerified()
    {
        self::assertFalse($this->instance->accepts('dummy/mime'));
        self::assertTrue($this->instance->accepts('text/html'));
    }

    /** @test */
    public function getsBody()
    {
        $body = $this->instance->getBody();

        self::assertIsArray($body);
        self::assertSame('123', $body['value']);
    }
}
