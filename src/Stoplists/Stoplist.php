<?php

declare(strict_types=1);

namespace Kudashevs\RakePhp\Stoplists;

/**
 * Stoplist represents an abstraction of a list of stop words (or stoplist).
 */
interface Stoplist
{
    /**
     * @return array<array-key, string>
     */
    public function getWords(): array;
}
