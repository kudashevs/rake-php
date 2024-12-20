<?php

declare(strict_types=1);

namespace Kudashevs\RakePort;

class Port
{
    protected string $stop_words_file_path;

    protected string $stop_words_pattern;

    /**
     * @param string $stop_words_file_path Path to the file with stop words.
     */
    function __construct(string $stop_words_file_path = __DIR__ . '/resources/SmartStoplist.txt')
    {
        $this->stop_words_file_path = $stop_words_file_path;
        $this->stop_words_pattern = $this->build_stop_word_regex($stop_words_file_path);
    }

    /**
     * Builds a regex expression to match any of the stop word.
     *
     * Keyword arguments:
     * stop_words_file_path -- filepath of a file containing stop words
     */
    private function build_stop_word_regex(string $stop_words_file_path): string
    {
        $stop_words_list = $this->load_stop_words($stop_words_file_path);

        $stop_words_regex_list = [];
        foreach ($stop_words_list as $word) {
            $word_regex = '\b' . $word . '\b';
            $stop_words_regex_list[] = $word_regex;
        }

        return '/' . implode('|', $stop_words_regex_list) . '/i';
    }

    /**
     * Loads stop words from a file and return as a list of words.
     *
     * Keyword arguments:
     * stop_words_file_path -- filepath of a file containing stop words
     */
    private function load_stop_words(string $stop_words_file_path): array
    {
        $stop_words = [];

        $file = @fopen($stop_words_file_path, 'r');
        while (($line = fgets($file)) !== false) {
            $line = trim($line);

            if ($line[0] !== '#') {
                $stop_words[] = $line;
            }
        }
        fclose($file);

        return $stop_words;
    }

    /**
     * @param string $text
     * @return array
     */
    public function exec(string $text): array
    {
        $sentences = $this->split_sentences($text);
        $phrases = $this->generate_candidate_keywords($sentences);
        $scores = $this->calculate_word_scores($phrases);
        $keyword_candidates = $this->generate_candidate_keyword_scores($phrases, $scores);
        arsort($keyword_candidates);

        return $keyword_candidates;
    }

    /**
     * Split text into sentences.
     */
    protected function split_sentences(string $text): array
    {
        return preg_split('/[.!?,;:\t\\\"\(\)\x{2018}\x{2019}\x{2013}]|\s\-\s/u', $text);
    }

    /**
     * Returns keyword phrases after removing stopwords from each sentence.
     */
    private function generate_candidate_keywords($sentences): array
    {
        $phrases_list = [];
        foreach ($sentences as $sentence) {
            $phrases = explode(
                '|',
                preg_replace($this->stop_words_pattern, '|', trim($sentence))
            );

            foreach ($phrases as $phrase) {
                $phrase = strtolower(trim($phrase));

                if ($phrase !== '') {
                    $phrases_list[] = $phrase;
                }
            }
        }

        return $phrases_list;
    }

    /**
     * Return a list of all words of length greater than specified min size.
     *
     * Keyword arguments:
     * text -- the text that is to be split into words
     * word_min_size -- the min. no. of characters a word must have (def: 0)
     */
    protected function separate_words(string $string, int $word_min_size = 0): array
    {
        $words_temp = preg_split('/[^a-zA-Z0-9_+\-\/]/u', $string);

        $words = [];
        foreach ($words_temp as $word) {
            $current_word = strtolower(trim($word));
            if (
                strlen($current_word) > $word_min_size // @note this word_min_size is not used anywhere
                && $current_word !== ''
                && !(is_numeric($current_word))
            ) {
                $words[] = $word;
            }
        }

        return $words;
    }


    /**
     * Calculates the word score for all the words in the phrases.
     */
    private function calculate_word_scores(array $phrases): array
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
     * Returns the dict. of candidate keywords with scores.
     */
    private function generate_candidate_keyword_scores(array $phrases, array $scores): array
    {
        $keyword_candidates = [];
        foreach ($phrases as $phrase) {
            $keyword_candidates[$phrase] = $keyword_candidates[$phrase] ?? 0;
            $words = $this->separate_words($phrase);
            $candidate_score = 0;

            foreach ($words as $word) {
                $candidate_score += $scores[$word];
            }

            $keyword_candidates[$phrase] = $candidate_score;
        }

        return $keyword_candidates;
    }


}
