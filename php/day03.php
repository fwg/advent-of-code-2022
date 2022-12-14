<?php
$input = explode("\n", trim(file_get_contents(__DIR__ . '/../input/day03.test.txt')));

if ((int)$argc > 1 && $argv[1] != "test") {
    $input = explode("\n", trim(file_get_contents(__DIR__ . '/../input/day03.txt')));
}

// hint: read this inside out
/** @var string[][][] $rucksacks */
$rucksacks = array_map(fn($line) => array_map(
    // 3. flip indexes and characters to make a "look-up map" with the
    // original position in the rucksack string as the value
    'array_flip',
    // 2. chunk into the two same-sized compartments
    array_chunk(
        // 1. treat each character as 1 item
        str_split($line, 1),
        strlen($line) / 2
    )
), $input);
// yes, PHP desperately needs a piping operator (like |>) to make functional
// compositions like the above more readable.

$same_items = 0;

foreach ($rucksacks as $rucksack) {
    foreach ($rucksack[0] as $item => $idx) {
        if (isset($rucksack[1][$item])) {
            $same_items += priority($item);
        }
    }
}

echo 'sum of the priorities of same items in both compartments: ', $same_items, PHP_EOL;

$badge_items = 0;
// I'm glad that we didn't need to find the elves' groups by finding overlapping items
$groups = array_chunk(
    // merge the two compartments first
    array_map(fn($rucksack) => array_merge($rucksack[0], $rucksack[1]), $rucksacks),
    3
);

foreach ($groups as $group) {
    // go through the first elf's items
    foreach ($group[0] as $item => $idx) {
        // if this item appears in both of the other rucksacks, it's the badge
        if (isset($group[1][$item]) && isset($group[2][$item])) {
            $badge_items += priority($item);
        }
    }
}

echo 'sum of the priorities of the badge items: ', $badge_items, PHP_EOL;

/**
 * a-z => 1-26
 * A-Z => 27-52
 *
 * @param string $c
 * @return int
 */
function priority($c) {
    $A = ord('A');
    $a = ord('a') - $A;
    $c = ord($c) - $A;

    if ($c >= $a) {
        return 1 + $c - $a;
    }
    return 27 + $c;
}