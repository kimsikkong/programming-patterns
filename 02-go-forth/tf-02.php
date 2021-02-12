<?php

$stack = [];
$heap = [];

function read_file(&$stack)
{
    $file_name = array_pop($stack);
    $f = fopen($file_name, 'r');
    array_push($stack, fread($f, filesize($file_name)));
    fclose($f);
}

function filter_chars(&$stack)
{
    array_push($stack, mb_strtolower(preg_replace("/[\W_]+/", " ", array_pop($stack))));
}

function scan(&$stack)
{
    array_push($stack, ...explode(" ", array_pop($stack)));
}

function remove_stop_words(&$stack, &$heap)
{
    $f = fopen("../stop_words.txt", "r");
    array_push($stack, explode(",", fgets($f)));
    fclose($f);
    $heap['stop_word'] = array_pop($stack);
    $heap['words'] = [];

    while (count($stack) > 0) {
        if (in_array($stack[count($stack) - 1], $heap['stop_word'])) {
            array_pop($stack);
        } else {
            $heap['words'][] = array_pop($stack);
        }
    }

    array_push($stack, ...$heap['words']);
    unset($heap['words']);
    unset($heap['stop_word']);
}

function count_frequency(&$stack)
{
    $heap['word_freq'] = [];

    while (count($stack) > 0) {
        if (array_key_exists($stack[count($stack) - 1], $heap['word_freq'])) {
            array_push($stack, $heap['word_freq'][$stack[count($stack) - 1]]);
            array_push($stack, 1);
            array_push($stack, array_pop($stack) + array_pop($stack));

        } else {
            array_push($stack, 1);
        }

        $v = array_pop($stack);
        $heap['word_freq'][array_pop($stack)] = $v;
    }

    array_push($stack, $heap['word_freq']);
    unset($heap['word_freq']);
}

function _sort(&$stack)
{
    asort($stack[0]);
}

array_push($stack, $argv[1]);
read_file($stack);
filter_chars($stack);
scan($stack);
remove_stop_words($stack, $heap);
count_frequency($stack);

// TODO improve
_sort($stack);

for ($i = 0; $i < 25; $i++) {
    $k = array_key_last($stack[0]);
    $v = array_pop($stack[0]);
    echo "$k - $v" . PHP_EOL;
}
