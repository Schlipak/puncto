<?php

namespace Puncto\Test;

use PHPUnit\Framework\TestCase;
use Puncto\Logger;

class LoggerTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        if (!extension_loaded('runkit')) {
            phpinfo();
            return;
        }

        runkit_function_rename('error_log', 'error_log_old');
        runkit_function_add(
            'error_log',
            '$message',
            'echo $message;'
        );
    }

    public static function tearDownAfterClass()
    {
        if (!extension_loaded('runkit')) {
            return;
        }

        runkit_function_remove('error_log');
        runkit_function_rename('error_log_old', 'error_log');
    }

    protected function setUp()
    {
        parent::setUp();

        $_ENV['PUNCTO_VERBOSITY'] = 'INFO';
    }

    /** @test */
    public function logsMessage()
    {
        ob_start();
        Logger::log('Test');
        self::assertSame('Test', ob_get_clean());
    }

    /** @test */
    public function logsMessageWithColor()
    {
        ob_start();
        Logger::log('Test', 'red');
        self::assertSame("\e[0;31mTest\e[0m", ob_get_clean());
    }

    /** @test */
    public function logsWarning()
    {
        ob_start();
        Logger::warn('Test');
        self::assertSame("\e[0;33mTest\e[0m", ob_get_clean());
    }

    /** @test */
    public function logsError()
    {
        ob_start();
        Logger::error('Test');
        self::assertSame("\e[1;31mTest\e[0m", ob_get_clean());
    }

    /** @test */
    public function logsDebug()
    {
        $_ENV['PUNCTO_VERBOSITY'] = 'DEBUG';

        ob_start();
        Logger::debug('Test');
        self::assertSame("\e[7;37mTest\e[0m", ob_get_clean());
    }

    /** @test */
    public function verbosityDefaultsToInfo()
    {
        $_ENV['PUNCTO_VERBOSITY'] = 'MISSING_VERBOSITY';

        ob_start();
        Logger::debug('Test');
        self::assertEmpty(ob_get_clean());

        ob_start();
        Logger::log('Test');
        self::assertSame('Test', ob_get_clean());
    }

    /** @test */
    public function verbosityIsCaseInsensitive()
    {
        $_ENV['PUNCTO_VERBOSITY'] = 'warn';

        ob_start();
        Logger::debug('Test');
        self::assertEmpty(ob_get_clean());

        ob_start();
        Logger::log('Test');
        self::assertEmpty(ob_get_clean());

        ob_start();
        Logger::warn('Test');
        self::assertSame("\e[0;33mTest\e[0m", ob_get_clean());
    }

    /** @test */
    public function ignoresDebugOnDefaultVerbosity()
    {
        ob_start();
        Logger::debug('Test');
        self::assertEmpty(ob_get_clean());
    }

    /** @test */
    public function logsWithCutomColors()
    {
        ob_start();
        Logger::log('Test', 'red', 'normal', 'blue');
        self::assertSame("\e[0;31;44mTest\e[0m", ob_get_clean());
    }
}
