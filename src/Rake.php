<?php

declare(strict_types=1);

namespace Kudashevs\RakePhp;

use Kudashevs\RakePhp\Exceptions\WrongStoplistSource;

class Rake
{
    private string $stopWordsRegex;

    /**
     * @param string $stoplist Path to the file with stop words
     */
    function __construct(string $stoplist = __DIR__ . '/StopLists/SmartStoplist.txt')
    {
        $this->stopWordsRegex = $this->buildStopWordsRegex($stoplist);
    }

    /**
     * Extract key phrases from input text
     *
     * @param string $text Input text
     */
    public function extract($text): array
    {
        $phrases = $this->splitIntoPhrases($text);
        $keywordCandidates = $this->extractCandidateKeywords($phrases);
        $keywordScores = $this->calculateWordScores($keywordCandidates);

        $extractedKeywords = $this->generateExtractedKeywords($keywordCandidates, $keywordScores);

        arsort($extractedKeywords);

        return $extractedKeywords;
    }

    /**
     * @param string $text Text to be split into phrases
     * @return array Array of phrases
     */
    protected function splitIntoPhrases(string $text): array
    {
        return preg_split('/[.!?,;:\t\\\"\(\)\x{2018}\x{2019}\x{2013}]|\s\-\s/u', $text);
    }

    /**
     * Split phrases into of contiguous words. Words within a sequence are assigned
     * the same position in the text and together are considered a candidate keyword)
     * For more information @see 1.2.1 Candidate keywords.
     *
     * @param array $phrases Array of phrases
     * @return array Array of candidates
     */
    private function extractCandidateKeywords(array $phrases): array
    {
        $candidates = [];
        foreach ($phrases as $phrase) {
            $sequences = explode('|', preg_replace($this->stopWordsRegex, '|', $phrase));

            foreach ($sequences as $sequence) {
                $sequence = strtolower(trim($sequence));

                if ($sequence !== '') {
                    $candidates[] = $sequence;
                }
            }
        }

        return $candidates;
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
    private function calculateWordScores(array $candidates): array
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
        $words_temp = preg_split('/[^a-zA-Z0-9_+\-\/]/u', $text, -1);

        return array_filter($words_temp, function ($word) {
            return $word !== '' && !(is_numeric($word));
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

    /**
     * Retrieves stop words and generates a stop words regex.
     *
     * @throws WrongStoplistSource
     */
    private function buildStopWordsRegex(string $stoplist): string
    {
        $rawStopWords = $this->loadStopWords($stoplist);

        $preparedStopWords = array_map(function ($word) {
            return '\b' . $word . '\b';
        }, $rawStopWords);

        return '/' . implode('|', $preparedStopWords) . '/i';
    }

    /**
     * Load stop words from a provided source.
     *
     * @throws WrongStoplistSource
     */
    private function loadStopWords(string $stoplist): array
    {
        if (!file_exists($stoplist)) {
            throw new WrongStoplistSource('Error: cannot read the file: ' . $stoplist);
        }

        $rawStopWords = @file($stoplist, FILE_IGNORE_NEW_LINES) ?: [];

        return array_filter($rawStopWords, function ($line) {
            return $line[0] !== '#';
        });
    }
}
