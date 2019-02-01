<?php

namespace Puncto\Test;

use PHPUnit\Framework\TestCase;
use Puncto\Exceptions\RenderException;
use Puncto\Exceptions\FatalException;

class ExceptionTest extends TestCase
{
    /** @test */
    public function renderExceptionWorks()
    {
        $err = new RenderException("Test message", 123);

        self::assertSame(123, $err->getCode());
        self::assertTrue(boolval(preg_match("/RenderException 'Test message' in/", (string) $err)));
    }

    /** @test */
    public function fatalExceptionWorks()
    {
        $err = new FatalException("Test message", 123);

        self::assertSame(123, $err->getCode());
        self::assertTrue(boolval(preg_match("/^!! Fatal Exception: 'Test message' in/", (string) $err)));
    }
}
