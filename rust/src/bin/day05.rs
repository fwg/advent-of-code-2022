use advent2022::input;

fn main() {
    let input = input("05");
    let parts = input.split("\n\n").collect::<Vec<_>>();

    // our stacks of crates are at specific indexes
    let mut stacks: [Vec<char>; 9] = [
        Vec::new(),
        Vec::new(),
        Vec::new(),
        Vec::new(),
        Vec::new(),
        Vec::new(),
        Vec::new(),
        Vec::new(),
        Vec::new(),
    ];

    let mut crate_lines: Vec<&str> = parts[0].split("\n").collect();
    // remove last line with the stack numbers
    crate_lines.pop();
    // go from bottom to top
    crate_lines.reverse();
    crate_lines.iter().for_each(|line| {
        // line is like "[S] [N] [F] [G] [W] [B] [H] [F] [N]"
        // indices at 1, 5, 9, ...
        for n in 0..=8 {
            let idx = 1 + 4 * n;
            let c = line.chars().nth(idx);
            if c != Some(' ') {
                stacks[n].push(c.unwrap());
            }
        }
    });

    let mut part1 = stacks.clone().map(|v| v.clone());
    let mut part2 = stacks.map(|v| v.clone());

    parts[1].split("\n").for_each(|line| {
        // line is like "move N from A to B"
        let op: Vec<u32> = line
            .split(" ")
            .filter_map(|word| match word.parse::<u32>() {
                Ok(n) => Some(n),
                _ => None,
            })
            .collect();

        if op.len() < 3 {
            return;
        }
        let count = usize::try_from(op[0]).expect("Malformed count");
        let to = usize::try_from(op[2] - 1).expect("Malformed to");
        let from = usize::try_from(op[1] - 1).expect("Malformed from");

        // part 1: move one by one
        for _ in 0..count {
            let item = part1[from].pop().unwrap();
            part1[to].push(item);
        }

        // part 2: move at once
        let mid = part2[from].len() - count;
        let mut items = part2[from].split_off(mid);
        part2[to].append(&mut items);
    });

    let tops: String = part1
        .map(|v| v.last().expect("Empty stack!").clone())
        .iter()
        .collect();
    println!("part1: {}", tops);

    let tops2: String = part2
        .map(|v| v.last().expect("Empty stack!").clone())
        .iter()
        .collect();
    println!("part2: {}", tops2);
}
