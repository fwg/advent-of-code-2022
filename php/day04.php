<?php
$input = explode("\n", trim(file_get_contents(__DIR__ . '/../input/day04.test.txt')));

if ((int)$argc > 1 && $argv[1] != "test") {
    $input = explode("\n", trim(file_get_contents(__DIR__ . '/../input/day04.txt')));
}

$assignments = array_map(
    fn ($line) => array_map(
        fn ($part) => array_map(
            'intval',
            explode('-', $part)
        ),
        explode(',', $line)
    ),
    $input
);

$contained_count = 0;

foreach ($assignments as $assignment) {
    if (one_in_the_other($assignment[0], $assignment[1])) {
        $contained_count += 1;
    }
}

echo "count of assignment pairs where one is completely contained in the other: ";
echo $contained_count, PHP_EOL;

$overlapped_count = 0;

foreach ($assignments as $assignment) {
    if (have_overlap($assignment[0], $assignment[1])) {
        $overlapped_count += 1;
    }
}

echo "count of assignment pairs with overlap: ";
echo $overlapped_count, PHP_EOL;

function in_range($n, $range) {
    return $n >= $range[0] && $n <= $range[1];
}
function contained($range1, $range2) {
    return in_range($range1[0], $range2) && in_range($range1[1], $range2);
}
function one_in_the_other($range1, $range2) {
    return contained($range1, $range2) || contained($range2, $range1);
}
function have_overlap($range1, $range2) {
    return in_range($range1[0], $range2) || in_range($range1[1], $range2) ||
           in_range($range2[0], $range1) || in_range($range2[1], $range1);
}