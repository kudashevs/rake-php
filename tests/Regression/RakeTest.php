<?php

namespace Kudashevs\RakePhp\Tests\Regression;

use Kudashevs\RakePhp\Rake;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RakeTest extends TestCase
{
    #[Test]
    public function it_can_handle_an_empty_include_exclude_words(): void
    {
        /*
         * Bug found: 28.01.2025
         * Details: when an empty string is added, it leads to unpredictable results because the
         * empty input affects a regular expression into the stopWordsRegex property.
         */
        $service = new Rake([
            'exclude' => ['new', ''],
            'include' => ['beautiful', ''],
        ]);
        $text = 'New York City is a beautiful one';

        $words = $service->extract($text);

        $this->assertCount(1, $words);
        $this->assertSame('new york city', key($words));
    }
}
