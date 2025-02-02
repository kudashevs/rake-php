<?php

namespace Kudashevs\RakePhp\Tests\Unit\Preparators;

use Kudashevs\RakePhp\Preparers\WordsPreparer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class WordsPreparerTest extends TestCase
{
    private WordsPreparer $preparator;

    protected function setUp(): void
    {
        $this->preparator = new WordsPreparer();
    }

    #[Test]
    public function it_can_prepare_provided_words(): void
    {
        $words = ['test', ' ', 42];

        $prepared = $this->preparator->prepare($words);

        $this->assertCount(1, $prepared);
    }
}
