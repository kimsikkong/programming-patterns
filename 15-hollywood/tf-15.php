<?php

class WordFrequencyFramework
{

    private array $load_event_handlers = [];
    private array $do_work_event_handlers = [];
    private array $end_event_handlers = [];

    public function registerForLoadEvent(callable $handler): void
    {
        $this->load_event_handlers[] = $handler;
    }

    public function registerForDoWorkEvent(callable $handler): void
    {
        $this->do_work_event_handlers[] = $handler;
    }

    public function registerEndEvent(callable $handler): void
    {
        $this->end_event_handlers[] = $handler;
    }

    public function run(string $file_path): void
    {
        foreach ($this->load_event_handlers as $f) {
            $f($file_path);
        }

        foreach ($this->do_work_event_handlers as $f) {
            $f();
        }

        foreach ($this->end_event_handlers as $f) {
            $f();
        }
    }
}

class DataStorage
{

    private string $data = "";
    private StopWordFilter $stop_word_filter;
    private array $word_event_handler = [];

    public function __construct(WordFrequencyFramework $wordFrequencyFramework, StopWordFilter $stop_word_filter)
    {
        $this->stop_word_filter = $stop_word_filter;
        $wordFrequencyFramework->registerForLoadEvent($this->load());
        $wordFrequencyFramework->registerForDoWorkEvent($this->produceWords());
    }

    private function load(): callable
    {
        return function (string $file_path) {
            $this->data = fread(fopen($file_path, 'r'), filesize($file_path));
            $this->data = mb_strtolower(preg_replace("/[\W_]+/", " ", $this->data));
        };
    }

    private function produceWords(): callable
    {
        return function () {
            foreach (explode(" ", $this->data) as $word) {
                if (!$this->stop_word_filter->isStopWord($word)) {
                    foreach ($this->word_event_handler as $f) {
                        $f($word);
                    }
                }
            }
        };
    }

    public function registerForWorkEvent(callable $handler): void
    {
        $this->word_event_handler[] = $handler;
    }
}

class StopWordFilter
{

    private array $stop_words = [];

    public function __construct(WordFrequencyFramework $word_frequency_framework)
    {
        $word_frequency_framework->registerForLoadEvent($this->load());
    }

    private function load(): callable
    {
        return function () {
            $this->stop_words = explode(",", fgets(fopen("../stop_words.txt", "r")));

            for ($i = 97; $i < 123; $i++) {
                $this->stop_words[] = chr($i);
            }
        };
    }

    public function isStopWord(string $word): bool
    {
        return in_array($word, $this->stop_words);
    }

}

class WordFrequencyCounter
{

    private array $word_freq = [];

    public function __construct(WordFrequencyFramework $word_frequency_framework, DataStorage $data_storage)
    {
        $data_storage->registerForWorkEvent($this->incrementCount());
        $word_frequency_framework->registerEndEvent($this->printFreqs());
    }

    private function incrementCount(): callable
    {
        return function ($word) {
            if (array_key_exists($word, $this->word_freq)) {
                $this->word_freq[$word]++;
            } else {
                $this->word_freq[$word] = 1;
            }
        };
    }

    private function printFreqs(): callable
    {
        return function () {
            arsort($this->word_freq);
            foreach (array_slice($this->word_freq, 0, 25) as $k => $v) {
                echo "$k - $v" . PHP_EOL;
            }
        };
    }

}

$word_frequency_framework = new WordFrequencyFramework();
$stop_word_filter = new StopWordFilter($word_frequency_framework);
$data_storage = new DataStorage($word_frequency_framework, $stop_word_filter);
$word_frequency_counter = new WordFrequencyCounter($word_frequency_framework, $data_storage);
$word_frequency_framework->run($argv[1]);

