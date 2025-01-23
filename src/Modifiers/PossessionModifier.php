<?php

declare(strict_types=1);

namespace Kudashevs\RakePhp\Modifiers;

/**
 * A PossessionModifier removes an apostrophe from nouns.
 */
class PossessionModifier implements Modifier
{
    /**
     * @inheritDoc
     */
    public function modify(array $sequences): array
    {
        return array_map(function ($sequence) {
            /**
             * The expression removes the possessive 's from a middle of a sequence (for cases like "Is that Olivia’s bag?")
             * and from the end of a sequence(for cases like "it’s Sandra’s" or "a brother of Maria’s".)
             * For more information @see https://dictionary.cambridge.org/grammar/british-grammar/possession-john-s-car-a-friend-of-mine
             */
            return preg_replace('/\'(s)?(\s+|$)/iS', '${2}', $sequence);
        }, $sequences);
    }
}
