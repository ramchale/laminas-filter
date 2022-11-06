<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Word;

use Laminas\Filter\Word\UnderscoreToSeparator as UnderscoreToSeparatorFilter;
use PHPUnit\Framework\TestCase;

class UnderscoreToSeparatorTest extends TestCase
{
    public function testFilterSeparatesCamelCasedWordsDefaultSeparator(): void
    {
        $string   = 'underscore_separated_words';
        $filter   = new UnderscoreToSeparatorFilter();
        $filtered = $filter($string);

        self::assertNotEquals($string, $filtered);
        self::assertSame('underscore separated words', $filtered);
    }

    public function testFilterSeparatesCamelCasedWordsProvidedSeparator(): void
    {
        $string   = 'underscore_separated_words';
        $filter   = new UnderscoreToSeparatorFilter(':=:');
        $filtered = $filter($string);

        self::assertNotEquals($string, $filtered);
        self::assertSame('underscore:=:separated:=:words', $filtered);
    }
}
