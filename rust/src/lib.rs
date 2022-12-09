use std::fs;
use std::path::PathBuf;

pub fn input(day: &str) -> String {
    let mut path = PathBuf::from("../input");
    path.push(["day", day, ".txt"].join(""));
    fs::read_to_string(path.as_path())
        .expect("Reading input/day04.txt failed!")
}