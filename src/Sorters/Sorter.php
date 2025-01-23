<?php

declare(strict_types=1);

namespace Kudashevs\RakePhp\Sorters;

/**
 * Sorter represents an abstraction that sorts the output of the algorithm.
 */
interface Sorter
{
    /**
     * Sort the data in different orders.
     *
     * @param array<string, int|float> $data
     * @return array<string, int|float>
     */
    public function sort(array $data): array;
}
