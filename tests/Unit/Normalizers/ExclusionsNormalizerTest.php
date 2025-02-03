<?php

namespace Kudashevs\RakePhp\Tests\Unit\Normalizers;

use Kudashevs\RakePhp\Normalizers\ExclusionsNormalizer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ExclusionsNormalizerTest extends TestCase
{
    private ExclusionsNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new ExclusionsNormalizer();
    }

    #[Test]
    public function it_can_clean_provided_words(): void
    {
        $words = ['test', ' ', 42];

        $prepared = $this->normalizer->prepare($words);

        $this->assertCount(1, $prepared);
    }

    #[Test]
    public function it_can_remove_duplicates(): void
    {
        $words = ['test', 'test'];

        $prepared = $this->normalizer->prepare($words);

        $this->assertCount(1, $prepared);
    }

    #[Test]
    public function it_can_normalize_and_unfold_a_word_alternation_regex(): void
    {
        $words = ['change(s|d)'];

        $prepared = $this->normalizer->prepare($words);

        $this->assertCount(3, $prepared);
    }

    #[Test]
    public function it_can_noramlize_and_unfold_an_alternation_rege(): void
    {
        $words = ['(change|changes)'];

        $prepared = $this->normalizer->prepare($words);

        $this->assertCount(2, $prepared);
    }

    #[Test]
    public function it_can_normalize_and_return_intact_a_simple_match_regex(): void
    {
        $words = ['.+(ly)'];

        $prepared = $this->normalizer->prepare($words);

        $this->assertSame('.+(ly)', current($prepared));
    }

    #[Test]
    public function it_can_normalize_and_quote_other_regexs(): void
    {
        $words = ['cause(\w+){1,2}'];

        $prepared = $this->normalizer->prepare($words);

        $this->assertMatchesRegularExpression('/^.+\(.+\)\\\{.*\\\}$/', current($prepared));
    }
}
