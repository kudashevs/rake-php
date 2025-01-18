<?php

namespace Kudashevs\RakePhp\Tests\Unit;

use Kudashevs\RakePhp\Exceptions\InvalidOptionType;
use Kudashevs\RakePhp\Modifiers\Modifier;
use Kudashevs\RakePhp\Rake;
use Kudashevs\RakePhp\Stoplists\Stoplist;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RakeTest extends TestCase
{
    private Rake $service;

    protected function setUp(): void
    {
        $this->service = new Rake();
    }

    #[Test]
    public function it_throws_an_exception_when_a_wrong_stoplist(): void
    {
        $this->expectException(InvalidOptionType::class);
        $this->expectExceptionMessage('of type');

        new Rake(['stoplist' => 'wrong']);
    }

    #[Test]
    public function it_can_use_a_different_stoplist(): void
    {
        $stoplistStub = $this->createStub(Stoplist::class);
        $stoplistStub->method('getWords')
            ->willReturn(['a', 'is']);

        $service = new Rake(['stoplist' => $stoplistStub]);
        $text = 'this is a text';

        $words = $service->extract($text);

        $this->assertCount(2, $words);
        $this->assertArrayHasKey('this', $words);
    }

    #[Test]
    public function it_can_apply_modifiers(): void
    {
        $modifierStub = $this->createStub(Modifier::class);
        $modifierStub->method('modify')
            ->willReturn(['test', 'this']);

        $service = new Rake(['modifiers' => $modifierStub]);
        $text = 'this is a test';

        $words = $service->extract($text);

        $this->assertCount(2, $words);
        $this->assertArrayHasKey('this', $words);
    }

    #[Test]
    public function it_can_apply_modifiers_and_clean_the_results(): void
    {
        $modifierStub = $this->createStub(Modifier::class);
        $modifierStub->method('modify')
            ->willReturn(['test ', ' ', '   this']);

        $service = new Rake(['modifiers' => $modifierStub]);
        $text = 'this is a test';

        $words = $service->extract($text);

        $this->assertCount(2, $words);
        $this->assertArrayHasKey('test', $words);
        $this->assertArrayHasKey('this', $words);
    }

    #[Test]
    public function it_can_extract_words(): void
    {
        $text = 'split this phrase';

        $words = $this->service->extractWords($text);

        $this->assertCount(2, $words);
        $this->assertSame('split', current($words));
    }

    #[Test]
    public function it_can_extract_scores(): void
    {
        $text = 'split this phrase';

        $words = $this->service->extractScores($text);

        $this->assertCount(2, $words);
        $this->assertSame(1.0, current($words));
    }

    #[Test]
    public function it_throws_an_exception_when_an_invalid_exclude_type(): void
    {
        $this->expectException(InvalidOptionType::class);
        $this->expectExceptionMessage('array');

        new Rake(['exclude' => 'wrong']);
    }

    #[Test]
    public function it_can_exclude_words_from_a_stoplist(): void
    {
        $service = new Rake(['exclude' => ['this']]);
        $text = 'split this phrase';

        $words = $service->extract($text);

        $this->assertCount(1, $words);
    }

    #[Test]
    public function it_can_exclude_words_from_a_stoplist_in_a_different_case(): void
    {
        $service = new Rake(['exclude' => ['New']]);
        $text = 'visit New York now';

        $words = $service->extract($text);

        $this->assertCount(1, $words);
    }

    #[Test]
    public function it_throws_an_exception_when_an_invalid_include_type(): void
    {
        $this->expectException(InvalidOptionType::class);
        $this->expectExceptionMessage('array');

        new Rake(['include' => 'wrong']);
    }

    #[Test]
    public function it_can_include_words_to_a_stoplist(): void
    {
        $service = new Rake(['include' => ['split', 'phrase']]);
        $text = 'split this phrase';

        $words = $service->extract($text);

        $this->assertCount(0, $words);
    }

    #[Test]
    public function it_cannot_split_when_no_stop_words_in_a_text(): void
    {
        $text = 'unsplit phrase';

        $words = $this->service->extract($text);

        $this->assertCount(1, $words);
    }

    #[Test]
    public function it_can_split_words(): void
    {
        $text = 'split this phrase';

        $words = $this->service->extract($text);

        $this->assertCount(2, $words);
    }

    #[Test]
    public function it_can_split_a_numeric(): void
    {
        $text = 'split these 42 words';

        $words = $this->service->extract($text);

        $this->assertCount(2, $words);
    }

    #[Test]
    public function it_can_split_a_numeric_with_u2018(): void
    {
        $text = 'split these 4’2 words';

        $words = $this->service->extract($text);

        $this->assertCount(3, $words);
    }

    #[Test]
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
