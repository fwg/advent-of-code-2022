<?php
$input = file_get_contents(__DIR__ . '/../input/day02.txt');
$moves = array_map(fn($line) => $line ? explode(' ', $line) : [], explode("\n", $input));

$score1 = array_sum(array_map(fn($move) => empty($move) ? 0 : score($move[0], $move[1]), $moves));
echo "Score 1: ", $score1, PHP_EOL;
$score2 = array_sum(array_map(fn($move) => empty($move) ? 0 : score2($move[0], $move[1]), $moves));
echo "Score 2: ", $score2, PHP_EOL;

function score($theirs, $ours) {
    $equals = [
        'A' => 'X',
        'B' => 'Y',
        'C' => 'Z',
    ];
    $beats = array_flip([
        'X' => 'C', // rock beats scissors
        'Y' => 'A', // paper beats rock
        'Z' => 'B', // scissor beats paper
    ]);
    $shape = ['X' => 1, 'Y' => 2, 'Z' => 3];

    // default we lose
    $score = 0;
    // draw
    if ($ours == $equals[$theirs]) $score = 3;
    // we win
    if ($ours == $beats[$theirs]) $score = 6;

    return $score + $shape[$ours];
}

function score2($theirs, $end) {
    $result = [
        'X' => 0,
        'Y' => 3,
        'Z' => 6,
    ];
    $beats = array_flip([
        'A' => 'C', // rock beats scissors
        'B' => 'A', // paper beats rock
        'C' => 'B', // scissor beats paper
    ]);
    $shape = ['A' => 1, 'B' => 2, 'C' => 3];

    // default: we play a draw
    $play = $theirs;
    // we need to win
    if ($result[$end] == 6) $play = $beats[$theirs];
    // we need to lose
    if ($result[$end] == 0) $play = array_flip($beats)[$theirs];

    return $result[$end] + $shape[$play];
}