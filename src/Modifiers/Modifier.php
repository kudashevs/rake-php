<?php

declare(strict_types=1);

namespace Kudashevs\RakePhp\Modifiers;

/**
 * Modifier represents an abstraction that can alter or change the output of the algorithm.
 */
interface Modifier
{
    /**
     * Alter or change words in sequences.
     *
     * @param array<array-key, string> $sequences
     * @return array<array-key, string>
     */
    public function modify(array $sequences): array;
}
