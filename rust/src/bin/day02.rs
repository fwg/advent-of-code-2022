// This is my second ever rust program.
// Time to do some Types!
use std::fmt::Formatter;
use std::path::PathBuf;
use std::{fmt, fs};

#[derive(Eq, PartialEq, Clone)]
enum Move {
    Rock,
    Paper,
    Scissors,
}

impl fmt::Display for Move {
    fn fmt(&self, f: &mut Formatter<'_>) -> fmt::Result {
        write!(
            f,
            "{}",
            match self {
                Move::Rock => "Rock",
                Move::Paper => "Paper",
                Move::Scissors => "Scissors",
            }
        )
    }
}

/// Which move would beat `a`?
fn beats(a: &Move) -> Move {
    match a {
        Move::Rock => Move::Paper,
        Move::Paper => Move::Scissors,
        Move::Scissors => Move::Rock,
    }
}

/// Which move would lose against `a`?
fn loses_against(a: &Move) -> Move {
    match a {
        Move::Rock => Move::Scissors,
        Move::Paper => Move::Rock,
        Move::Scissors => Move::Paper,
    }
}

/// Translate their move A=rock/B=paper/C=scissors
fn translate_move(c: char) -> Option<Move> {
    match c {
        'A' => Some(Move::Rock),
        'B' => Some(Move::Paper),
        'C' => Some(Move::Scissors),
        _ => None,
    }
}

/// Part 1: our move is X=rock/Y=paper/Z=scissors
fn translate_part1(c: char) -> Option<Move> {
    match c {
        'X' => Some(Move::Rock),
        'Y' => Some(Move::Paper),
        'Z' => Some(Move::Scissors),
        _ => None,
    }
}

/// Part 2: our move is X=loss/Y=draw/Z=win
fn translate_part2(c: char, theirs: &Move) -> Option<Move> {
    match c {
        'X' => Some(loses_against(theirs)),
        'Y' => Some(theirs.clone()),
        'Z' => Some(beats(theirs)),
        _ => None,
    }
}

/// Score our move
fn score_move(m: &Move) -> u8 {
    match m {
        Move::Rock => 1,
        Move::Paper => 2,
        Move::Scissors => 3,
    }
}

/// Score a game of rock-paper-scissors for the elves
fn score(ours: &Move, theirs: &Move) -> u8 {
    let score = if ours == theirs {
        3
    } else if *ours == beats(theirs) {
        6
    } else {
        0
    };

    return score_move(ours) + score;
}

fn main() {
    let input = fs::read_to_string(PathBuf::from("../input/day02.txt"))
        .expect("Reading input/day02.txt failed!");

    // interpret the lines as games of rock-paper-scissors
    let mut part1: u32 = 0;
    let mut part2: u32 = 0;

    input.split("\n").for_each(|line| {
        if line.is_empty() {
            return;
        }
        let a = line.chars().nth(0).expect("Malformed line");
        let b = line.chars().nth(2).expect("Malformed line");

        part1 += u32::from(match (translate_move(a), translate_part1(b)) {
            (Some(theirs), Some(ours)) => score(&ours, &theirs),
            // hehehe, butts!
            (_, _) => 0,
        });

        part2 += u32::from(match translate_move(a) {
            Some(theirs) => match translate_part2(b, &theirs) {
                Some(ours) => score(&ours, &theirs),
                _ => 0,
            },
            _ => 0,
        });
    });

    println!("Part 1: {}", part1);
    println!("Part 2: {}", part2);
}
