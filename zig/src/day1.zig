const std = @import("std");
const print = std.debug.print;
const parseInt = std.fmt.parseInt;

pub fn main() !void {
    var arena = std.heap.ArenaAllocator.init(std.heap.page_allocator);
    defer arena.deinit();
    const allocator = arena.allocator();

    const stdin: std.fs.File = try std.fs.cwd().openFile("../input/day1.txt", .{});
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

    var max: i32 = 0;

    for (elves.items) |elf| {
        if (elf > max) {
            max = elf;
        }
    }

    print("max elf calories: {}\n", .{max});
}

fn sum(numbers: []const i32) i32 {
    var result: i32 = 0;
    for (numbers) |x| {
        result += x;
    }
    return result;
}

test "simple test" {
    try std.testing.expectEqual(@as(i32, 42), try parseInt(i32, "42", 10));
}
