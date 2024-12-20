<?php

declare(strict_types=1);

namespace Kudashevs\RakePhp;

use Kudashevs\RakePhp\Exceptions\WrongFileException;

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
        $keywordScores = $this->calculate_word_scores($keywordCandidates);

        $extracted_keywords = $this->generate_candidate_keyword_scores($keywordCandidates, $keywordScores);

        arsort($extracted_keywords);

        return $extracted_keywords;
    }

    /**
     * @param string $text Text to be splitted into sentences
     */
    protected function splitIntoPhrases($text)
    {
        return preg_split('/[.!?,;:\t\\\"\(\)\x{2018}\x{2019}\x{2013}]|\s\-\s/u', $text);
    }

    /**
     * @param string $phrase Phrase to be splitted into words
     */
    protected function separate_words($phrase)
    {
        $words_temp = preg_split('/[^a-zA-Z0-9_+\-\/]/u', $phrase, -1);

        return array_filter($words_temp, function ($word) {
            return $word !== '' && !(is_numeric($word));
        });
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
     * Calculate score for each word
     *
     * @param array $phrases Array containing individual phrases
     */
    private function calculate_word_scores($phrases)
    {
        $word_frequency = [];
        $word_degree = [];

        foreach ($phrases as $phrase) {
            $words = $this->separate_words($phrase);
            $words_list_degree = count($words) - 1;

            foreach ($words as $word) {
                $word_frequency[$word] = $word_frequency[$word] ?? 0;
                $word_frequency[$word] += 1;
                $word_degree[$word] = $word_degree[$word] ?? 0;
                $word_degree[$word] += $words_list_degree;
            }
        }

        foreach ($word_frequency as $word => $freq) {
            $word_degree[$word] += $freq;
        }

        $scores = [];
        foreach ($word_frequency as $word => $freq) {
            $scores[$word] = $scores[$word] ?? 0;
            $scores[$word] = $word_degree[$word] / (float)$freq;
        }

        return $scores;
    }

    /**
     * Calculate score for each phrase by words scores
     *
     * @param array $phrases Array of phrases (optimally) returned by get_phrases() method
     * @param array $scores Array of words and their scores returned by get_scores() method
     */
    private function generate_candidate_keyword_scores($phrases, $scores)
    {
        $candidates = [];

        foreach ($phrases as $phrase) {
            $candidates[$phrase] = $candidates[$phrase] ?? 0;
            $words = $this->separate_words($phrase);
            $score = 0;

            foreach ($words as $word) {
                $score += $scores[$word];
            }

            $candidates[$phrase] = $score;
        }

        return $candidates;
    }

    /**
     * Retrieves stop words and generates a stop words regex.
     *
     * @throws WrongFileException
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
     * @throws WrongFileException
     */
    private function loadStopWords(string $stoplist): array
    {
        if (!file_exists($stoplist)) {
            throw new WrongFileException('Error: cannot read the file: ' . $stoplist);
        }

        $rawStopWords = @file($stoplist, FILE_IGNORE_NEW_LINES) ?: [];

        return array_filter($rawStopWords, function ($line) {
            return $line[0] !== '#';
        });
    }
}
