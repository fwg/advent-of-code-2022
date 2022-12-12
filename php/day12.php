<?php
$input = trim(file_get_contents(__DIR__ . '/../input/day12.test.txt'));

if ((int)$argc > 1 && $argv[1] != "test") {
    $input = trim(file_get_contents(__DIR__ . '/../input/day12.txt'));
}

class Point {
    function __construct(public int $x = 0, public int $y = 0,) {}
    function __toString(): string {return $this->x . ',' . $this->y;}
    // function move(array $dir) {$this->x += $dir[]}
}

// translate to heightmap
$translation = ['S' => 0, 'E' => 26];
foreach (range(0, 25) as $i) {
    $translation[chr(ord('a') + $i)] = $i;
}

$map = [];
$start_point = null;
$end_point = null;

foreach (explode("\n", $input) as $y => $line) {
    foreach (str_split($line) as $x => $char) {
        $map[$y][$x] = $translation[$char];

        if ($char === 'E') {
            $end_point = new Point($x, $y);
        }
        if ($char === 'S') {
            $start_point = new Point($x, $y);
        }
    }
}

if (!($end_point && $start_point)) {
    die("S and E must be defined!");
}

function path_length(array $map, Point $start_point, Point $end_point): int
{
    $positions = [$end_point];
    $visited = [$end_point . '' => true];
    $steps = 0;
    $directions = [
        ['x' => -1, 'y' => 0],
        ['x' => 1, 'y' => 0],
        ['x' => 0, 'y' => -1],
        ['x' => 0, 'y' => 1],
    ];

    // go back from end point: move to each viable position
    while (!isset($visited[$start_point . ''])) {
        $new_positions = [];
        foreach ($positions as $point) {
            $h0 = $map[$point->y][$point->x];
            // look into all directions
            foreach ($directions as $direction) {
                $y = $point->y + $direction['y'];
                $x = $point->x + $direction['x'];
                if (!isset($map[$y][$x])) {
                    continue;
                }
                $h1 = $map[$y][$x];
                // note: this is the reverse from the description on AoC
                // can go arbitrarily far up
                // can go equal height
                // can go 1 down
                if (!($h1 >= $h0 - 1)) {
                    // we can't go here
                    continue;
                }
                $p = new Point($x, $y);
                // we've already visited this
                if (isset($visited[$p . ''])) {
                    continue;
                }
                $new_positions[] = $p;
                $visited[$p . ''] = true;
            }
        }

        // we've taken 1 step in possibly multiple directions
        $steps += 1;
        $positions = $new_positions;
    }

    return $steps;
}

// part 1: find path length from S to E
echo "part 1: ", path_length($map, $start_point, $end_point), PHP_EOL;

// part 2: find shortest path from all points in the map at h=0
$min = PHP_INT_MAX;
foreach ($map as $y => $row) {
    foreach ($row as $x => $h) {
        if ($h > 0) {
            continue;
        }
        $p = new Point($x, $y);
        $l = path_length($map, $p, $end_point);
        echo "part 2 starting from {$p} reaching E in {$l} steps\n";
        if ($l < $min) {
            $min = $l;
        }
    }
}

echo "part 2: ", $min, PHP_EOL;