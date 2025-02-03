<?php

declare(strict_types=1);

namespace Kudashevs\RakePhp\Normalizers;

class ExclusionsNormalizer extends Normalizer
{
    /**
     * @inheritDoc
     */
    public function prepare(array $words): array
    {
        return $this->cleanUp($words);
    }
}
