<?php

namespace Puncto\Test;

use PHPUnit\Framework\TestCase;
use Puncto\Utils\StringHelper;

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

    /** @test */
    public function cleanPath()
    {
        self::assertSame('/', StringHelper::toCleanPath('/'));
        self::assertSame('/demo/test', StringHelper::toCleanPath('/demo/test/'));
        self::assertSame('/test', StringHelper::toCleanPath('/test/'));
        self::assertSame('demo', StringHelper::toCleanPath('/demo/', true));
    }
}
