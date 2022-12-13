<?php
$input = trim(file_get_contents(__DIR__ . '/../input/day13.test.txt'));

if ((int)$argc > 1 && $argv[1] != "test") {
    $input = trim(file_get_contents(__DIR__ . '/../input/day13.txt'));
}

enum Result
{
    case Right;
    case Wrong;
    case Maybe;
}

function packet_order_correct($p1, $p2): Result {
    // If both values are integers, the lower integer should come first.
    if (is_int($p1) && is_int($p2)) {
        if ($p1 < $p2) {
            return Result::Right;
        }
        if ($p2 < $p1) {
            return Result::Wrong;
        }
        return Result::Maybe;
    }

    // If both values are lists, compare lists.
    if (is_array($p1) && is_array($p2)) {
        $i = 0;
        do {
            // if both sides run out at the same time, continue
            if (!isset($p1[$i]) && !isset($p2[$i])) {
                return Result::Maybe;
            }
            // If the left list runs out of items first, the inputs are in the right order.
            if (!isset($p1[$i]) && isset($p2[$i])) {
                return Result::Right;
            }
            // If the right list runs out of items first, the inputs are not in the right order.
            if (isset($p1[$i]) && !isset($p2[$i])) {
                return Result::Wrong;
            }
            // else compare items
            $r = packet_order_correct($p1[$i], $p2[$i]);
            // continue if undecided
            if ($r == Result::Maybe) {
                $i += 1;
                continue;
            }
            return $r;
        } while (true);
    }

    if (is_int($p1) && is_array($p2)) {
        return packet_order_correct([$p1], $p2);
    }
    if (is_array($p1) && is_int($p2)) {
        return packet_order_correct($p1, [$p2]);
    }

    // otherwise we got type confusion = wrong order
    return Result::Wrong;
}

// packet pairs
$packet_pairs = array_map(
    fn($pair) => array_map('json_decode', explode("\n", $pair)),
    explode("\n\n", $input)
);

// part 1:
// Determine which pairs of packets are already in the right order.
// What is the sum of the indices of those pairs?
$sum_right = 0;
$index = 1;

foreach ($packet_pairs as $pair) {
    $sum_right += match (packet_order_correct($pair[0], $pair[1])) {
        Result::Right => $index,
        default => 0,
    };
    $index += 1;
}

echo "part 1: ", $sum_right, PHP_EOL;

// part 2:
// put all packets in the correct order.
// new divider packets to find after sorting
$packets = [
    [[2]],
    [[6]],
];
foreach ($packet_pairs as $pair) {
    $packets[] = $pair[0];
    $packets[] = $pair[1];
}
usort($packets, fn($p1, $p2) => match (packet_order_correct($p1, $p2)) {
    Result::Right => -1,
    Result::Wrong => 1,
    Result::Maybe => 0,
});
// we're lazy
$encoded = array_map('json_encode', $packets);
$div1 = -1;
$div2 = -1;
foreach ($encoded as $i => $ep) {
    if ($ep === '[[2]]') $div1 = $i + 1;
    if ($ep === '[[6]]') $div2 = $i + 1;
}

echo "part 2: ", $div1 * $div2, PHP_EOL;
