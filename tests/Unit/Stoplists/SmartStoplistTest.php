<?php

namespace Kudashevs\RakePhp\Tests\Unit\Stoplists;

use Kudashevs\RakePhp\Stoplists\SmartStoplist;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SmartStoplistTest extends TestCase
{
    #[Test]
    public function it_can_return_words(): void
    {
        $instance = new SmartStoplist();

        $listOfStopWords = $instance->getWords();

        $this->assertNotEmpty($listOfStopWords);
    }
}
