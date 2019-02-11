<?php

namespace Puncto\Test;

use PHPUnit\Framework\TestCase;
use Puncto\Utils\Inflector;

class InflectorTest extends TestCase
{
    /** @test */
    public function pluralizes()
    {
        self::assertSame('fish', Inflector::pluralize('fish'));
        self::assertSame('people', Inflector::pluralize('person'));
        self::assertSame('mice', Inflector::pluralize('mouse'));
        self::assertSame('codes', Inflector::pluralize('code'));
        self::assertSame('s', Inflector::pluralize(''));
    }

    /** @test */
    public function pluralizesIf()
    {
        self::assertSame('1 fish', Inflector::pluralizeIf(1, 'fish'));
        self::assertSame('1 person', Inflector::pluralizeIf(1, 'person'));
        self::assertSame('2 mice', Inflector::pluralizeIf(2, 'mouse'));
        self::assertSame('300 codes', Inflector::pluralizeIf(300, 'code'));
        self::assertSame('0 s', Inflector::pluralizeIf(0, ''));
    }

    /** @test */
    public function singularizes()
    {
        self::assertSame('fish', Inflector::singularize('fish'));
        self::assertSame('person', Inflector::singularize('people'));
        self::assertSame('mouse', Inflector::singularize('mice'));
        self::assertSame('code', Inflector::singularize('codes'));
        self::assertSame('', Inflector::singularize('s'));
    }
}
