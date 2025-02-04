<?php

declare(strict_types=1);

namespace Kudashevs\RakePhp\Sorters;

class WordSorter implements Sorter
{
    private Order $order;

    public function __construct(Order $order = Order::DESC)
    {
        $this->order = $order;
    }

    /**
     * @inheritDoc
     */
    public function sort(array $data): array
    {
        $sorted = $data;

        ($this->order === Order::DESC)
            ? krsort($sorted)
            : ksort($sorted);

        return $sorted;
    }
}
