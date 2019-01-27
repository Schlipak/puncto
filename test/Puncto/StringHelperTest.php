<?php

namespace Puncto\Test;

use PHPUnit\Framework\TestCase;
use Puncto\StringHelper;

class StringHelperTest extends TestCase
{
    /** @test */
    public function camelCase()
    {
        self::assertSame('dummy', StringHelper::toCamelCase('DUMMY'));
        self::assertSame('dummyStringTest', StringHelper::toCamelCase('DUMMY_STRING_TEST'));
        self::assertSame('dummyStringTest', StringHelper::toCamelCase('DUMMY STRING TEST'));
    }

    /** @test */
    public function classCase()
    {
        self::assertSame('Dummy', StringHelper::toClassCase('DUMMY'));
        self::assertSame('DummyStringTest', StringHelper::toClassCase('DUMMY_STRING_TEST'));
        self::assertSame('DummyStringTest', StringHelper::toClassCase('DUMMY STRING TEST'));
    }
}
