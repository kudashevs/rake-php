<?php

namespace Kudashevs\RakePhp\Tests\Unit;

use Kudashevs\RakePhp\Exceptions\WrongStoplistSource;
use Kudashevs\RakePhp\Rake;
use PHPUnit\Framework\TestCase;

class RakeTest extends TestCase
{
    private Rake $service;

    protected function setUp(): void
    {
        $this->service = new Rake();
    }

    /** @test */
    public function it_throws_an_exception_when_a_wrong_stopwords_file(): void
    {
        $this->expectException(WrongStoplistSource::class);
        $this->expectExceptionMessage('wrong');

        new Rake('wrong');
    }

    /** @test */
    public function it_can_split_words(): void
    {
        $text = 'split word';
        $words = $this->service->extract($text);

        $this->assertCount(1, $words);
    }

    /** @test */
    public function it_can_split_a_numeric(): void
    {
        $text = 'split 42 word';
        $rake = new Rake();
        $phrases = $rake->extract($text);

        $this->assertCount(1, $phrases);
    }

    /** @test */
    public function it_can_split_a_numeric_with_u2018(): void
    {
        $text = 'split 4’2 word';
        $rake = new Rake();
        $phrases = $rake->extract($text);

        $this->assertCount(2, $phrases);
    }
}
