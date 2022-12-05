const std = @import("std");
const print = std.debug.print;
const parseInt = std.fmt.parseInt;
const common = @import("./common.zig");


fn priority(c: u8) u8 {
    if (c >= 'a' and c <= 'z') {
        return c - 'a' + 1;
    }
    return c - 'A' + 27;
}

pub fn day03(in: []const u8) !common.Answer {
    var lines: std.mem.SplitIterator(u8) = std.mem.split(u8, in, "\n");
    var line = lines.next();

    // split each line into two halves - the compartments of the rucksack.
    // part 1: which character is in both halves? sum up all characters' prios
    var part1Sum: u16 = 0;

    while (line != null and line.?.len > 0) {
        const half = line.?.len / 2;

        line: for (line.?[0..half]) |c| {
            for (line.?[half..line.?.len]) |d| {
                if (c == d) {
                    part1Sum += priority(c);
                    break :line;
                }
            }
        }

        line = lines.next();
    }

    lines.reset();
    line = lines.next();
    // part 2: go in groups of three lines, and we look at complete rucksacks
    var part2Sum: u32 = 0;
    var group: [3][]const u8 = undefined;
    var idx: u8 = 0;

    while (line != null and line.?.len > 0) {
        group[idx] = line.?;
        idx += 1;
        line = lines.next();

        if (idx != 3) {
            continue;
        }

        group: for (group[0][0..group[0].len]) |a| {
            for (group[1][0..group[1].len]) |b| {
                if (a != b) {
                    continue;
                }
                for (group[2][0..group[2].len]) |c| {
                    if (a != c) {
                        continue;
                    }

                    part2Sum += priority(a);
                    break :group;
                }
            }
        }

        idx = 0;
    }

    return common.Answer{
        .part1 = part1Sum,
        .part2 = part2Sum,
    };
}

pub fn main() !void {
    var arena = std.heap.ArenaAllocator.init(std.heap.page_allocator);
    defer arena.deinit();
    const allocator = arena.allocator();

    const stdin: std.fs.File = try std.fs.cwd().openFile("../input/day03.txt", .{});
    const input = try stdin.reader().readAllAlloc(allocator, 64 * 1024);

    const answer = try day03(input);
    print("day 3 part 1: {}\n", .{answer.part1});
    print("day 3 part 2: {}\n", .{answer.part2});
}

test "day 03" {
    const input =
        \\vJrwpWtwJgWrhcsFMMfFFhFp
        \\jqHRNqRjqzjGDLGLrsFMfFZSrLrFZsSL
        \\PmmdzqPrVvPwwTWBwg
        \\wMqvLMZHhHMvwLHjbvcjnnSBnvTQFn
        \\ttgJtRGJQctTZtZT
        \\CrZsJsPPZsGzwwsLwLmpwMDw
    ;
    const answer = try day03(input);
    try std.testing.expectEqual(@as(u32, 157), answer.part1);
    try std.testing.expectEqual(@as(u32, 70), answer.part2);
}