<?php

namespace Kudashevs\RakePhp\Tests\Unit\Sorters;

use Kudashevs\RakePhp\Sorters\Order;
use Kudashevs\RakePhp\Sorters\ScoreSorter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ScoreSorterTest extends TestCase
{
    #[Test]
    public function it_sorts_in_descending_order_by_default(): void
    {
        $output = [
            'first' => 1,
            'second' => 2,
            'third' => 3,
        ];

        $sorter = new ScoreSorter();

        $sorted = $sorter->sort($output);

        $this->assertSame(3, current($sorted));
    }

    #[Test]
    public function it_can_sort_in_descending_order(): void
    {
        $output = [
            'first' => 1,
            'second' => 2,
            'third' => 3,
        ];

        $sorter = new ScoreSorter(Order::DESC);

        $sorted = $sorter->sort($output);

        $this->assertSame(3, current($sorted));
    }

    #[Test]
    public function it_can_sort_in_ascending_order(): void
    {
        $output = [
            'first' => 1,
            'second' => 2,
            'third' => 3,
        ];

        $sorter = new ScoreSorter(Order::ASC);

        $sorted = $sorter->sort($output);

        $this->assertSame(1, current($sorted));
    }
}
