<?php

declare(strict_types=1);

namespace Kudashevs\RakePhp\Preparers;

/**
 * Preparer represents an abstraction that prepare lists of words for usage.
 */
abstract class Preparer
{
    /**
     * Prepare a list of words.
     *
     * @param array<array-key, string> $words
     * @return array<array-key, string>
     */
    abstract public function prepare(array $words): array;

    /**
     * Remove unwanted and empty words.
     *
     * @param array<array-key, string> $words
     * @return array<array-key, string>
     */
    protected function cleanUp(array $words): array
    {
        return array_filter($words, function ($word) {
            return is_string($word) && !empty(trim($word));
        });
    }
}
