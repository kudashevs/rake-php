<?php

declare(strict_types=1);

namespace Kudashevs\RakePhp\Normalizers;

class InclusionsNormalizer extends Normalizer
{
    /**
     * @inheritDoc
     */
    public function prepare(array $words): array
    {
        $validWords = $this->extractValid($words);
        $unfoldWords = $this->unfoldWords($validWords);
        $cleanWords = $this->removeEmpty($unfoldWords);

        return array_unique($cleanWords);
    }
}
