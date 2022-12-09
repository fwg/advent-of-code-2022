#![feature(iter_array_chunks)]
// at the time of writing (2022-12-08) array_chunks needs rust nightly!
use std::fs;
use std::path::PathBuf;

fn score(badge: char) -> u32 {
    match badge {
        'a'..='z' => 1 + badge as u32 - 'a' as u32,
        'A'..='Z' => 27 + badge as u32 - 'A' as u32,
        _ => 0,
    }
}

fn main() {
    let input = fs::read_to_string(PathBuf::from("../input/day03.txt"))
        .expect("Reading input/day03.txt failed!");

    // day 3: rucksacks with items that are chars
    // part 1: two equal sized compartments (halves), badge is the one that is in both halves
    let part1: u32 = input
        .split("\n")
        .map(|line| {
            let (first, second) = line.split_at(line.len() / 2);
            for x in first.chars() {
                if second.contains(x) {
                    return score(x);
                }
            }
            return 0;
        })
        .sum();
    println!("Part 1: {}", part1);

    // part 2: find badge in group of 3
    let mut part2: u32 = 0;

    for [one, two, three] in input.split("\n").array_chunks() {
        for x in one.chars() {
            if two.contains(x) && three.contains(x) {
                part2 += score(x);
                break;
            }
        }
    }

    println!("Part 2: {}", part2);
}
