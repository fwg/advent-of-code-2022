const std = @import("std");
const print = std.debug.print;
const parseInt = std.fmt.parseInt;
const common = @import("./common.zig");

const Move = enum(u8) {
    rock = 'A',
    paper = 'B',
    scissors = 'C',
    unknown,
};

const Result = enum(u8) {
    loss = 0,
    draw = 3,
    win = 6,
};

const Game = struct {
    theirs: Move,
    ours: Move,
};

fn willLoseAgainst(m: Move) Move {
    return switch(m) {
        .rock => .scissors,
        .paper => .rock,
        .scissors => .paper,
        .unknown => .unknown
    };
}

fn willWinAgainst(m: Move) Move {
    return switch(m) {
        .rock => .paper,
        .paper => .scissors,
        .scissors => .rock,
        .unknown => .unknown
    };
}

fn beats(m1: Move, m2: Move) bool {
    return !(m1 == .unknown or m2 == .unknown) and
           m1 == willWinAgainst(m2);
}

fn move(m: u8) Move {
    if (m >= @enumToInt(Move.rock) and m <= @enumToInt(Move.scissors)) {
        return @intToEnum(Move, m);
    }
    return .unknown;
}

fn moveXYZ(m: u8) Move {
    if (m == 'X') return .rock;
    if (m == 'Y') return .paper;
    if (m == 'Z') return .scissors;
    return .unknown;
}

fn resultXYZ(m: u8) Result {
    if (m == 'Z') return .win;
    if (m == 'Y') return .draw;
    return .loss;
}

fn scoreMove(m: Move) u8 {
    return switch(m) {
        .rock => 1,
        .paper => 2,
        .scissors => 3,
        .unknown => 0
    };
}

fn scoreGame(g: Game) u8 {
    if (g.theirs == g.ours) return @enumToInt(Result.draw);
    if (beats(g.ours, g.theirs)) return @enumToInt(Result.win);
    return @enumToInt(Result.loss);
}

pub fn main() !void {
    var arena = std.heap.ArenaAllocator.init(std.heap.page_allocator);
    defer arena.deinit();
    const allocator = arena.allocator();
    const stdin: std.fs.File = try std.fs.cwd().openFile("../input/day02.txt", .{});
    const input = stdin.reader();
    var buf: []u8 = try allocator.alloc(u8, 64);

    var sumPart1: u32 = 0;
    var sumPart2: u32 = 0;

    while (true) {
        const line = try input.readUntilDelimiterOrEof(buf, '\n');

        if (line == null) {
            break;
        }

        // each line is "[ABC] [XYZ]"
        var g: Game = .{
            .theirs = move(buf[0]),
            .ours = .unknown
        };

        // part one: score the game as if XYZ is our move
        g.ours = moveXYZ(buf[2]);
        sumPart1 += scoreGame(g) + scoreMove(g.ours);

        // part two: score the game as if XYZ is the desired score
        g.ours = switch (resultXYZ(buf[2])) {
            .win => willWinAgainst(g.theirs),
            .draw => g.theirs,
            .loss => willLoseAgainst(g.theirs)
        };
        sumPart2 += scoreGame(g) + scoreMove(g.ours);
    }

    print("rock-paper-scissors game score 1: {}\n", .{sumPart1});
    print("rock-paper-scissors game score 2: {}\n", .{sumPart2});
}
