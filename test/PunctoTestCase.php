<?php

namespace Puncto\Test;

use PHPUnit\Framework\TestCase;
use Puncto\PunctoObject;

class PunctoTestCase extends TestCase
{
    /** @test */
    public function extendsPunctoObject()
    {
        self::assertInstanceOf(PunctoObject::class, $this->instance);
    }
}
