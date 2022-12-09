use std::ops::RangeInclusive;
use advent2022::input;

fn contains<T>(r1: &RangeInclusive<T>, r2: &RangeInclusive<T>) -> bool where T: PartialOrd {
    r1.contains(r2.start()) && r1.contains(r2.end())
}

fn overlaps<T>(r1: &RangeInclusive<T>, r2: &RangeInclusive<T>) -> bool where T: PartialOrd {
    r1.contains(r2.start()) || r1.contains(r2.end()) || contains(r2, r1)
}

fn main() {
    let input = input("04");
    let assignments = input.split("\n").map(|line| {
        if line.is_empty() {
            return (0..=0, 1..=1);
        }
        let mut pair = line.split(",").map(|r| {
            let mut rs = r.split("-").map(|i| {
                i.parse::<u32>()
                    .expect(format!("Could not parse int {}", i).as_str())
            });
            let first = rs.next().expect("Malformed");
            let second = rs.next().expect("Malformed");
            first..=second
        });
        (pair.next().expect("Malformed"), pair.next().expect("Malformed"))
    });

    let mut contained = 0;
    let mut overlapped = 0;

    assignments.for_each(|(first, second)| {
        if contains(&first, &second) || contains(&second, &first) {
            contained += 1;
        }
        if overlaps(&first, &second) {
            overlapped += 1;
        }
    });

    println!("part 1: {}", contained);
    println!("part 2: {}", overlapped);
}