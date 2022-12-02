<?php

$input = file_get_contents(__DIR__ . '/../input/day01.txt');
$elves = explode("\n\n", $input);
$max_calories = 0;

foreach ($elves as $i => $list) {
    $calories = array_map('intval', explode("\n", $list));
    $sum = $calories['_sum'] = sum($calories);
    $elves[$i] = $calories;

    if ($sum > $max_calories) {
        $max_calories = $sum;
    }
}

echo "maximum sum of calories of an elf: ", $max_calories, PHP_EOL;

usort($elves, function ($a, $b) {
    return $b['_sum'] - $a['_sum'];
});

$top_three = array_map(fn($e) => $e['_sum'], array_slice($elves, 0, 3));
echo "Top 3 elves' calorie stashes: ";
echo join(' ', $top_three);
echo " = ", sum($top_three);
echo PHP_EOL;


function sum($xs) {
    $s = 0;
    foreach ($xs as $x) $s += $x;
    return $s;
}