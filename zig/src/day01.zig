const std = @import("std");
const print = std.debug.print;
const parseInt = std.fmt.parseInt;
const common = @import("./common.zig");
const sum = common.sum;
const insertMax = common.insertMax;

pub fn main() !void {
    var arena = std.heap.ArenaAllocator.init(std.heap.page_allocator);
    defer arena.deinit();
    const allocator = arena.allocator();

    const stdin: std.fs.File = try std.fs.cwd().openFile("../input/day01.txt", .{});
    const input = stdin.reader();

    var calories = std.ArrayList(i32).init(allocator);
    defer calories.deinit();

    var elves = std.ArrayList(i32).init(allocator);
    defer elves.deinit();

    var buf: []u8 = try allocator.alloc(u8, 64);

    while (true) {
        const line = try input.readUntilDelimiterOrEof(buf, '\n');

        if (line == null) {
            break;
        }

        // emtpy line: one elf's calorie list ended
        if (line.?.len < 1) {
            try elves.append(sum(calories.items));
            calories.clearRetainingCapacity();
            continue;
        }

        try calories.append(try parseInt(i32, line.?, 10));
    }

    var maxes = [3]i32 {0, 0, 0};

    for (elves.items) |elf| {
        insertMax(&maxes, elf);
    }

    print("max elf calories: {}, {}, {}\n", .{maxes[0], maxes[1], maxes[2]});
    print("max sum: {}\n", .{maxes[0] + maxes[1] + maxes[2]});
}
