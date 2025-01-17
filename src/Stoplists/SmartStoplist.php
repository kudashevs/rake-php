<?php

declare(strict_types=1);

namespace Kudashevs\RakePhp\Stoplists;

final class SmartStoplist implements Stoplist
{
    private const SMART_STOPLIST_FILE_PATH = __DIR__ . '/SmartStoplist.txt';

    /**
     * @var array<array-key, string>
     */
    private array $words;

    public function __construct()
    {
        $this->initWords();
    }

    private function initWords(): void
    {
        $this->words = @file(self::SMART_STOPLIST_FILE_PATH, FILE_IGNORE_NEW_LINES) ?: [];
    }

    /**
     * @inheritDoc
     */
    public function getWords(): array
    {
        return array_filter($this->words, function ($word) {
            return !str_starts_with($word, '#');
        });
    }
}
