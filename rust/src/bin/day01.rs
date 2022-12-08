// This is my first rust program!
use std::fs;
use std::path::PathBuf;

fn main() {
    let input = fs::read_to_string(PathBuf::from("../input/day01.txt"))
        .expect("Reading input/day01.txt failed!");
    let snack_list = input.split("\n\n");
    let mut top_three = [0, 0, 0];

    snack_list.for_each(|elf| {
        let mut sum = elf
            .split("\n")
            .map(|n| n.parse().unwrap_or(0))
            .sum();

        let mut i = 0;
        // insert sorted, shift smaller values to right
        while i < 3 {
            if sum > top_three[i] {
                let tmp = top_three[i];
                top_three[i] = sum;
                sum = tmp;
            }
            i += 1;
        }
    });

    println!(
        "Part 1: max calories in any elf's rucksack: {}",
        top_three[0]
    );
    println!(
        "Part 2: top 3 calories in rucksacks: {} = {}",
        top_three.map(|n| n.to_string()).join(" + "),
        top_three.iter().sum::<u32>()
    );
}
