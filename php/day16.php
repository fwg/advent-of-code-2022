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

$valves = [];
$flow_rates = [];
$tunnels = [];

foreach (explode("\n", $input) as $line) {
    if (!preg_match('#^Valve (\S+) has flow rate=(\d+); tunnels? leads? to valves? (.+)#', $line, $match)) {
        continue;
    }
    $valves[] = $match[1];
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
    array_splice($valves, array_search($n, $valves), 1);
}

// first up, adjacency matrix
$adj = [[]];

foreach ($valves as $i => $n) {
    foreach ($valves as $j => $n2) {
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

foreach ($valves as $i => $v1) {
    foreach ($valves as $j => $v2) {
        if ($i == $j || $adj[$i][$j] > 0) {
            continue;
        }
        $paths[$i][$j] = dikstra($adj, $i, $j);
        // matrix is symmetrical
        $paths[$j][$i] = $paths[$i][$j];
    }
}

// phew, 120 LOC, for what?

$valve_to_node = array_flip($valves);

function time_after_open($t, $at, $open) {
    global $valve_to_node;
    global $paths;
    return $t - $paths[$valve_to_node[$at]][$valve_to_node[$open]] - 1;
}

function pressure_release_m($t, $starting, $path) {
    global $flow_rates;
    $release = 0;
    $at = $starting;
    foreach ($path as $valve) {
        // time to go there + 1 minute to open
        $t_after_open = time_after_open($t, $at, $valve);
        $release += $t_after_open * $flow_rates[$valve];
        $at = $valve;
    }
    return $release;
}
function pressure_release($t, $starting, $path) {
    static $memo = [];
    $key = serialize([$t, $starting, $path]);
    if (isset($memo[$key])) {
        return $memo[$key];
    }
    return $memo[$key] = pressure_release_m($t, $starting, $path);
}

function best_path_m($t, $starting, $closed): array {
    $c = count($closed);
    if (!$c) {
        return [[], 0];
    }
    // only 1 valve? just go there
    if ($c < 2) {
        return [$closed, pressure_release($t, $starting, $closed)];
    }
    // more than 2 valves: permutate path and figure out max
    $max = 0;
    $path = [];
    foreach ($closed as $valve) {
        $closed_next = $closed;
        array_splice($closed_next, array_search($valve, $closed_next), 1);
        $t_next = time_after_open($t, $starting, $valve);
        // can't go past the time limit! - this was the best 'optimization'
        if ($t_next < 0) {
            continue;
        }
        $r = pressure_release($t, $starting, [$valve]);
        $potential_path = best_path($t_next, $valve, $closed_next);
        if ($r + $potential_path[1] > $max) {
            $path = array_merge([$valve], $potential_path[0]);
            $max = $r + $potential_path[1];
        }
    }
    return [$path, $max];
}

function best_path($t, $starting, $closed): array {
    static $memo = [];
    $key = serialize([$t, $starting, $closed]);
    if (isset($memo[$key])) {
        return $memo[$key];
    }
    return $memo[$key] = best_path_m($t, $starting, $closed);
}

// start with all valves closed
$closed = [];
foreach ($valves as $valve) {
    if ($valve == 'AA') {
        continue;
    }
    $closed[] = $valve;
}

// start at AA = node 0
$best = best_path(30, 'AA', $closed);

echo "closed: ", join(', ', $closed), PHP_EOL;
echo "path taken: ", join(' -> ', $best[0]), PHP_EOL;
echo "total pressured released: ", $best[1], PHP_EOL;