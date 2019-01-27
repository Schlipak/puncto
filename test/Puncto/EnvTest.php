<?php

namespace Puncto\Test;

use Puncto\Env;

class EnvTest extends PunctoTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $_ENV['DUMMY_TEST'] = 'dummy';
        $this->instance = new Env();
    }

    /** @test */
    public function setsVersionNumber()
    {
        self::assertSame(Env::VERSION, $this->instance->PUNCTO_VERSION);
    }

    /** @test */
    public function getsVariableFromEnv()
    {
        foreach ($_ENV as $key => $value) {
            self::assertSame($value, $this->instance->$key);
        }
    }

    /** @test */
    public function returnsNullOnMissingVariable()
    {
        self::assertNull($this->instance->PUNCTO_TEST_MISSING_ENV_VARIABLE);
    }
}
