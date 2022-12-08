<?php
$input = <<<TEST
$ cd /
$ ls
dir a
14848514 b.txt
8504156 c.dat
dir d
$ cd a
$ ls
dir e
29116 f
2557 g
62596 h.lst
$ cd e
$ ls
584 i
$ cd ..
$ cd ..
$ cd d
$ ls
4060174 j
8033020 d.log
5626152 d.ext
7214296 k
TEST;

if ((int)$argc > 1) {
    $input = trim(file_get_contents(__DIR__ . '/../input/day07.txt'));
}

$lines = explode("\n", $input);

$stack = [];
$dirs = [];

foreach ($lines as $line) {
    if (preg_match('#^\$ cd (.+)$#', $line, $match)) {
        if ($match[1] == '..') {
            array_pop($stack);
        } else {
            $stack[] = $match[1];
        }
        continue;
    }
    if (preg_match('#^\$ ls#', $line)) {
        continue;
    }

    if (preg_match('#^(\d+) (.+)$#', $line, $match)) {
        for ($i = count($stack); $i >= 1; $i--) {
            $path = join('/', array_slice($stack, 0, $i));
            $dirs[$path] = ($dirs[$path] ?? 0) + (int)$match[1];
        }
    }
    if (preg_match('#^dir (.+)$#', $line, $match)) {
        $path = join('/', $stack) . '/' . $match[1];
        $dirs[$path] = ($dirs[$path] ?? 0);
    }
}

asort($dirs);
$sum = 0;

foreach ($dirs as $path => $size) {
    if ($size <= 100000) {
        $sum += $size;
    }
}

echo "part 1: ", $sum, PHP_EOL;

$free = 70_000_000 - $dirs['/'];
$needed = 30_000_000 - $free;

$min = PHP_INT_MAX;

foreach ($dirs as $path => $size) {
    if ($size <= $min && $size >= $needed) {
        $min = $size;
    }
}

echo "part 2: ", $min, PHP_EOL;
