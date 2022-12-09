use std::ops::RangeInclusive;
use advent2022::input;

fn display(r: &RangeInclusive<u32>) -> String {
    format!("{}-{}", r.start(), r.end())
}

fn subsumes<T>(r1: &RangeInclusive<T>, r2: &RangeInclusive<T>) -> bool where T: PartialOrd {
    r1.contains(r2.start()) && r1.contains(r2.end())
}

fn overlaps<T>(r1: &RangeInclusive<T>, r2: &RangeInclusive<T>) -> bool where T: PartialOrd {
    r1.contains(r1.start()) || r1.contains(r2.end()) || subsumes(r2, r1)
}

fn main() {
    let input = input("04");
    let assignments = input.split("\n").map(|line| {
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

    assignments.take(3).for_each(|(first, second)| {
        println!("{} - {} = {}", display(&first), display(&second), overlaps(&first, &second));
        println!("-----");
    });
}