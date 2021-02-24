<?php

function extractWord()
{
    return function(string $file_path) {
      $word_str = fread(fopen($file_path, 'r'), filesize($file_path));
      $words = explode(" ", mb_strtolower(preg_replace("/[\W_]+/", " ", $word_str)));
      $stop_words = explode(",", fgets(fopen("../stop_words.txt", 'r')));
      for ($i = 97; $i < 123; $i++) { $stop_words[] = chr($i); }
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

function profileWrapper(callable $f)
{
    return function($args) use ($f) {
      $start_time = get_time();
      $ret = $f($args);
      $end_time = get_time();
      $time = $end_time - $start_time;
      echo number_format($time, 6) . " 초 걸림" . PHP_EOL;
      return $ret;
    };
}

function get_time() { $t=explode(' ',microtime()); return (float)$t[0]+(float)$t[1]; }

$f1 = profileWrapper(extractWord());
$f2 = profileWrapper(frequency());
$f3 = profileWrapper(_sort());

$word_freq = $f3($f2($f1($argv[1])));
$word_freq = array_slice($word_freq, 0, 25);

foreach ($word_freq as $k => $v) {
    echo "$k - $v" . PHP_EOL;
}
