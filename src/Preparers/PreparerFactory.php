<?php

declare(strict_types=1);

namespace Kudashevs\RakePhp\Preparers;

use Kudashevs\RakePhp\Exceptions\InvalidPreparerCase;

class PreparerFactory
{
    public function for(string $case): Preparer
    {
        switch ($case) {
            case 'words':
                return new WordsPreparer();

            case 'exclusions':
                return new ExclusionsPreparer();

            case 'inclusions':
                return new InclusionsPreparer();

            default:
                throw new InvalidPreparerCase(
                    sprintf('An unknown preparer case %s provided.', $case)
                );
        }
    }
}
