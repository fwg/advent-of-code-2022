<?php
$input = <<<TEST
30373
25512
65332
33549
35390
TEST;

if ((int)$argc > 1) {
    $input = trim(file_get_contents(__DIR__ . '/../input/day08.txt'));
}

$trees = array_map('str_split', explode("\n", $input));

// count the first and last row, and 2 trees for each row in between
$outside = count($trees[0]) * 2 + (count($trees) - 2) * 2;

echo 'trees outside: ', $outside, PHP_EOL;

$max_x = count($trees[0]) - 2;
$max_y = count($trees) - 2;

$inside = 0;

for ($x = 1; $x <= $max_x; $x++) {
    for ($y = 1; $y <= $max_y; $y++) {
        if (tree_is_visible($trees, $x, $y)) {
            $inside += 1;
        }
    }
}

echo 'part 1, trees visible: ', $outside + $inside, PHP_EOL;

$max_scenic_score = 0;

for ($x = 1; $x <= $max_x; $x++) {
    for ($y = 1; $y <= $max_y; $y++) {
        $scenic_score = tree_scenic_score($trees, $x, $y);
        if ($scenic_score > $max_scenic_score) {
            $max_scenic_score = $scenic_score;
        }
    }
}

echo 'part 2, max scenic score: ', $max_scenic_score, PHP_EOL;


function tree_is_visible($trees, $x, $y) {
    // need to go in each direction and see if any are smaller.
    $left = tree_visible_in_direction($trees, $x, $y, -1, 0);
    $right = tree_visible_in_direction($trees, $x, $y, 1, 0);
    $top = tree_visible_in_direction($trees, $x, $y, 0, -1);
    $bottom = tree_visible_in_direction($trees, $x, $y, 0, 1);
    return $left || $right || $top || $bottom;
}

function tree_visible_in_direction($trees, $x, $y, $x_offset, $y_offset) {
    $height = $trees[$y][$x];
    $x = $x + $x_offset;
    $y = $y + $y_offset;

    while (isset($trees[$y][$x])) {
        // if tree smaller, go further
        if ($trees[$y][$x] < $height) {

            $x = $x + $x_offset;
            $y = $y + $y_offset;
        } else {
            return false;
        }
    }

    return true;
}

function tree_scenic_score($trees, $x, $y) {
    $left = tree_scenic_score_in_direction($trees, $x, $y, -1, 0);
    $right = tree_scenic_score_in_direction($trees, $x, $y, 1, 0);
    $top = tree_scenic_score_in_direction($trees, $x, $y, 0, -1);
    $bottom = tree_scenic_score_in_direction($trees, $x, $y, 0, 1);
    return $left * $right * $top * $bottom;
}

function tree_scenic_score_in_direction($trees, $x, $y, $x_offset, $y_offset) {
    $height = $trees[$y][$x];
    $x = $x + $x_offset;
    $y = $y + $y_offset;
    $score = 0;

    while (isset($trees[$y][$x])) {
        $score += 1;
        // if tree smaller, go further
        if ($trees[$y][$x] < $height) {
            $x = $x + $x_offset;
            $y = $y + $y_offset;
        } else {
            break;
        }
    }

    return $score;
}