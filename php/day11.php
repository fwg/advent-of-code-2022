<?php
$input = trim(file_get_contents(__DIR__ . '/../input/day11.test.txt'));

if ((int)$argc > 1 && $argv[1] != "test") {
    $input = trim(file_get_contents(__DIR__ . '/../input/day11.txt'));
}

// part 2 integer bignums, we need no decimal places
bcscale(0);

$monkeys = array_map(function ($description) {
    $lines = array_map('trim', explode("\n", $description));
    $monkey = ['Inspected' => 0];

    foreach ($lines as $line) {
        $parts = array_map('trim', explode(":", $line));
        $k = $parts[0];
        $v = null;
        switch ($k) {
            case 'Starting items':
                $v = explode(', ', $parts[1]);
                $k = 'items';
                break;
            case 'Operation':
                preg_match('#new = old ([+*]) (old|\d+)#', $parts[1], $match);
                $v = ['op' => $match[1], 'arg' => $match[2]];
                break;
            case 'Test':
                preg_match('#divisible by (\d+)#', $parts[1], $match);
                $v = $match[1];
                break;
            case 'If true':
            case 'If false':
                preg_match('#throw to monkey (\d+)#', $parts[1], $match);
                $v = (int)$match[1];
                break;
            default:
                // 'Monkey N'
                $v = $k;
                $k = 'Name';
        }
        $monkey[$k] = $v;
    }

    return $monkey;
}, explode("\n\n", $input));

function monkey_round(&$monkeys, $with_relief = true, $modulus = 0): void
{
    foreach ($monkeys as &$monkey) {
        // monkey inspects each item in order
        while (!empty($monkey['items'])) {
            // part 2: worry is now a string for bcmath!
            $worry = array_shift($monkey['items']);
            $monkey['Inspected'] += 1;

            // Operation shows how your worry level changes as that monkey inspects an item.
            $op = $monkey['Operation']['op'];
            $arg = $monkey['Operation']['arg'];
            if ($arg == 'old') {
                $arg = $worry;
            }
            if ($op == '*') {
                $worry = bcmul($worry, $arg);
            } else {
                $worry = bcadd($worry, $arg);
            }

            // After each monkey inspects an item but before it tests your worry level,
            // your relief that the monkey's inspection didn't damage the item causes
            // your worry level to be divided by three and rounded down to the nearest
            // integer.
            if ($with_relief) {
                $worry = bcdiv($worry , '3');
            }

            // keep worry numbers manageable, as they grow exponentially otherwise
            if ($modulus) {
                $worry = bcmod($worry, $modulus);
            }

            // Test shows how the monkey uses your worry level to decide where to
            // throw an item next.
            $throw_to = $monkey['If false'];
            if (bcmod($worry, $monkey['Test']) == 0) {
                $throw_to = $monkey['If true'];
            }

            // throw to next monkey
            $monkeys[$throw_to]['items'][] = $worry;
        }
    }
}

function busiest_monkeys($monkeys): array {
    $inspected = array_map(fn($m) => $m['Inspected'], $monkeys);
    rsort($inspected);
    return [$inspected[0], $inspected[1]];
}

$initial_monkeys = $monkeys;

$rounds = 20;
while ($rounds --> 0) {
    monkey_round($monkeys);
}

$maxes = busiest_monkeys($monkeys);
echo "part 1: ", $maxes[0] * $maxes[1], PHP_EOL;

// part 2: without worry relief (/3)
$monkeys = $initial_monkeys;
// global modulus is product of all "divisible by" tests
$modulus = 1;
foreach ($monkeys as $monkey) {
    $modulus *= $monkey['Test'];
}
echo "part 2 modulus: ", $modulus, PHP_EOL;

$rounds = 10_000;
while ($rounds --> 0) {
    monkey_round($monkeys, false, $modulus);
}

$maxes = busiest_monkeys($monkeys);
echo "part 2: ", $maxes[0] * $maxes[1], PHP_EOL;