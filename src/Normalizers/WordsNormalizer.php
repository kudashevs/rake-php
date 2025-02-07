<?php

declare(strict_types=1);

namespace Kudashevs\RakePhp\Normalizers;

class WordsNormalizer extends Normalizer
{
    /**
     * @inheritDoc
     */
    public function prepare(array $words): array
    {
        $cleanWords = $this->cleanUp($words);

        return array_unique($cleanWords);
    }
}
