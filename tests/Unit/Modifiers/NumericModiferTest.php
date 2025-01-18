<?php

namespace Kudashevs\RakePhp\Tests\Unit\Modifiers;

use Kudashevs\RakePhp\Modifiers\NumericModifier;
use Kudashevs\RakePhp\Rake;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class NumericModiferTest extends TestCase
{
    #[Test]
    public function it_cannot_modify_numeric_without_a_modifier(): void
    {
        $service = new Rake();
        $text = 'split 42 words';

        $words = $service->extract($text);

        $this->assertCount(1, $words);
    }

    #[Test]
    public function it_can_modify_numeric_with_a_modifier(): void
    {
        $service = new Rake([
            'modifiers' => NumericModifier::class,
        ]);
        $text = 'split 42 words';

        $words = $service->extract($text);

        $this->assertCount(2, $words);
    }
}
