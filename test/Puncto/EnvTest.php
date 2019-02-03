<?php

namespace Puncto\Test;

use Puncto\Env;

class EnvTest extends PunctoTestCase
{
    protected function setUp()
    {
        parent::setUp();

        unset($_ENV['PUNCTO_ENV']);
        $_ENV['DUMMY_TEST'] = 'dummy';

        $this->instance = new Env();
    }

    /** @test */
    public function setsVersionNumber()
    {
        self::assertSame(Env::getVersion(), $this->instance->PUNCTO_VERSION);
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

    /** @test */
    public function fallsBackToDevelopmentWhenNotSet()
    {
        self::assertSame('development', $this->instance->PUNCTO_ENV);
    }
}
