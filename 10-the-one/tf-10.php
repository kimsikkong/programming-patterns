<?php

class TFTheOne {

    public $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function bind($func) {
        $this->value = $func($this->value);
        return $this;
    }

    public function print() {
        echo $this->value;
    }
}

$read_file = function ($file_path)
{
    return fread(fopen($file_path, 'r'), filesize($file_path));
};

$filter_chars = function ($str_data)
{
    return preg_replace("/[\W_]+/", " ", $str_data);
};

$normalize = function ($str_data)
{
    return mb_strtolower($str_data);
};

$scan = function ($str_data)
{
    return explode(" ", $str_data);
};

$remove_stop_words = function ($word_list) {
    $stop_words = explode(",", fgets(fopen("../stop_words.txt", "r")));
    return array_values(array_filter($word_list, function($word) use ($stop_words) {
        return !in_array($word, $stop_words);
    }));
};

$count_frequency = function ($word_list) {
  $word_freq = [];

  for ($i = 0; $i < count($word_list); $i++) {
      if (array_key_exists($word_list[$i], $word_freq)) {
          $word_freq[$word_list[$i]] += 1;
      } else {
          $word_freq[$word_list[$i]] = 1;
      }
  }

  return $word_freq;
};

$sort = function ($word_freq)
{
    arsort($word_freq);
    return $word_freq;
};

$top_25_frequency = function ($word_freq)
{
  $word_freq = array_slice($word_freq, 0, 25);
  $str = "";

  foreach ($word_freq as $k => $v) {
      $str .= "$k - $v" . PHP_EOL;
  }

  return $str;
};


$t = new TFTheOne($argv[1]);
$t->bind($read_file)
    ->bind($filter_chars)
    ->bind($normalize)
    ->bind($scan)
    ->bind($remove_stop_words)
    ->bind($count_frequency)
    ->bind($sort)
    ->bind($top_25_frequency)
    ->print();
