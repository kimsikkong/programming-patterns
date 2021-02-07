<?php

$f = fopen('../stop_words.txt', 'r');
$data = [explode(',', fgets($f))];

fclose($f);

$data[] = []; // [1] 각 라인
$data[] = null; // [2] 단어 시작 문자 index
$data[] = 0; // [3] 단어 내 문자 index
$data[] = false; // [4] 단어 끝을 확인하는 flag
$data[] = ''; // [5] 해당 단어
$data[] = ''; // [6] 빈도 파일 내 단어
$data[] = 0; // [7] 빈도 파일 내 빈도 수


$input_file = fopen($argv[1], 'r');
$word_freqs = fopen('./word_freqs_php', 'w+');

while (true) {
    $data[1] = fgets($input_file); // 첫줄 읽기

    if ('' === $data[1] or false === $data[1]) {
        break;
    }

    $data[1] = str_split($data[1]);

    foreach ($data[1] as $c) {

        // 단어 시작 확인
        if ($data[2] === null) {
            if (ctype_alnum($c)) {
                $data[2] = $data[3];
            }
        } else {
            // 단어 끝 확인
            if (!ctype_alnum($c)) {
                $data[5] = mb_strtolower(implode("", array_slice($data[1], $data[2], $data[3] - $data[2])));

                // 단어 시작 idx 초기화
                $data[2] = null;

                if (mb_strlen($data[5]) >= 2 and !in_array($data[5], $data[0])) {
                    // 빈도 파일에서 단어 찾았는지 여부
                    $data[4] = false;

                    while (true) {
                        $data[6] = fgets($word_freqs);
                        // 순회하면서 파일 찾아야 함

                        // 파일이 비어있거나 끝까지 찾았을 경우
                        if ("" === $data[6] or false === $data[6]) {
                            break;
                        }

                        $data[6] = explode(",", $data[6]);
                        $data[7] = intval($data[6][1]);
                        $data[6] = trim($data[6][0]);

                        // 빈도 파일 내 단어 찾았을 경우
                        if ($data[5] === $data[6]) {
                            $data[7]++;
                            $data[4] = true;
                            break;
                        }
                    }

                    if (false === $data[4]) {
                        // 못찾았을 경우 write
                        fwrite($word_freqs, sprintf("%20s,%04d\n", $data[5], "1"));
                    } else {
                        // 찾았을 경우, 파일 포인터 이동 후 write
                        fseek($word_freqs, -26, SEEK_CUR);
                        fwrite($word_freqs, sprintf("%20s,%04d\n", $data[5], $data[7]));
                    }
                }
                // 파일 포인트를 맨 처음으로 이동
                fseek($word_freqs, 0);
            }
        }
        $data[3]++;
    }

    // 새로운 줄을 위해 초기화
    $data[2] = null;
    $data[3] = 0;
}

unset($data);
fclose($input_file);

// Let's use the first 25 entries for the top 25 words
$data = array_fill(0, 25, []);
$data[] = ''; // [25] 단어
$data[] = 0; // [26] 빈도

while (true) {
    $data[25] = fgets($word_freqs);

    if ('' === $data[25] or false === $data[25]) {
        break;
    }

    $data[25] = explode(",", $data[25]);
    $data[26] = intval($data[25][1]);
    $data[25] = trim($data[25][0]);

    for ($i = 0; $i < 25; $i++) {
        // 해당 index 에 배열이 비어있을 경우 추가
        // 빈도가 더 클 경우에는 push
        if ($data[$i] === [] or $data[$i][1] < $data[26]) {
            array_splice($data, $i, 0, [[$data[25], $data[26]]]);
            unset($data[27]);
            break;
        }
    }
}

for ($i = 0; $i < 25; $i++) {
    echo "{$data[$i][0]}  -  {$data[$i][1]}" . PHP_EOL;
}

fclose($word_freqs);
