<?php

$limit = 3_000;
ini_set('xdebug.max_nesting_level', $limit + 10);
ini_set('memory_limit','512M');

function countFrequency($word_list, $stop_word, &$word_freq)
{
    if (null == $word_list) {
        return;
    }

    $word = $word_list[0];

    if (!in_array($word, $stop_word)) {
        if (array_key_exists($word, $word_freq)) {
            $word_freq[$word]++;
        } else {
            $word_freq[$word] = 1;
        }
    }

    countFrequency(array_slice($word_list, 1), $stop_word, $word_freq);
}

$stop_words = explode(",", fgets(fopen("../stop_words.txt", "r")));
preg_match_all("/[a-z]{2,}/", mb_strtolower(fread(fopen($argv[1], "r"), filesize($argv[1]))), $word_list);
$word_freq = [];

for ($i = 0; $i < count($word_list[0]); $i += $limit) {
    countFrequency(array_slice($word_list[0], $i, $limit), $stop_words, $word_freq);
}

arsort($word_freq);
$word_freq = array_slice($word_freq, 0, 25);

foreach ($word_freq as $k => $v) {
    echo "$k - $v" . PHP_EOL;
}