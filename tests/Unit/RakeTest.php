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
    public function it_cannot_split_when_no_stop_words_in_a_text(): void
    {
        $text = 'unsplit phrase';

        $words = $this->service->extract($text);

        $this->assertCount(1, $words);
    }

    /** @test */
    public function it_can_split_words(): void
    {
        $text = 'split this phrase';

        $words = $this->service->extract($text);

        $this->assertCount(2, $words);
    }

    /** @test */
    public function it_can_split_a_numeric(): void
    {
        $text = 'split these 42 words';

        $words = $this->service->extract($text);

        $this->assertCount(2, $words);
    }

    /** @test */
    public function it_can_split_a_numeric_with_u2018(): void
    {
        $text = 'split these 4’2 words';

        $words = $this->service->extract($text);

        $this->assertCount(3, $words);
    }

        $this->assertCount(2, $phrases);
    }
}
