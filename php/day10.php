<?php
// ignore E_WARNING for list () construct, we're being lazy today.
error_reporting(E_ERROR | E_PARSE | E_STRICT);

$input = trim(file_get_contents(__DIR__ . '/../input/day10.test.txt'));

if ((int)$argc > 1 && $argv[1] != "test") {
    $input = trim(file_get_contents(__DIR__ . '/../input/day10.txt'));
}

$lines = explode(PHP_EOL, $input);

$X = 1;
$cycles = ['addx' => 2, 'noop' => 1];
$cycle = 0;

$X_at = [];

foreach ($lines as $line) {
    list ($op, $arg) = explode(' ', $line);
    $op_cycles = $cycles[$op] ?? 0;

    // each op takes some cycles
    while ($op_cycles --> 0) {
        $cycle += 1;

        // part 1: take X every 40th cycle, starting at 20
        if (($cycle - 20) % 40 == 0) {
            $X_at[$cycle] = $X;
        }
    }

    // at end of cycles, op's execution becomes visible in the register X
    if ($op == 'addx') {
        $X += (int)$arg;
    }
}

// signal strength = cycle * $X
$signal = 0;
foreach ($X_at as $cycle => $x) {
    $signal += $cycle * $x;
}
echo "part 1: ", $signal, PHP_EOL;