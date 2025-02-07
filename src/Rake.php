<?php

declare(strict_types=1);

namespace Kudashevs\RakePhp;

use InvalidArgumentException;
use Kudashevs\RakePhp\Exceptions\InvalidOptionType;
use Kudashevs\RakePhp\Modifiers\Modifier;
use Kudashevs\RakePhp\Normalizers\NormalizerFactory;
use Kudashevs\RakePhp\Sorters\ScoreSorter;
use Kudashevs\RakePhp\Sorters\Sorter;
use Kudashevs\RakePhp\Stoplists\SmartStoplist;
use Kudashevs\RakePhp\Stoplists\Stoplist;

class Rake
{
    /** @var class-string<SmartStoplist> */
    protected const DEFAULT_STOPLIST = SmartStoplist::class;

    /** @var class-string<ScoreSorter> */
    protected const DEFAULT_SORTER = ScoreSorter::class;

    protected const DEFAULT_STOP_WORDS_REPLACEMENT = '|';

    protected NormalizerFactory $factory;

    protected readonly string $stopWordsRegex;

    /** @var array<array-key, Modifier> */
    protected array $modifiers = [];

    protected Sorter $sorter;

    protected Stoplist $stoplist;

    /**
     * 'include' array An array of stop words inclusions.
     * 'exclude' array An array of stop words exclusions.
     *
     * @var array{
     *     include: array<array-key, string>,
     *     exclude: array<array-key, string>,
     * }
     */
    protected array $options = [
        'include' => [],
        'exclude' => [],
    ];

    /**
     * 'modifiers'  string|object|array A string, an instance or an array of Modifiers (@see Modifier::class).
     * 'stoplist'   Stoplist An instance of a Stoplist (@see Stoplist::class).
     * 'include'    array An array of words that should be included in the stoplist.
     * 'exclude'    array An array of words that should be excluded from the stoplist.
     *
     * @param array{
     *     modifiers?: string|Modifier|array<array-key, Modifier>,
     *     sorter?: Sorter,
     *     stoplist?: Stoplist,
     *     include?: array<array-key, string>,
     *     exclude?: array<array-key, string>,
     * } $options
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $options = [])
    {
        $this->initNormalizerFactory();

        $this->initModifiers($options);
        $this->initSorter($options);
        $this->initStoplist($options);
        $this->initOptions($options);

        $this->initStopWordsRegex();
    }

    protected function initNormalizerFactory(): void
    {
        $this->factory = new NormalizerFactory();
    }

    /**
     * @param array{modifiers?: string|Modifier|array<array-key, Modifier>} $options
     *
     * @throws InvalidOptionType
     */
    protected function initModifiers(array $options): void
    {
        $this->validateModifiersOption($options);

        $modifiers = $this->normalizeModifiers($options);

        foreach ($modifiers as $modifier) {
            if (!is_a($modifier, Modifier::class, true)) {
                throw new InvalidOptionType('The modifiers option must contain values of type Modifier only.');
            }

            if (is_string($modifier)) {
                $modifier = new $modifier();
            }

            $this->modifiers[] = $modifier;
        }
    }

    protected function validateModifiersOption(array $options): void
    {
        if (
            isset($options['modifiers'])
            && !is_string($options['modifiers'])
            && !is_object($options['modifiers'])
            && !is_array($options['modifiers'])
        ) {
            throw new InvalidOptionType('The modifiers option must be a string, an instance, or an array.');
        }
    }

    /**
     * @param array{modifiers?: string|Modifier|array<array-key, Modifier>} $options
     * @return array<array-key, string|Modifier>
     */
    protected function normalizeModifiers(array $options): array
    {
        if (!isset($options['modifiers'])) {
            return [];
        }

        return (
            is_string($options['modifiers'])
            || is_object($options['modifiers'])
        )
            ? [$options['modifiers']]
            : $options['modifiers'];
    }

    /**
     * @param array{sorter?: Sorter} $options
     *
     * @throws InvalidOptionType
     */
    protected function initSorter(array $options): void
    {
        $this->validateSorterOption($options);

        $this->sorter = $options['sorter'] ?? new (self::DEFAULT_SORTER)();
    }

    protected function validateSorterOption(array $options): void
    {
        if (isset($options['sorter']) && !$options['sorter'] instanceof Sorter) {
            throw new InvalidOptionType('The sorter option must be of type Sorter.');
        }
    }

    /**
     * @param array{stoplist?: Stoplist} $options
     *
     * @throws InvalidOptionType
     */
    protected function initStoplist(array $options): void
    {
        $this->validateStoplistOption($options);

        $this->stoplist = $options['stoplist'] ?? new (self::DEFAULT_STOPLIST)();
    }

    protected function validateStoplistOption(array $options): void
    {
        if (isset($options['stoplist']) && !$options['stoplist'] instanceof Stoplist) {
            throw new InvalidOptionType('The stoplist option must be of type Stoplist.');
        }
    }

    /**
     * @param array{include?: array<array-key, string>, exclude?: array<array-key, string>} $options
     *
     * @throws InvalidOptionType
     */
    protected function initOptions(array $options): void
    {
        $this->validateIncludeExcludeOption($options);

        $this->options = array_merge($this->options, $options);
    }

    protected function validateIncludeExcludeOption(array $options): void
    {
        if (isset($options['exclude']) && !is_array($options['exclude'])) {
            throw new InvalidOptionType('The exclude option must be an array.');
        }

        if (isset($options['include']) && !is_array($options['include'])) {
            throw new InvalidOptionType('The include option must be an array.');
        }
    }

    protected function initStopWordsRegex(): void
    {
        $this->stopWordsRegex = $this->buildStopWordsRegex();
    }

    /**
     * Generate a stop words regex from a provided stoplist.
     *
     * @return string
     */
    protected function buildStopWordsRegex(): string
    {
        $preparedStopWords = $this->prepareStopWords($this->stoplist->getWords());
        $specialCases = $this->prepareSpecialCases($preparedStopWords);

        return $this->generateStopWordsRegex($preparedStopWords, $specialCases);
    }

    /**
     * The preparation process includes the following steps:
     * - prepare provided words
     * - prepare provided exclusions
     * - prepare provided inclusions
     * - prioritize exclusions over inclusions
     * - apply exclusions to the list of stop words
     * - apply inclusions to the list of stop words
     *
     * @param array<array-key, string> $rawWords
     * @return array<array-key, string>
     */
    protected function prepareStopWords(array $rawWords): array
    {
        $words = $this->prepareWords($rawWords);
        $exclusions = $this->getPreparedExclusions();
        $inclusions = $this->getPreparedInclusions();

        // prioritize exclusions over inclusions
        $inclusions = array_diff($inclusions, $exclusions);

        $wordsWithoutExclusions = array_diff($words, $exclusions);

        return array_merge($wordsWithoutExclusions, $inclusions);
    }

    /**
     * @param array<array-key, string> $words
     * @return array<array-key, string>
     */
    protected function prepareWords(array $words): array
    {
        return $this->factory->for('words')
            ->prepare($words);
    }

    /**
     * @return array<array-key, string>
     */
    protected function getPreparedExclusions(): array
    {
        return $this->factory->for('exclusions')
            ->prepare($this->options['exclude']);
    }

    /**
     * @return array<array-key, string>
     */
    protected function getPreparedInclusions(): array
    {
        return $this->factory->for('inclusions')
            ->prepare($this->options['include']);
    }

    /**
     * Prepare special cases in the lowercase(case) => case format.
     *
     * @return array<string, string>
     */
    protected function prepareSpecialCases(array $words): array
    {
        $exclusions = $this->getPreparedExclusions();
        $unusualCases = array_diff($exclusions, $words);

        return array_reduce($unusualCases, function ($cases, $case) {
            $cases[strtolower($case)] = $case;
            return $cases;
        }, []);
    }

    protected function generateStopWordsRegex(array $stopWords, array $specialCases): string
    {
        $regexParts = array_map(function ($word) use ($specialCases) {
            if (array_key_exists($word, $specialCases)) {
                return '\b(?-i)(?!' . $specialCases[$word] . ')(?i)' . $word . '\b';
            }

            return '\b' . $word . '\b';
        }, $stopWords);

        $regexBody = implode('|', $regexParts);

        return '/' . $regexBody . '/iSU';
    }

    /**
     * Apply the RAKE (Rapid Automatic Keyword Extraction) algorithm
     * to a text and return a list of results in the keyword => score format.
     *
     * @param string $text Input text
     * @return array<string, int|float> An array of the RAKE algorithm result
     */
    public function extract(string $text): array
    {
        $keywordCandidates = $this->extractCandidateKeywords($text);

        /*
         * This step is not a part of the RAKE algorithm. However, we want to make the result more manageable and flexible.
         * To do this, the package introduces a Modifier abstraction that can alter or change the output of the algorithm.
         */
        $keywordCandidates = $this->applyModifiers($keywordCandidates);

        $keywordScores = $this->calculateWordScores($keywordCandidates);

        $extractedKeywords = $this->generateExtractedKeywords($keywordCandidates, $keywordScores);

        return $this->sorter->sort($extractedKeywords);
    }

    /**
     * Apply the RAKE (Rapid Automatic Keyword Extraction) algorithm
     * to a provided text and return a list of found keywords only.
     *
     * @param string $text
     * @return array<array-key, string>
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
     * @return array<array-key, int|float>
     */
    public function extractScores(string $text): array
    {
        $result = $this->extract($text);

        return array_values($result);
    }

    /**
     * Split phrases into of contiguous words. Words within a sequence are assigned
     * the same position in the text and together are considered a candidate keyword
     * For more information @see 1.2.1 Candidate keywords.
     *
     * @param string $text Input text
     * @return array<array-key, string> Array of candidates
     */
    protected function extractCandidateKeywords(string $text): array
    {
        $preprocessed = $this->preprocessText($text);
        $sequences = $this->splitIntoSequences($preprocessed);

        $candidates = [];
        foreach ($sequences as $sequence) {
            $candidate = strtolower(trim($sequence));

            if ($this->isAppropriateCandidate($candidate)) {
                $candidates[] = $candidate;
            }
        }

        return $candidates;
    }

    protected function isAppropriateCandidate(string $candidate): bool
    {
        return $candidate !== '';
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
        $textWithoutNewLines = preg_replace('/\R+/', ' ', $text);

        $textWithoutStopWords = preg_replace(
            $this->stopWordsRegex,
            self::DEFAULT_STOP_WORDS_REPLACEMENT,
            $textWithoutNewLines
        );

        return str_replace([' - '], self::DEFAULT_STOP_WORDS_REPLACEMENT, $textWithoutStopWords);
    }

    /**
     * @param string $text Text to be split into phrases
     * @return array<array-key, string> Array of phrases
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
     * @param array<array-key, string> $sequences
     * @return array<array-key, string>
     */
    protected function applyModifiers(array $sequences): array
    {
        $mutated = $sequences;

        foreach ($this->modifiers as $modifier) {
            $mutated = $modifier->modify($sequences);
        }

        return $this->cleanUpAfterModifications($mutated);
    }

    /**
     * @param array<array-key, string> $sequences
     * @return array<array-key, string>
     */
    protected function cleanUpAfterModifications(array $sequences): array
    {
        return array_reduce($sequences, function ($acc, $sequence) {
            if (!preg_match('/^\s+$/i', $sequence)) {
                $acc[] = trim($sequence);
            }

            return $acc;
        }, []);
    }

    /**
     * Calculate score for each candidate. After every candidate keyword is identified
     * and the graph of word co-occurrences is complete, a score is calculated  for each
     * candidate keyword and defined as the sum of its member word scores.
     * For more information @see 1.2.2 Keyword scores
     *
     * @param array<array-key, string> $candidates Array of candidates
     * @return array<string, int|float> Array of scores
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
     * @return array<array-key, string> Array of words
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
     * @param array<array-key, string> $candidates Array of keyword candidates
     * @param array<string, int|float> $scores Array of candidates' scores
     * @return array<string, int|float> Array of extracted keywords
     */
    protected function generateExtractedKeywords(array $candidates, array $scores): array
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
