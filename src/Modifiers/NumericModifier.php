<?php

declare(strict_types=1);

namespace Kudashevs\RakePhp\Modifiers;

/**
 * A NumericModifier removes words that consist only of numbers.
 */
class NumericModifier implements Modifier
{
    /**
     * @inheritDoc
     */
    public function modify(array $sequences): array
    {
        $withoutNumeric = array_reduce($sequences, function ($acc, $sequence) {
            return array_merge($acc, $this->splitByNumeric($sequence));
        }, []);

        return array_unique($withoutNumeric);
    }

    protected function splitByNumeric(string $sequence): array
    {
        $parts = preg_split('/\b\d+\b/', $sequence);

        return array_map(function ($part) {
            return trim($part);
        }, $parts);
    }
}
