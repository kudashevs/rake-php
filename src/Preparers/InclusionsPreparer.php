<?php

declare(strict_types=1);

namespace Kudashevs\RakePhp\Preparers;

class InclusionsPreparer extends Preparer
{
    /**
     * @inheritDoc
     */
    public function prepare(array $words): array
    {
        return $this->cleanUp($words);
    }
}
