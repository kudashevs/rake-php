<?php

namespace Kudashevs\RakePhp\Tests\Unit;

use Kudashevs\RakePhp\Exceptions\WrongStoplistSource;
use Kudashevs\RakePhp\Rake;
use PHPUnit\Framework\TestCase;

class RakeTest extends TestCase
{
    /** @test */
    public function it_throws_an_exception_when_a_wrong_stopwords_file(): void
    {
        $this->expectException(WrongStoplistSource::class);
        $this->expectExceptionMessage('wrong');

        new Rake('wrong');
    }
}
