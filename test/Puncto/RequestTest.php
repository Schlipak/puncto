<?php

namespace Puncto\Test;

use Puncto\Request;
use Puncto\StringHelper;

class RequestTest extends PunctoTestCase
{
    protected function setUp()
    {
        parent::setUp();

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
}
