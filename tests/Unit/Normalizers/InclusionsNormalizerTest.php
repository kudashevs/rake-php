<?php

namespace Kudashevs\RakePhp\Tests\Unit\Normalizers;

use Kudashevs\RakePhp\Normalizers\InclusionsNormalizer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class InclusionsNormalizerTest extends TestCase
{
    private InclusionsNormalizer $preparator;

    protected function setUp(): void
    {
        $this->preparator = new InclusionsNormalizer();
    }

    #[Test]
    public function it_can_clean_provided_words(): void
    {
        $words = ['test', ' ', 42];

        $prepared = $this->preparator->prepare($words);

        $this->assertCount(1, $prepared);
    }
}
