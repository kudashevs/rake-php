<?php

declare(strict_types=1);

namespace Kudashevs\RakePhp\Preparers;

class ExclusionsPreparer extends Preparer
{
    /**
     * @inheritDoc
     */
    public function prepare(array $words): array
    {
        return $this->cleanUp($words);
    }
}
