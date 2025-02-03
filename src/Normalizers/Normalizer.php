<?php

declare(strict_types=1);

namespace Kudashevs\RakePhp\Normalizers;

/**
 * Normalizer represents an abstraction that prepare lists of words for usage.
 */
abstract class Normalizer
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
        $validWords = $this->extractValid($words);

        return $this->removeEmpty($validWords);
    }

    /**
     * Remove unwanted words.
     *
     * @param array<array-key, string> $words
     * @return array<array-key, string>
     */
    protected function extractValid(array $words): array
    {
        return array_filter($words, function ($word) {
            return is_string($word);
        });
    }

    /**
     * Remove empty words.
     *
     * @param array<array-key, string> $words
     * @return array<array-key, string>
     */
    protected function removeEmpty(array $words): array
    {
        return array_filter($words, function ($word) {
            return !empty(trim($word));
        });
    }

    /**
     * @param array<array-key, string> $words
     * @return array<array-key, string>
     */
    protected function unfoldWords(array $words): array
    {
        return array_reduce($words, function ($words, $word) {
            array_push($words, ...$this->unfoldWordFrom($word));
            return $words;
        }, []);
    }

    /**
     * @return array<array-key, string>
     */
    protected function unfoldWordFrom(string $expression): array
    {
        if (preg_match('/\.[.+]\(\w+\)/', $expression, $matches)) {
            return [$expression];
        }

        if (preg_match('/(?P<word>\w+)\((?P<ends>[a-z0-9|]+)\)/iSu', $expression, $matches)) {
            $ends = explode('|', $matches['ends']);

            $word = $matches['word'];
            $derivatives = array_map(function ($end) use ($word) {
                return $word . $end;
            }, $ends);

            return [$word, ...$derivatives];
        }

        if (preg_match('/\((?P<words>[a-z0-9|]+)\)/iSu', $expression, $matches)) {
            $words = explode('|', $matches['words']);

            return [...$words];
        }

        return [preg_quote($expression, '/')];
    }
}
