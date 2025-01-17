<?php

declare(strict_types=1);

namespace Kudashevs\RakePhp\Stoplists;

/**
 * Stoplist represents an abstraction of a list of stop words (or stoplist).
 *
 * The input parameters for RAKE comprise a list of stop words (or stoplist)
 * For more information @see 1.2 Rapid automatic keyword extraction
 */
interface Stoplist
{
    /**
     * @return array<array-key, string>
     */
    public function getWords(): array;
}
