<?php

declare(strict_types=1);

namespace Kudashevs\RakePhp\Sorters;

final class ScoreSorter implements Sorter
{
    private Order $order;

    public function __construct($order = Order::DESC)
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
            ? arsort($sorted)
            : asort($sorted);

        return $sorted;
    }
}
