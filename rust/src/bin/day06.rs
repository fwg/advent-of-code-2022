use advent2022::input;

fn main() {
    let input = input("06").trim_end().chars().collect::<Vec<char>>();
    let (at, _marker) = input
        .windows(4)
        .enumerate()
        .find(unique_marker)
        .expect("No four-char marker found");
    println!("part 1: {}", 4 + at);

    let (at, _marker) = input
        .windows(14)
        .enumerate()
        .find(unique_marker)
        .expect("No fourteen-char marker found");
    println!("part 1: {}", 14 + at);
}

fn unique_marker((_n, window): &(usize, &[char])) -> bool {
    for i in 0..window.len() - 1 {
        for j in i + 1..window.len() {
            if window[i] == window[j] {
                return false;
            }
        }
    }
    true
}
