<?php

namespace Puncto\Test;

use PHPUnit\Framework\TestCase;
use Puncto\Traits\RenderHelper;

class DummyHelper
{
    use RenderHelper;
}

class RenderHelperTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->helper = new DummyHelper();
    }

    /** @test */
    public function escapesHtml()
    {
        $html = '<button>Test</button>';
        $simpleText = 'Simple text';
        $quotedText = 'Simple "quoted" text';

        self::assertNotSame($html, $this->helper->escape($html));
        self::assertSame(htmlspecialchars($html), $this->helper->escape($html));

        self::assertSame($simpleText, $this->helper->escape($simpleText));
        self::assertSame(htmlspecialchars($simpleText), $this->helper->escape($simpleText));

        self::assertNotSame($quotedText, $this->helper->escape($quotedText));
        self::assertSame(htmlspecialchars($quotedText), $this->helper->escape($quotedText));
    }

    /** @test */
    public function camelCasesStrings()
    {
        $simple = 'Simple';
        $multiple = 'Multiple word string';
        $dashed = 'dashed-Capitalized-string';
        $underscored = 'Underscored_string_With_Caps';

        self::assertSame('simple', $this->helper->camelCase($simple));
        self::assertSame('multipleWordString', $this->helper->camelCase($multiple));
        self::assertSame('dashedCapitalizedString', $this->helper->camelCase($dashed));
        self::assertSame('underscoredStringWithCaps', $this->helper->camelCase($underscored));
    }

    /** @test */
    public function classCasesStrings()
    {
        $simple = 'Simple';
        $multiple = 'Multiple word string';
        $dashed = 'dashed-Capitalized-string';
        $underscored = 'Underscored_string_With_Caps';

        self::assertSame('Simple', $this->helper->classCase($simple));
        self::assertSame('MultipleWordString', $this->helper->classCase($multiple));
        self::assertSame('DashedCapitalizedString', $this->helper->classCase($dashed));
        self::assertSame('UnderscoredStringWithCaps', $this->helper->classCase($underscored));
    }
}
