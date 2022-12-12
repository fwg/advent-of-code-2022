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

function path_lengths(array $map, array $start_points, Point $end_point): array
{
    $positions = [$end_point];
    $visited = [$end_point . '' => true];
    $steps = array_map(fn($p) => 1, $start_points);
    $directions = [
        ['x' => -1, 'y' => 0],
        ['x' => 1, 'y' => 0],
        ['x' => 0, 'y' => -1],
        ['x' => 0, 'y' => 1],
    ];

    // go back from end point: move to each viable position
    while (!empty(array_filter($start_points, fn($p) => !isset($visited[$p.''])))) {
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
                $visited[$p . ''] = true;
                $new_positions[] = $p;
            }
        }

        // apparently we cannot go anywhere anymore
        if (empty($new_positions)) {
            break;
        }
        // we've taken 1 step in possibly multiple directions
        foreach ($start_points as $i => $point) {
            // but only increase step count for start points we haven't visited
            if (!isset($visited[$point . ''])) {
                $steps[$i] += 1;
            }
        }
        $positions = $new_positions;

        // draw_visited_map($map, $visited);
        // usleep(10000);
    }

    // set all unvisited starting point steps to basically infinity
    foreach ($start_points as $i => $point) {
        if (!isset($visited[$point . ''])) {
            $steps[$i] = PHP_INT_MAX;
        }
    }

    return $steps;
}

function draw_visited_map($map, $visited): void
{
    echo "\33[2J";
    foreach ($map as $y => $row) {
        foreach ($row as $x => $cell) {
            if (isset($visited[$x . ',' . $y])) {
                echo '#';
            } else {
                echo '.';
            }
        }
        echo "\n";
    }
}

// part 1: find path length from S to E
echo "part 1: ", path_lengths($map, [$start_point], $end_point)[0], PHP_EOL;

// part 2: find shortest path from all points in the map at h=0
$starting_points = [];
foreach ($map as $y => $row) {
    foreach ($row as $x => $h) {
        if ($h > 0) {
            continue;
        }
        $p = new Point($x, $y);
        $starting_points[] = $p;
    }
}

$paths = path_lengths($map, $starting_points, $end_point);
$min = PHP_INT_MAX;

foreach ($paths as $i => $steps) {
    if ($steps < $min) {
        $min = $steps;
    }
}

echo "part 2: ", $min, PHP_EOL;