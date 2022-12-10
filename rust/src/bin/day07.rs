use advent2022::input;
use std::collections::HashMap;

fn main() {
    let input = input("07");
    let lines = input.trim_end().split("\n").collect::<Vec<&str>>();

    // each line is either:
    // $ cd (/|..|name)
    // $ ls
    // dir name
    // 123 file
    let mut cwd = Vec::<&str>::new();
    let mut sizes = HashMap::<String, u32>::new();

    for line in lines {
        if line.starts_with("$ cd ") {
            let parts = line.split(" ");
            let name = parts.last().expect("Malformed cd command!");
            if name == ".." {
                cwd.pop();
            } else {
                cwd.push(name);
            }
            continue;
        }
        if line.starts_with("$ ls") || line.starts_with("dir ") {
            // does nothing
            continue;
        }
        // line is expected to be "size file"
        let parts = line.split(" ");
        let size = parts.take(1).last().expect("Malformed file line!").parse::<u32>().expect("Not a number :(");
        // add file size to all dirs in path hierarchy
        let mut paths = cwd.clone();
        while !paths.is_empty() {
            let path = paths.join("/");
            let dir_size = sizes.get(&path);
            sizes.insert(
                path,
                size + match dir_size {
                    Some(s) => s,
                    None => &0,
                },
            );
            paths.pop();
        }
    }

    // part 1: find dirs with size at most 100000
    let mut part1: u32 = 0;
    for (_dir, size) in sizes.iter() {
        if size < &100000 {
            part1 += size;
        }
    }
    println!("part 1: {}", part1);

    // part 2: find smallest dir to delete to get free space >= 30M
    let root_size = sizes.get("/").expect("Has root size");
    let space = 70_000_000;
    let needed = 30_000_000;
    let available = space - root_size;
    let looking_for = needed - available;

    let mut part2_min = u32::MAX;
    for (_dir, size) in sizes.iter() {
        if size < &part2_min && size >= &looking_for {
            part2_min = *size;
        }
    }
    println!("part 2: {}", part2_min);

}
