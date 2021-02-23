<?php

function extractWord()
{
    return function(string $file_path) {
      $word_str = fread(fopen($file_path, 'r'), filesize($file_path));
      $words = explode(" ", mb_strtolower(preg_replace("/[\W_]+/", " ", $word_str)));
      $stop_words = explode(",", fgets(fopen("../stop_words.txt", 'r')));
      return array_filter($words, function($word) use ($stop_words) {
          if (!in_array($word, $stop_words)) {
              return $word;
          }
      });
    };
}


function frequency()
{
    return function(array $word_list) {
        $word_freq = [];

        foreach ($word_list as $word) {
            if (array_key_exists($word, $word_freq)) {
                $word_freq[$word]++;
            } else {
                $word_freq[$word] = 1;
            }
        }

        return $word_freq;
    };
}

function _sort()
{
    return function(array $word_freq) {
        arsort($word_freq);
        return $word_freq;
    };
}

$tracked_functions = [extractWord(), frequency(), _sort()];

// TODO 