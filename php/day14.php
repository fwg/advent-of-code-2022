<?php
$input = trim(file_get_contents(__DIR__ . '/../input/day14.test.txt'));

if ((int)$argc > 1 && $argv[1] != "test") {
    $input = trim(file_get_contents(__DIR__ . '/../input/day14.txt'));
}

// input is rock traces
$rocks = array_map(
    fn($line) => array_map(
        fn($coords) => array_map('intval', explode(',', $coords)),
        explode(' -> ', $line)),
    explode("\n", $input));

$max_x = 0;
$min_x = PHP_INT_MAX;
$max_y = 0;
$min_y = PHP_INT_MAX;

foreach ($rocks as $trace) {
    foreach ($trace as $coords) {
        $max_x = max($max_x, $coords[0]);
        $min_x = min($min_x, $coords[0]);
        $max_y = max($max_y, $coords[1]);
        $min_y = min($min_y, $coords[1]);
    }
}

$cave = [];
// fill with air
for ($y = 0; $y <= $max_y; $y++) {
    for ($x = 0; $x <= $max_x; $x++) {
        $cave[$y][$x] = '.';
    }
}

// draw traces
foreach ($rocks as $trace) {
    for ($i = 0, $c = count($trace) - 1; $i < $c; $i++) {
        draw_rocks($cave, $trace[$i], $trace[$i + 1]);
    }
}



$sand_count = 0;
$from = [500, 0];

while (($settled_at = drop_sand($cave, $from))) {
    $cave[$settled_at[1]][$settled_at[0]] = 'o';
    $sand_count += 1;
}

// draw_cave($cave, $max_x - $min_x + 3, $max_y + 2, $min_x - 2, 0);
echo "part 1: ", $sand_count, PHP_EOL;

function drop_sand($cave, $from): ?array
{
    $at = $from;
    // while we're in air, see if we can drop further
    while ($cave[$at[1]][$at[0]] == '.') {
        $below =
            ($cave[$at[1] + 1][$at[0] - 1] ?? ' ') .
            ($cave[$at[1] + 1][$at[0]] ?? ' ') .
            ($cave[$at[1] + 1][$at[0] + 1] ?? ' ');
        // down one step
        if ($below[1] == '.') {
            $at[1] += 1;
            continue;
        }
        // one step down and to the left
        if ($below[0] == '.') {
            $at[0] -= 1;
            $at[1] += 1;
            continue;
        }
        // one step down and to the right
        if ($below[2] == '.') {
            $at[0] += 1;
            $at[1] += 1;
            continue;
        }
        // if all three below us are either sand or rock, settle
        if (preg_match('/^[o#]{3}$/', $below)) {
            return $at;
        }
        // can't fall further and haven't settled => the abyss
        break;
    }
    return null;
}

function draw_rocks(&$cave, $from, $to)
{
    $dir_x = $to[0] - $from[0];
    $dir_y = $to[1] - $from[1];
    // norm
    if ($dir_x) $dir_x /= abs($dir_x);
    if ($dir_y) $dir_y /= abs($dir_y);
    $at = $from;
    $cave[$from[1]][$from[0]] = '#';

    while ($at[0] != $to[0] || $at[1] != $to[1]) {
        $at = [$at[0] + $dir_x, $at[1] + $dir_y];
        $cave[$at[1]][$at[0]] = '#';
    }
}

function draw_cave($cave, $w, $h, $x = 0, $y = 0)
{
//    echo "\33[2J";
    $max_x = $x + $w;
    $max_y = $y + $h;

    for (; $y < $max_y; $y++) {
        for ($x_i = $x; $x_i < $max_x; $x_i++) {
            echo $cave[$y][$x_i] ?? ' ';
        }
        echo PHP_EOL;
    }
}