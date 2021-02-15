<?php

abstract class TFExcercise
{

    abstract function info();

}

class DataStorageManager extends TFExcercise
{

    private string $data;

    public function __construct(string $file_path)
    {
        $word = fread(fopen($file_path, "r"), filesize($file_path));
        $this->data = mb_strtolower(preg_replace("/[\W_]+/", " ", $word));
    }

    function words(): array
    {
        return explode(" ", $this->data);
    }

    function info()
    {

    }

}

class StopWordManager extends TFExcercise
{

    private array $stop_words;

    public function __construct()
    {
        $stop_words_string = fgets(fopen("../stop_words.txt", "r"));
        $this->stop_words = explode(",", $stop_words_string);

        for ($i = 97; $i < 123; $i++) {
            array_push($this->stop_words, chr($i));
        }

    }

    public function isStopWord(string $word): bool
    {
        return in_array($word, $this->stop_words);
    }

    function info()
    {
        // TODO: Implement info() method.
    }

}

class WordFrequencyManager extends TFExcercise
{

    private array $word_freq;

    public function __construct()
    {
        $this->word_freq = [];
    }

    public function increment_count(string $word): void
    {
        if (array_key_exists($word, $this->word_freq)) {
            $this->word_freq[$word]++;
        } else {
            $this->word_freq[$word] = 1;
        }
    }

    public function sorted(): array
    {
        arsort($this->word_freq);
        return $this->word_freq;
    }

    function info()
    {
        // TODO: Implement info() method.
    }

}

class WordFrequencyController extends TFExcercise
{

    private DataStorageManager $data_storage_manager;
    private StopWordManager  $stop_word_manager;
    private WordFrequencyManager  $word_frequency_manager;

    public function __construct(string $file_path)
    {
        $this->data_storage_manager = new DataStorageManager($file_path);
        $this->stop_word_manager = new StopWordManager();
        $this->word_frequency_manager = new WordFrequencyManager();
    }

    public function run()
    {
        foreach ($this->data_storage_manager->words() as $word) {
            if (!$this->stop_word_manager->isStopWord($word)) {
                $this->word_frequency_manager->increment_count($word);
            }
        }

        $word_freq = array_slice($this->word_frequency_manager->sorted(), 0, 25);

        foreach ($word_freq as $k => $v) {
            echo "$k - $v" . PHP_EOL;
        }
    }

    function info()
    {
        // TODO: Implement info() method.
    }

}

$word_frequency_controller = new WordFrequencyController($argv[1]);
$word_frequency_controller->run();