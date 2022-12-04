const std = @import("std");

pub fn sum(numbers: []const i32) i32 {
    var result: i32 = 0;
    for (numbers) |x| {
        result += x;
    }
    return result;
}

pub fn insertMax(maxes: []i32, n: i32) void {
    if (n <= maxes[0]) {
        return;
    }
    maxes[0] = n;
    var i: usize = 0;
    var t: i32 = undefined;

    while (i < maxes.len - 1) {
        if (maxes[i] > maxes[i + 1]) {
            t = maxes[i + 1];
            maxes[i + 1] = maxes[i];
            maxes[i] = t;
        }
        i += 1;
    }
}

pub const Answer = struct {
    part1: u32,
    part2: u32,
};