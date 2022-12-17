<?php
$input = trim(file_get_contents(__DIR__ . '/../input/day16.test.txt'));

if ((int)$argc > 1 && $argv[1] != "test") {
    $input = trim(file_get_contents(__DIR__ . '/../input/day16.txt'));
}

/**
 * plan?
 * - adjacency matrix with "fully connected" graph,
 *   edge weights are distances
 * - remove flow-rate 0 nodes, collapses paths (but keep distance)
 */

$nodes = [];
$flow_rates = [];
$tunnels = [];

foreach (explode("\n", $input) as $line) {
    if (!preg_match('#^Valve (\S+) has flow rate=(\d+); tunnels? leads? to valves? (.+)#', $line, $match)) {
        continue;
    }
    $nodes[] = $match[1];
    $flow_rates[$match[1]] = (int)$match[2];
    foreach (explode(", ", $match[3]) as $tunnel) {
        $tunnels[$match[1]][$tunnel] = 1;
    }
}

// remove all flow rate=0 nodes, except starting node AA
// by directly connecting all their connected nodes
foreach ($flow_rates as $n => $flow_rate) {
    if ($flow_rate > 0) {
        continue;
    }
    if ($n == 'AA') {
        continue;
    }
    $connections = array_keys($tunnels[$n]);
    // directly connect all the nodes
    foreach ($connections as $node) {
        foreach ($connections as $node2) {
            // no loops, also catches count(connections) = 1
            if ($node2 == $node) {
                continue;
            }
            // already has a tunnel
            if (isset($tunnels[$node][$node2])) {
                continue;
            }
            // new tunnel is longer!
            $tunnels[$node][$node2] = $tunnels[$node][$n] + $tunnels[$n][$node2];
        }
        // remove tunnel to us
        unset($tunnels[$node][$n]);
    }
    // remove the node
    unset($tunnels[$n]);
    unset($flow_rates[$n]);
    array_splice($nodes, array_search($n, $nodes), 1);
}

// first up, adjacency matrix
$adj = [[]];

foreach ($nodes as $i => $n) {
    foreach ($nodes as $j => $n2) {
        if (!empty($tunnels[$n][$n2])) {
            $adj[$i][$j] = $tunnels[$n][$n2];
        } else {
            $adj[$i][$j] = 0;
        }
    }
}

// find shortest path between the nodes
function dikstra($adj, $from, $to): int {
    // start from source node
    $set = [$from];
    $length = [$from => 0];

    while (!empty($set) && !isset($length[$to])) {
        $new_set = [];
        foreach ($set as $i) {
            foreach ($adj[$i] as $j => $edge) {
                if (!$edge) {
                    continue;
                }
                if (isset($length[$j])) {
                    continue;
                }
                $new_set[] = $j;
                $length[$j] = $length[$i] + $edge;
            }
        }
        $set = $new_set;
    }

    return $length[$to];
}

$paths = $adj;

foreach ($nodes as $i => $node) {
    foreach ($nodes as $j => $node2) {
        if ($i == $j || $adj[$i][$j] > 0) {
            continue;
        }
        $paths[$i][$j] = dikstra($adj, $i, $j);
        // matrix is symmetrical
        $paths[$j][$i] = $paths[$i][$j];
    }
}

// phew, 120 LOC, for what?

// now, our strategy is:
// for each node where we're at, figure out nodes to visit = valves to open
//   then: consider each case
//   if we go and open valve X,
//      what is the sum of the possible pressure release afterwards?
//   maximise that.

function possible($closed_valves, int $at_node, int $at_time, $flow_rates, $paths): int {
    // at time T, the possible release we could get from each valve is
    // the flow rate multiplied by the time it will be open, if we
    // open it next, by going there.
    $sum = 0;
    foreach ($closed_valves as $valve => $j) {
        // walk the tunnel + 1 minute opening
        $time_to_open = $paths[$at_node][$j] + 1;
        $release = ($at_time - $time_to_open) * $flow_rates[$valve];
        $sum += $release;
    }
    return $sum;
}

$closed = [];
foreach ($nodes as $i => $node) {
    if ($node == 'AA') {
        continue;
    }
    $closed[$node] = $i;
}

// start at AA = node 0
$at = 0;
$total_release = 0;
$time = 30;
$path_taken = ['AA'];

while (!empty($closed)) {
    $max_possible = 0;
    $max_release = 0;
    $go_to = null;
    $possible_futures = [];

    foreach ($closed as $valve => $node) {
        $t_go = $time - ($paths[$at][$node] + 1);
        $release = $t_go * $flow_rates[$valve];
        $afterwards = $closed;
        unset($afterwards[$valve]);

        if (empty($afterwards)) {
            $go_to = $valve;
            break;
        }

        $possible_afterwards = possible(
            $afterwards,
            $node,
            $t_go,
            $flow_rates,
            $paths
        );

        $possible_futures[] = [$release, $release + $possible_afterwards, $valve];
        $x = $possible_afterwards;
        echo "possible when going to $valve = $release / {$x}\n";
        // prefer more pressure release, but only if
        // the difference in potential release is small enough

        if ($release > $max_release &&
            ($max_possible - $possible_afterwards) < ($release - $max_release)) {
            $max_release = $release;
            $max_possible = $possible_afterwards;
            echo "new max: $max_release / $max_possible\n";
            $go_to = $valve;
        }

//        if ($release > $max_release ||
//            ($possible_afterwards - $max_possible) < ($release - $max_release)) {
//            $max_release = $release;
//            $max_possible = $possible_afterwards;
//            echo "new max: $max_release / $max_possible\n";
//            $go_to = $valve;
//        }
    }

    // first sort by release
//    usort($possible_futures, fn($f1, $f2) => ($f2[0] - $f1[0]));
//    $gogo = null;
//    if (count($possible_futures) > 1) {
//        $gogo = array_reduce(
//            $possible_futures,
//            // go lower release now if the diff in potential is worth it
//            function ($go, $alt) {
//                if (is_null($go)) {
//                    return $alt;
//                }
//                if ($alt[1] - $go[1] > $go[0] - $alt[0]) {
//                    echo "better go to $alt[2] than $go[2]\n";
//                    return $alt;
//                }
//                echo "better go to $go[2] than $alt[2]\n";
//                return $go;
//            },
//        );
//    }
//    if ($gogo) echo "n: would go to ", $gogo[2], PHP_EOL;
    echo "going to $go_to\n";

    // now, go the valve that has maximum possibility afterwards
    $time -= $paths[$at][$closed[$go_to]] + 1;
    $total_release += $time * $flow_rates[$go_to];
    echo "time is $time, reward is ", $time * $flow_rates[$go_to], PHP_EOL;
    $at = $closed[$go_to];
    unset($closed[$go_to]);
    $path_taken[] = $go_to;
}

echo "path taken: ", join(' -> ', $path_taken), PHP_EOL;
echo "total pressured released: ", $total_release, PHP_EOL;