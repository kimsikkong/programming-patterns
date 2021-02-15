<?php

abstract class TFExcercise
{

    abstract function info();

}

class DataStorageManager extends TFExcercise
{

    private string $data;

    public function dispatch(array $message)
    {
        if ($message[0] === "init") {
            $this->init($message[1]);
        } elseif ($message[0] === "words") {
            return $this->words();
        } else {
            throw new Exception("Message not found {$message[0]}");
        }
    }

    private function init(string $file_path): void
    {
        $word = fread(fopen($file_path, "r"), filesize($file_path));
        $this->data = mb_strtolower(preg_replace("/[\W_]+/", " ", $word));
    }

    private function words(): array
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

    public function dispatch(array $message)
    {
        if ($message[0] === "init") {
            $this->init();
        } elseif ($message[0] === "is_stop_word") {
            return $this->isStopWord($message[1]);
        } else {
            throw new Exception("Message not found {$message[0]}");
        }
    }

    public function init()
    {
        $stop_words_string = fgets(fopen("../stop_words.txt", "r"));
        $this->stop_words = explode(",", $stop_words_string);

        for ($i = 97; $i < 123; $i++) {
            array_push($this->stop_words, chr($i));
        }
    }

    private function isStopWord(string $word): bool
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

    public function dispatch(array $message)
    {
        if ($message[0] === "init") {
            $this->init();
        } elseif ($message[0] === "increment_count") {
            $this->increment_count($message[1]);
        } elseif ($message[0] === "sorted") {
            return $this->sorted();
        } else {
            throw new Exception("Message not found {$message[0]}");
        }
    }

    public function init()
    {
        $this->word_freq = [];
    }

    private function increment_count(string $word): void
    {
        if (array_key_exists($word, $this->word_freq)) {
            $this->word_freq[$word]++;
        } else {
            $this->word_freq[$word] = 1;
        }
    }

    private function sorted(): array
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

    public function dispatch(array $message)
    {
        if ($message[0] === "init") {
            $this->init($message[1]);
        } elseif ($message[0] === "run") {
            $this->run();
        } else {
            throw new Exception("Message not found {$message[0]}");
        }
    }

    public function init(string $file_path)
    {
        $this->data_storage_manager = new DataStorageManager();
        $this->data_storage_manager->dispatch(["init", $file_path]);
        $this->stop_word_manager = new StopWordManager();
        $this->stop_word_manager->dispatch(["init"]);
        $this->word_frequency_manager = new WordFrequencyManager();
        $this->word_frequency_manager->dispatch(["init"]);
    }

    private function run()
    {
        foreach ($this->data_storage_manager->dispatch(['words']) as $word) {
            if (!$this->stop_word_manager->dispatch(['is_stop_word', $word])) {
                $this->word_frequency_manager->dispatch(['increment_count', $word]);
            }
        }

        $word_freq = array_slice($this->word_frequency_manager->dispatch(['sorted']), 0, 25);

        foreach ($word_freq as $k => $v) {
            echo "$k - $v" . PHP_EOL;
        }
    }

    function info()
    {
        // TODO: Implement info() method.
    }

}

$word_frequency_controller = new WordFrequencyController();
$word_frequency_controller->dispatch(['init', $argv[1]]);
$word_frequency_controller->dispatch(['run']);