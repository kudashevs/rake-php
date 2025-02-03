<?php

namespace Kudashevs\RakePhp\Tests\Unit\Normalizers;

use Kudashevs\RakePhp\Normalizers\WordsNormalizer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class WordsNormalizerTest extends TestCase
{
    private WordsNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new WordsNormalizer();
    }

    #[Test]
    public function it_can_clean_provided_words(): void
    {
        $words = ['test', ' ', 42];

        $prepared = $this->normalizer->prepare($words);

        $this->assertCount(1, $prepared);
    }
}
