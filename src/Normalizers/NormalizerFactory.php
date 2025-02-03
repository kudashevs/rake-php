<?php

declare(strict_types=1);

namespace Kudashevs\RakePhp\Normalizers;

use Kudashevs\RakePhp\Exceptions\InvalidNormalizerCase;

class NormalizerFactory
{
    public function for(string $case): Normalizer
    {
        return match ($case) {
            'words' => new WordsNormalizer(),
            'exclusions' => new ExclusionsNormalizer(),
            'inclusions' => new InclusionsNormalizer(),

            default => throw new InvalidNormalizerCase(
                sprintf('An unknown preparer case %s provided.', $case)
            ),
        };
    }
}
