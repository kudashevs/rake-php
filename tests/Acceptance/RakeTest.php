<?php

namespace Kudashevs\RakePhp\Tests\Acceptance;

use Kudashevs\RakePhp\Rake;
use PHPUnit\Framework\TestCase;

class RakeTest extends TestCase
{
    /** @test */
    public function it_should_generate_result_with_score(): void
    {
        $text = "Criteria of compatibility of a system of linear Diophantine equations, strict inequations, and nonstrict inequations are considered. Upper bounds for components of a minimal set of solutions and algorithms of construction of minimal generating sets of solutions for all types of systems are given.";
        $expected = [
            'linear diophantine equations' => 9.0,
            'minimal generating sets' => 8.5,
            'minimal set' => 4.5,
            'strict inequations' => 4.0,
            'nonstrict inequations' => 4.0,
            'upper bounds' => 4.0,
            'criteria' => 1.0,
            'compatibility' => 1.0,
            'system' => 1.0,
            'considered' => 1.0,
            'components' => 1.0,
            'solutions' => 1.0,
            'algorithms' => 1.0,
            'construction' => 1.0,
            'types' => 1.0,
            'systems' => 1.0,
        ];

        $rake = new Rake();
        $phrases = $rake->extract($text);

        $this->assertSame($expected, $phrases);
    }
}
