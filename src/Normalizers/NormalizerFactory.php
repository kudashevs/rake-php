<?php

declare(strict_types=1);

namespace Kudashevs\RakePhp\Normalizers;

use Kudashevs\RakePhp\Exceptions\InvalidNormalizerCase;

class NormalizerFactory
{
    public function for(string $case): Normalizer
    {
        switch ($case) {
            case 'words':
                return new WordsNormalizer();

            case 'exclusions':
                return new ExclusionsNormalizer();

            case 'inclusions':
                return new InclusionsNormalizer();

            default:
                throw new InvalidNormalizerCase(
                    sprintf('An unknown preparer case %s provided.', $case)
                );
        }
    }
}
