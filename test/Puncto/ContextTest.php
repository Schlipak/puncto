<?php

namespace Puncto\Test;

use Puncto\Context;
use Puncto\Test\PunctoTestCase;

class ContextTest extends PunctoTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->request = 111;
        $this->env = 222;
        $this->params = 333;
        $this->renderer = 444;

        $this->instance = new Context($this->request, $this->env, $this->params, $this->renderer);
    }

    public function containsData()
    {
        self::assertSame($this->request, $this->instance->request);
        self::assertSame($this->env, $this->instance->env);
        self::assertSame($this->params, $this->instance->params);
        self::assertSame($this->renderer, $this->instance->renderer);
    }
}
