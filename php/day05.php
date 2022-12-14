<?php
$input = explode("\n\n", trim(file_get_contents(__DIR__ . '/../input/day05.test.txt')));

if ((int)$argc > 1 && $argv[1] != "test") {
    $input = explode("\n\n", trim(file_get_contents(__DIR__ . '/../input/day05.txt')));
}

$stackLines = explode("\n", $input[0]);

if (!preg_match_all('#\d+#', array_pop($stackLines), $stackNums)) {
    die('stacks input malformed?');
}

$maxStack = count($stackNums[0]);
$stacks = [];
// character offsets in the lines
$stackIndexes = [];
$stackIdx = 1;

foreach ($stackNums[0] as $num) {
    $stacks[$num] = [];
    $stackIndexes[$num] = $stackIdx;
    $stackIdx += 4;
}

foreach (array_reverse($stackLines) as $line) {
    foreach ($stackIndexes as $num => $index) {
        if (isset($line[$index]) && trim($line[$index])) {
            $stacks[$num][] = $line[$index];
        }
    }
}

if (!preg_match_all('#move (\d+) from (\d+) to (\d+)#', $input[1], $ops, PREG_SET_ORDER)) {
    die('ops input malformed?');
}

$originalStacks = $stacks;

foreach ($ops as $op) {
    $count = (int)$op[1];
    $from = (int)$op[2];
    $to = (int)$op[3];

    while ($count --> 0) {
        $item = array_pop($stacks[$from]);
        array_push($stacks[$to], $item);
    }
}

echo 'part 1: which items are at the top? ';

foreach ($stacks as $stack) {
    echo $stack[count($stack) - 1];
}

echo PHP_EOL;

// part 2: CrateMover 9001, picks up multiple crates at once
$stacks = $originalStacks;

foreach ($ops as $op) {
    $count = (int)$op[1];
    $from = (int)$op[2];
    $to = (int)$op[3];
    $arm = [];

    while ($count --> 0) {
        array_unshift($arm, array_pop($stacks[$from]));
    }

    $stacks[$to] = array_merge($stacks[$to], $arm);
}

echo 'part 2: which items are at the top? ';

foreach ($stacks as $stack) {
    echo $stack[count($stack) - 1];
}

echo PHP_EOL;