const std = @import("std");

pub fn build(b: *std.build.Builder) !void {
    // Standard target options allows the person running `zig build` to choose
    // what target to build for. Here we do not override the defaults, which
    // means any target is allowed, and the default is native. Other options
    // for restricting supported target set are available.
    const target = b.standardTargetOptions(.{});

    // Standard release options allow the person running `zig build` to select
    // between Debug, ReleaseSafe, ReleaseFast, and ReleaseSmall.
    const mode = b.standardReleaseOptions();

    const days  = [_][]const u8 {
        "day1",
    };
    const run_step = b.step("run", "Run the app");
    const test_step = b.step("test", "Run unit tests");
    // make a string buffer on the stack
    var buf: [64]u8 = undefined;
    // we need to pass a slice to bufPrint though
    const bufSlice = buf[0..];

    // add run & test for all days
    for (days) |day| {
        // zig unfortunately cannot concat the const string slices with ++ (yet?),
        // so we need to bufPrint into a mutable buffer.
        // for this try to work build() needs to return !void
        const path = try std.fmt.bufPrint(bufSlice, "src/{s}.zig", .{ day });

        const exe = b.addExecutable(day, path);
        exe.setTarget(target);
        exe.setBuildMode(mode);
        exe.install();

        const exe_run = exe.run();
        exe_run.step.dependOn(b.getInstallStep());

        if (b.args) |args| {
            exe_run.addArgs(args);
        }

        run_step.dependOn(&exe_run.step);

        const exe_test = b.addTest(path);
        exe_test.setTarget(target);
        exe_test.setBuildMode(mode);
        test_step.dependOn(&exe_test.step);
    }
}
