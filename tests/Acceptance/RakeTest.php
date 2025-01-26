<?php

namespace Kudashevs\RakePhp\Tests\Acceptance;

use Kudashevs\RakePhp\Rake;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * This test will use different RAKE examples. Here are the sources:
 * - common rake example - @see https://github.com/aneesha/RAKE/
 * - prashant package example - @see https://github.com/u-prashant/RAKE
 * - richdark package example - @see https://github.com/Richdark/RAKE-PHP/
 */
class RakeTest extends TestCase
{
    #[Test]
    public function it_should_handle_multibyte_strings(): void
    {
        $text = "an important ‘quote’ of quotes";

        $rake = new Rake();
        $phrases = $rake->extract($text);

        $this->assertCount(3, $phrases);
    }

    #[Test]
    public function it_should_calculate_a_common_rake_example(): void
    {
        $text = "Compatibility of systems of linear constraints over the set of natural numbers. Criteria of compatibility of a system of linear Diophantine equations, strict inequations, and nonstrict inequations are considered. Upper bounds for components of a minimal set of solutions and algorithms of construction of minimal generating sets of solutions for all types of systems are given. These criteria and the corresponding algorithms for constructing a minimal supporting set of solutions can be used in solving all the considered types of systems and systems of mixed types";
        $expected = [
            'minimal generating sets' => 8.666666666666666,
            'linear diophantine equations' => 8.5,
            'minimal supporting set' => 7.666666666666666,
            'minimal set' => 4.666666666666666,
            'linear constraints' => 4.5,
            'natural numbers' => 4.0,
            'strict inequations' => 4.0,
            'nonstrict inequations' => 4.0,
            'upper bounds' => 4.0,
            'mixed types' => 3.666666666666667,
            'considered types' => 3.166666666666667,
            'set' => 2.0,
            'types' => 1.6666666666666667,
            'considered' => 1.5,
            'compatibility' => 1.0,
            'systems' => 1.0,
            'criteria' => 1.0,
            'system' => 1.0,
            'components' => 1.0,
            'solutions' => 1.0,
            'algorithms' => 1.0,
            'construction' => 1.0,
            'constructing' => 1.0,
            'solving' => 1.0,
        ];

        $rake = new Rake();
        $phrases = $rake->extract($text);

        $this->assertSame($expected, $phrases);
    }

    #[Test]
    public function it_should_calculate_a_prashant_package_example(): void
    {
        $text = "Keyword extraction is not that difficult after all.
        There are many libraries that can help you with keyword extraction.
        Rapid automatic keyword extraction is one of those.";
        $expected = [
            'rapid automatic keyword extraction' => 13.333333333333332,
            'keyword extraction' => 5.333333333333333,
            'difficult' => 1.0,
            'libraries' => 1.0,
        ];

        $rake = new Rake();
        $phrases = $rake->extract($text);

        $this->assertSame($expected, $phrases);
    }

    #[Test]
    public function it_should_calculate_a_richdark_package_example(): void
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
