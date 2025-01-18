<?php

namespace Kudashevs\RakePhp\Tests\Unit\Modifiers;

use Kudashevs\RakePhp\Modifiers\PossessionModifier;
use Kudashevs\RakePhp\Rake;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PossessionModiferTest extends TestCase
{
    #[Test]
    public function it_can_modify_a_possession_with_apostrophe_without_a_modifier(): void
    {
        $service = new Rake();
        $text = 'that Olivia’s bag';

        $words = $service->extract($text);

        $this->assertCount(2, $words);
        $this->assertArrayHasKey('olivia', $words);
    }

    #[Test]
    public function it_cannot_modify_a_possession_with_single_quote_without_a_modifier(): void
    {
        $service = new Rake();
        $text = 'that brother\'s bag';

        $words = $service->extract($text);

        $this->assertCount(2, $words);
        $this->assertArrayHasKey('brother\'', $words);
    }

    #[Test]
    public function it_can_modify_a_possession_with_single_quote_with_a_modifier(): void
    {
        $service = new Rake(['modifiers' => PossessionModifier::class]);
        $text = 'that brother\'s bag';

        $words = $service->extract($text);

        $this->assertCount(2, $words);
        $this->assertArrayHasKey('brother', $words);
    }
}
