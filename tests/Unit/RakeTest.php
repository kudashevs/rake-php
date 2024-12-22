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

        new Rake(['stoplist' => 'wrong']);
    }

    /** @test */
    public function it_can_extract_words(): void
    {
        $text = 'split this phrase';

        $words = $this->service->extractWords($text);

        $this->assertCount(2, $words);
        $this->assertSame('split', current($words));
    }

    /** @test */
    public function it_can_extract_scores(): void
    {
        $text = 'split this phrase';

        $words = $this->service->extractScores($text);

        $this->assertCount(2, $words);
        $this->assertSame(1.0, current($words));
    }

    /** @test */
    public function it_can_use_a_different_stoplist(): void
    {
        $service = new Rake(['stoplist' => __DIR__ . '/../fixtures/stoplist.txt']);
        $text = 'this is a text';

        $words = $service->extract($text);

        $this->assertCount(2, $words);
        $this->assertArrayHasKey('this', $words);
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

    /** @test */
    public function it_can_split_with_new_lines(): void
    {
        $text = "split\nthese\n42\nwords\n";

        $words = $this->service->extract($text);

        $this->assertCount(2, $words);
        $this->assertFalse($this->assertMatchesRegex('/\R/', array_key_last($words)));
    }

    private function assertMatchesRegex(string $regex, string $text): bool
    {
        return preg_match($regex, $text) === 1;
    }
}
