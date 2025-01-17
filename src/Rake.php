<?php

declare(strict_types=1);

namespace Kudashevs\RakePhp;

use InvalidArgumentException;
use Kudashevs\RakePhp\Exceptions\InvalidOptionType;
use Kudashevs\RakePhp\Exceptions\WrongStoplistSource;
use Kudashevs\RakePhp\Stoplists\SmartStoplist;
use Kudashevs\RakePhp\Stoplists\Stoplist;

class Rake
{
    protected const DEFAULT_STOPLIST = SmartStoplist::class;

    protected const DEFAULT_STOP_WORDS_REPLACEMENT = '|';

    protected Stoplist $stoplist;

    protected readonly string $stopWordsRegex;

    /**
     * 'stoplist' string A default file with stop words.
     * 'exclude' array An array of stop words exclusions.
     * 'include' array An array of stop words inclusions.
     *
     * @var array{
     *     stoplist: string,
     *     exclude: array<array-key, string>,
     *     include: array<array-key, string>,
     * }
     */
    protected array $options = [
        'exclude' => [],
        'include' => [],
    ];

    /**
     * 'stoplist' string A valid file with a list of stop words (stoplist).
     * 'exclude' array An array of words that should be excluded from the stoplist.
     * 'include' array An array of words that should be included in the stoplist.
     *
     * @param array{
     *     stoplist: string,
     *     exclude: array<array-key, string>,
     *     include: array<array-key, string>,
     * } $options
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $options = [])
    {
        $this->initOptions($options);
        $this->initStoplist($options);

        $this->initStopWordsRegex();
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function initOptions(array $options): void
    {
        $this->validateOptions($options);

        $this->options = array_merge($this->options, $options);
    }

    protected function initStoplist(array $options): void
    {
        $this->stoplist = $options['stoplist'] ?? new (self::DEFAULT_STOPLIST)();
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function validateOptions(array $options): void
    {
        if (isset($options['exclude']) && !is_array($options['exclude'])) {
            throw new InvalidOptionType('The exclude option must be an array');
        }

        if (isset($options['include']) && !is_array($options['include'])) {
            throw new InvalidOptionType('The include option must be an array');
        }

        if (isset($options['stoplist']) && !$options['stoplist'] instanceof Stoplist) {
            throw new WrongStoplistSource('The stoplist option must be of type Stoplist.');
        }
    }

    protected function initStopWordsRegex(): void
    {
        $this->stopWordsRegex = $this->buildStopWordsRegex();
    }

    /**
     * Retrieves stop words and generates a stop words regex.
     *
     * @throws WrongStoplistSource
     */
    protected function buildStopWordsRegex(): string
    {
        $preparedStopWords = $this->prepareStopWords(
            $this->stoplist->getWords()
        );

        $bounderizedStopWords = array_map(function ($word) {
            return '\b' . $word . '\b';
        }, $preparedStopWords);

        $regex = implode('|', $bounderizedStopWords);

        return '/' . $regex . '/iSU';
    }

    /**
     * The preparation process includes the following steps:
     * - apply exclusions to the list of stop words
     * - apply inclusions to the list of stop words
     *
     * @param array $words
     * @return void
     */
    private function prepareStopWords(array $rawWords): array
    {
        $words = $this->prepareWords($rawWords);
        $exclusions = $this->getPreparedExclusions();
        $inclusions = $this->getPreparedInclusions();

        $withoutExclusions = array_diff($words, $exclusions);

        return array_merge($withoutExclusions, $inclusions);
    }

    protected function prepareWords(array $words): array
    {
        return $this->prepareWordsForStoplist($words);
    }

    protected function getPreparedExclusions(): array
    {
        return $this->prepareWordsForStoplist($this->options['exclude']);
    }

    protected function getPreparedInclusions(): array
    {
        return $this->prepareWordsForStoplist($this->options['include']);
    }

    protected function prepareWordsForStoplist(array $words): array
    {
        return array_filter($words, function ($word) {
            return is_string($word) && !preg_match('/^\s+$/i', $word);
        });
    }

    /**
     * Apply the RAKE (Rapid Automatic Keyword Extraction) algorithm
     * to a text and return a list of results in the keyword => score format.
     *
     * @param string $text Input text
     * @return array An array of the RAKE algorithm result
     */
    public function extract(string $text): array
    {
        $keywordCandidates = $this->extractCandidateKeywords($text);
        $keywordScores = $this->calculateWordScores($keywordCandidates);

        $extractedKeywords = $this->generateExtractedKeywords($keywordCandidates, $keywordScores);

        arsort($extractedKeywords);

        return $extractedKeywords;
    }

    /**
     * Apply the RAKE (Rapid Automatic Keyword Extraction) algorithm
     * to a provided text and return a list of found keywords only.
     *
     * @param string $text
     * @return array
     */
    public function extractWords(string $text): array
    {
        $result = $this->extract($text);

        return array_keys($result);
    }

    /**
     * Apply the RAKE (Rapid Automatic Keyword Extraction) algorithm
     * to a provided text and return a list of calculated scores only.
     *
     * @param string $text
     * @return array
     */
    public function extractScores(string $text): array
    {
        $result = $this->extract($text);

        return array_values($result);
    }

    /**
     * Split phrases into of contiguous words. Words within a sequence are assigned
     * the same position in the text and together are considered a candidate keyword)
     * For more information @see 1.2.1 Candidate keywords.
     *
     * @param string $text Input text
     * @return array Array of candidates
     */
    protected function extractCandidateKeywords(string $text): array
    {
        $preprocessed = $this->preprocessText($text);
        $sequences = $this->splitIntoSequences($preprocessed);

        $candidates = [];
        foreach ($sequences as $sequence) {
            $sequence = strtolower(trim($sequence));

            if ($sequence !== '') {
                $candidates[] = $sequence;
            }
        }

        return $candidates;
    }

    /**
     * The pre-processing includes the following steps:
     * - replace new lines with spaces
     * - replace stop words with a replacement
     *
     * @param string $text
     * @return string
     */
    protected function preprocessText(string $text): string
    {
        $textWithoutNewLines = preg_replace('/\R/', ' ', $text);

        $textWithoutStopWords = preg_replace(
            $this->stopWordsRegex,
            self::DEFAULT_STOP_WORDS_REPLACEMENT,
            $textWithoutNewLines
        );

        return str_replace([' - '], self::DEFAULT_STOP_WORDS_REPLACEMENT, $textWithoutStopWords);
    }

    /**
     * @param string $text Text to be split into phrases
     * @return array Array of phrases
     */
    protected function splitIntoSequences(string $text): array
    {
        return preg_split(
            '/[.!?,;:()\t\\\"\x{2018}\x{2019}\x{2013}' . self::DEFAULT_STOP_WORDS_REPLACEMENT . ']/uS',
            $text,
            -1,
            PREG_SPLIT_NO_EMPTY,
        );
    }

    /**
     * Calculate score for each candidate. After every candidate keyword is identified
     * and the graph of word co-occurrences is complete, a score is calculated  for each
     * candidate keyword and defined as the sum of its member word scores.
     * For more information @see 1.2.2 Keyword scores
     *
     * @param array $candidates Array of candidates
     * @return array Array of scores
     */
    protected function calculateWordScores(array $candidates): array
    {
        $wordFrequencies = [];
        $wordDegrees = [];

        foreach ($candidates as $candidate) {
            $words = $this->splitIntoWords($candidate);
            $wordsTotalDegree = count($words) - 1;

            foreach ($words as $word) {
                $wordFrequencies[$word] = $wordFrequencies[$word] ?? 0;
                $wordFrequencies[$word] += 1;
                $wordDegrees[$word] = $wordDegrees[$word] ?? 0;
                $wordDegrees[$word] += $wordsTotalDegree;
            }
        }

        foreach ($wordFrequencies as $word => $freq) {
            $wordDegrees[$word] += $freq;
        }

        $scores = [];
        foreach ($wordFrequencies as $word => $freq) {
            $scores[$word] = $scores[$word] ?? 0;
            $scores[$word] = $wordDegrees[$word] / (float)$freq;
        }

        return $scores;
    }

    /**
     * @param string $text Text to be split into words
     * @return array Array of words
     */
    protected function splitIntoWords(string $text): array
    {
        $words_temp = preg_split('/[^a-zA-Z0-9_+\-\/]/uS', $text, -1, PREG_SPLIT_NO_EMPTY);

        return array_filter($words_temp, function ($word) {
            return !(is_numeric($word));
        });
    }

    /**
     * Generate extracted keywords by combining each candidate with its score.
     *
     * @param array $candidates Array of keyword candidates
     * @param array $scores Array of candidates' scores
     * @return array Array of extracted keywords
     */
    private function generateExtractedKeywords(array $candidates, array $scores): array
    {
        $extractedKeywords = [];
        foreach ($candidates as $candidate) {
            $extractedKeywords[$candidate] = $extractedKeywords[$candidate] ?? 0;
            $words = $this->splitIntoWords($candidate);
            $score = 0;

            foreach ($words as $word) {
                $score += $scores[$word];
            }

            $extractedKeywords[$candidate] = $score;
        }

        return $extractedKeywords;
    }
}
