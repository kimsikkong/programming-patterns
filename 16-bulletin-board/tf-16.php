<?php

class EventManager {

    private array $subscription = [];

    public function subscribe(string $event_type, callable $handler)
    {
        if (array_key_exists($event_type, $this->subscription)) {
            $this->subscription[$event_type][] = $handler;
        } else {
            $this->subscription[$event_type][] = $handler;
        }
    }

    public function publish(array $event)
    {
        $event_type = $event[0];

        if (array_key_exists($event_type, $this->subscription)) {
            foreach ($this->subscription[$event_type] as $f) {
                $f($event);
            }
        }
    }

}

class DataStorage {

    private EventManager $event_manager;
    private array $words;

    public function __construct(EventManager $event_manager)
    {
        $this->event_manager = $event_manager;
        $this->event_manager->subscribe('load', $this->load());
        $this->event_manager->subscribe('start', $this->produceWords());
    }

    private function load()
    {
        return function(array $event) {
            $file_path = $event[1];
            $word_string = mb_strtolower(fread(fopen($file_path, 'r'), filesize($file_path)));
            $this->words = explode(" ", preg_replace("/[\W_]+/", " ", $word_string));
        };
    }

    private function produceWords()
    {
        return function() {
            foreach ($this->words as $word) {
                $this->event_manager->publish(['valid_word', $word]);
            }

            $this->event_manager->publish(['stop']);
        };
    }
}

class StopWordFilter {

    private EventManager $event_manager;
    private array $stop_words;

    public function __construct(EventManager $event_manager)
    {
        $this->event_manager = $event_manager;
        $this->event_manager->subscribe('load', $this->load());
        $this->event_manager->subscribe('valid_word', $this->isStopWord());
    }

    private function load()
    {
        return function() {
          $this->stop_words = explode(",", fgets(fopen('../stop_words.txt', 'r')));

          for ($i = 97; $i < 123; $i++) {
              $this->stop_words[] = chr($i);
          }
        };
    }

    private function isStopWord()
    {
        return function(array $event) {
            $word = $event[1];
            if (!in_array($word, $this->stop_words)) {
                $this->event_manager->publish(['count_word', $word]);
            }
        };
    }

}

class WordFrequencyCounter {

    private EventManager $event_manager;
    private array $word_freq = [];

    public function __construct(EventManager $event_manager)
    {
        $this->event_manager = $event_manager;
        $this->event_manager->subscribe('count_word', $this->incrementCount());
        $this->event_manager->subscribe('print', $this->print());
    }

    private function incrementCount()
    {
        return function(array $event) {
            $word = $event[1];
            if (array_key_exists($word, $this->word_freq)) {
                $this->word_freq[$word]++;
            } else {
                $this->word_freq[$word] = 1;
            }
        };
    }

    private function print()
    {
        return function() {
            arsort($this->word_freq);
            $array = array_slice($this->word_freq, 0, 25);

            foreach ($array as $key => $value) {
                echo "$key - $value" . PHP_EOL;
            }
        };
    }

}

class WordFrequencyApplication {

    private EventManager $event_manager;

    public function __construct(EventManager $event_manager)
    {
        $this->event_manager = $event_manager;
        $this->event_manager->subscribe('run', $this->run());
        $this->event_manager->subscribe('stop', $this->stop());
    }

    private function run()
    {
        return function(array $event) {
            $file_path = $event[1];
            $this->event_manager->publish(['load', $file_path]);
            $this->event_manager->publish(['start']);
        };
    }

    private function stop()
    {
        return function() {
          $this->event_manager->publish(['print']);
        };
    }

}

$em = new EventManager();
new DataStorage($em);
new StopWordFilter($em);
new WordFrequencyCounter($em);
new WordFrequencyApplication($em);

$em->publish(['run', $argv[1]]);

