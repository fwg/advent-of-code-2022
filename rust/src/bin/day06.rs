use advent2022::input;

fn main() {
    let input = input("06").trim_end().chars().collect::<Vec<char>>();
    let (at, _marker) = input
        .windows(4)
        .enumerate()
        .find(|(_n, window)| {
            !(window[0] == window[1]
                || window[0] == window[2]
                || window[0] == window[3]
                || window[1] == window[2]
                || window[1] == window[3]
                || window[2] == window[3])
        })
        .expect("No four-char marker found");
    println!("part 1: {}", 4 + at);
}
