<?php

declare(strict_types=1);

namespace Kudashevs\RakePhp;

use Kudashevs\RakePhp\Exceptions\WrongFileException;

class Rake
{
    private string $stopwords_pattern;

    /**
     * Build a stop words pattern from a provided file.
     *
     * @param string $stopwords_path Path to the file with stop words
     */
    function __construct(string $stopwords_path = __DIR__ . '/StopLists/stoplist_smart.txt')
    {
        $this->stopwords_pattern = $this->build_stopwords_regex($stopwords_path);
    }

    /**
     * Extract key phrases from input text
     *
     * @param string $text Input text
     */
    public function extract($text)
    {
        $sentences = $this->split_sentences($text);
        $phrases = $this->generate_candidate_keywords($sentences);
        $scores = $this->calculate_word_scores($phrases);
        $keywords = $this->get_keywords($phrases, $scores);
        arsort($keywords);

        return $keywords;
    }

    /**
     * @param string $text Text to be splitted into sentences
     */
    protected function split_sentences($text)
    {
        return preg_split('/[.!?,;:\t\\\"\(\)\x{2018}\x{2019}\x{2013}]|\s+\-\s+/u', $text);
    }

    /**
     * @param string $phrase Phrase to be splitted into words
     */
    public static function split_phrase($phrase)
    {
        // @note check python implementation for '0123456789'
        $words_temp = str_word_count($phrase, 1);

        return array_filter($words_temp, function ($word) {
            return $word !== '' && !(is_numeric($word));
        });
    }

    /**
     * Split sentences into phrases by loaded stop words
     *
     * @param array $sentences Array of sentences
     */
    private function generate_candidate_keywords($sentences)
    {
        $phrases_arr = [];
        foreach ($sentences as $s) {
            $phrases_temp = preg_replace($this->stopwords_pattern, '|', $s);
            $phrases = explode('|', $phrases_temp);

            foreach ($phrases as $p) {
                $p = strtolower(trim($p));

                if ($p !== '') {
                    $phrases_arr[] = $p;
                }
            }
        }

        return $phrases_arr;
    }

    /**
     * Calculate score for each word
     *
     * @param array $phrases Array containing individual phrases
     */
    private function calculate_word_scores($phrases)
    {
        $frequencies = [];
        $degrees = [];

        foreach ($phrases as $p) {
            $words = self::split_phrase($p);
            $words_count = count($words);
            $words_degree = $words_count - 1;

            foreach ($words as $w) {
                $frequencies[$w] = (isset($frequencies[$w])) ? $frequencies[$w] : 0;
                $frequencies[$w] += 1;
                $degrees[$w] = (isset($degrees[$w])) ? $degrees[$w] : 0;
                $degrees[$w] += $words_degree;
            }
        }

        foreach ($frequencies as $word => $freq) {
            $degrees[$word] += $freq;
        }

        $scores = [];

        foreach ($frequencies as $word => $freq) {
            $scores[$word] = (isset($scores[$word])) ? $scores[$word] : 0;
            $scores[$word] = $degrees[$word] / (float)$freq;
        }

        return $scores;
    }

    /**
     * Calculate score for each phrase by words scores
     *
     * @param array $phrases Array of phrases (optimally) returned by get_phrases() method
     * @param array $scores Array of words and their scores returned by get_scores() method
     */
    private function get_keywords($phrases, $scores)
    {
        $keywords = [];

        foreach ($phrases as $p) {
            $keywords[$p] = (isset($keywords[$p])) ? $keywords[$p] : 0;
            $words = self::split_phrase($p);
            $score = 0;

            foreach ($words as $w) {
                $score += $scores[$w];
            }

            $keywords[$p] = $score;
        }

        return $keywords;
    }

    /**
     * Retrieves stop words and genarates a regex containing each stop word
     */
    private function build_stopwords_regex(string $stopwords_path): string
    {
        $stopwords = $this->load_stopwords($stopwords_path);

        $prepared_stopwords = array_map(function ($stopword) {
            return '\b' . $stopword . '\b';
        }, $stopwords);

        return '/' . implode('|', $prepared_stopwords) . '/i';
    }

    /**
     * Load stop words from an input file
     */
    private function load_stopwords(string $stopwords_path): array
    {
        if (!file_exists($stopwords_path)) {
            throw new WrongFileException('Error: could not read file: ' . $stopwords_path);
        }

        $raw_stopwords = @file($stopwords_path, FILE_IGNORE_NEW_LINES) ?: [];

        return array_filter($raw_stopwords, function ($line) {
            return $line[0] !== '#';
        });
    }
}


