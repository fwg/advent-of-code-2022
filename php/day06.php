<?php
$input = str_split(<<<TEST
mjqjpqmgbljsphdztnvjfqwrcgsmlb
TEST);

if ($argc > 1) {
    $input = str_split(trim(file_get_contents(__DIR__ . '/../input/day06.txt')));
}

$len = count($input);

// start-of-packet marker = four characters ...
for ($i = 4; $i < $len; $i++) {
    $last_four = array_slice($input, $i - 4, 4);
    // ... that are all different
    if (count(array_flip($last_four)) == 4) {
        break;
    }
}

echo 'day 06 part 1: ', $i, PHP_EOL;

// start-of-message marker = fourteen characters ...
for ($i = 14; $i < $len; $i++) {
    $last_fourteen = array_slice($input, $i - 14, 14);
    // ... that are all different
    if (count(array_flip($last_fourteen)) == 14) {
        break;
    }
}

echo 'day 06 part 2: ', $i, PHP_EOL;