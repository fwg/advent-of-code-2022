<?php
$input = trim(file_get_contents(__DIR__ . '/../input/day15.test.txt'));

if ((int)$argc > 1 && $argv[1] != "test") {
    $input = trim(file_get_contents(__DIR__ . '/../input/day15.txt'));
}

// another non-euclidean geometry thing!

class Coordinates
{
    static Coordinates $north;
    static Coordinates $south;
    static Coordinates $east;
    static Coordinates $west;
    static array $cardinals = [];
    function __construct(public int $x, public int $y){}
    function distance(Coordinates $other): int {
        return abs($other->x - $this->x) + abs($other->y - $this->y);
    }
    function add(Coordinates $other): Coordinates {
        return new Coordinates($this->x + $other->x, $this->y + $other->y);
    }
    function sub(Coordinates $other): Coordinates {
        return new Coordinates($this->x - $other->x, $this->y - $other->y);
    }
    function move(Coordinates $m): Coordinates {
        $this->x += $m->x;
        $this->y += $m->y;
        return $this;
    }
    function scale(int $f): Coordinates {
        $this->x *= $f;
        $this->y *= $f;
        return $this;
    }
    function mul(int $f): Coordinates {
        return new Coordinates(
            $this->x * $f,
            $this->y * $f,
        );
    }
    function len(): int {
        return abs($this->x) + abs($this->y);
    }
    function euclideanLen(): float {
        return sqrt($this->x*$this->x + $this->y*$this->y);
    }
    function direction(): Coordinates {
        $x = max(-1, min(1, $this->x));
        $y = max(-1, min(1, $this->y));
        return new Coordinates($x, $y);
    }
    function __toString(): string {
        return "($this->x,$this->y)";
    }
    static function fromString(string $str): ?Coordinates {
        if (!preg_match('#\((\d+),(\d+)\)#', $str, $match)) {
            return null;
        }
        return new Coordinates($match[1], $match[1]);
    }
}

Coordinates::$north = new Coordinates(0, -1);
Coordinates::$south = new Coordinates(0, 1);
Coordinates::$west = new Coordinates(-1, 0);
Coordinates::$east = new Coordinates(1, 0);
Coordinates::$cardinals = [Coordinates::$north, Coordinates::$west, Coordinates::$south, Coordinates::$east];

class Map
{
    public Coordinates $min;
    public Coordinates $max;
    public array $known = [];

    function __construct()
    {
        $this->min = new Coordinates(0, 0);
        $this->max = new Coordinates(0, 0);
    }
    function at(Coordinates $c): string {
        return $this->known[$c->__toString()] ?? '.';
    }
    function at_i(int $x, int $y): string {
        return $this->known["($x,$y)"] ?? '.';
    }
    function set(Coordinates $c, string $v): void {
        $this->known[$c->__toString()] = $v;
        $this->min->x = min($this->min->x, $c->x);
        $this->min->y = min($this->min->y, $c->y);
        $this->max->x = max($this->max->x, $c->x);
        $this->max->y = max($this->max->y, $c->y);
    }
    function draw(?Coordinates $from = null, ?Coordinates $to = null): string {
        $from ??= $this->min;
        $to ??= $this->max;
        $out = "";
        for ($y = $from->y; $y <= $to->y; $y++) {
            for ($x = $from->x; $x <= $to->x; $x++) {
                $out .= $this->at_i($x, $y);
            }
            $out .= PHP_EOL;
        }
        return $out;
    }
    function draw_circle(Coordinates $origin, int $radius, string $sym = '#'): void {
        $pos = [$origin->scale(1)];
        $visited = [$origin . '' => true];
        while (!empty($pos)) {
            $new_pos = [];
            foreach ($pos as $coordinates) {
                foreach (Coordinates::$cardinals as $cardinal) {
                    $next = $coordinates->add($cardinal);
                    if ($origin->distance($next) > $radius || isset($visited[$next . ''])) {
                        continue;
                    }
                    $new_pos[] = $next;
                    $visited[$next . ''] = true;
                    if ($this->at($next) == '.') {
                        $this->set($next, $sym);
                    }
                }
            }
            $pos = $new_pos;
        }
    }
    function iterate(Coordinates $from, Coordinates $to, callable $f): void {
        for ($y = $from->y; $y <= $to->y; $y++) {
            for ($x = $from->x; $x <= $to->x; $x++) {
                $f($this, $x, $y);
            }
        }
    }
}

$map = new Map();
/** @var Coordinates[] $sensors */
$sensors = [];
$beacons = [];
$distances = [];

foreach (explode("\n", $input) as $line) {
    if (!preg_match('#Sensor at x=(-?\d+), y=(-?\d+): closest beacon is at x=(-?\d+), y=(-?\d+)#', $line, $match)) {
        continue;
    }
    $S = new Coordinates((int)$match[1], (int)$match[2]);
    $B = new Coordinates((int)$match[3], (int)$match[4]);
    $sensors[] = $S;
    $beacons[] = $B;
    $distances[$S.''] = $S->distance($B);

    $map->set($S, 'S');
    $map->set($B, 'B');

    if (($argv[1] ?? "") == "test") {
        $map->draw_circle($S, $S->distance($B));
    }
}

// part 1 test: at y=10, how many #?
if (($argv[1] ?? "") == "test") {
    $map->set(new Coordinates(0, 0), '0');
    echo $map->draw();
    $row = $map->draw(
        new Coordinates($map->min->x, 10),
        new Coordinates($map->max->x, 10)
    );
    $count = 0;
    foreach (str_split($row) as $c) {
        if ($c == '#') {
            $count += 1;
        }
    }
    echo "part 1 test: ", $count, PHP_EOL;
    $count2 = 0;
    $map->iterate(new Coordinates($map->min->x, 10), new Coordinates($map->max->x, 10), function (Map $map, $x, $y) use (&$count2, $sensors, $distances) {
        $xy = new Coordinates($x, $y);
        // beacon or sensor?
        if ($map->at($xy) == 'B' || $map->at($xy) == 'S') {
            return;
        }
        // if any of the sensors reach this point, count up
        if (array_reduce($sensors, fn ($found, Coordinates $s) => $found || $s->distance($xy) <= $distances[$s.''], false)) {
            $count2 += 1;
        }
    });
    echo "part 1 test 2: ", $count2, PHP_EOL;
}
if (($argv[1] ?? "") == "run1") {
    // part 1 test: at y=2000000, how many #?
    echo "min: ", $map->min, PHP_EOL;
    echo "max: ", $map->max, PHP_EOL;
    $max_distance = max(...array_values($distances));
    echo "max_distance: ", $max_distance, PHP_EOL;
    $from = new Coordinates($map->min->x - $max_distance, 2_000_000);
    $to = new Coordinates($map->max->x + $max_distance, 2_000_000);
    $count = 0;
    $map->iterate($from, $to, function (Map $map, $x, $y) use (&$count, $sensors, $distances) {
        $xy = new Coordinates($x, $y);
        // beacon or sensor?
        if ($map->at($xy) == 'B' || $map->at($xy) == 'S') {
            return;
        }
        // if any of the sensors reach this point, count up
        foreach ($sensors as $sensor) {
            if ($sensor->distance($xy) <= $distances[$sensor . '']) {
                $count += 1;
                break;
            }
        }
    });

    echo "part 1: ", $count, PHP_EOL;
}

if (($argv[1] ?? "") == "test") {
    // part 2: in the space 0 <= x <= 20, 0 <= y <= 20, where is a free spot?
    $from = new Coordinates(0, 0);
    $to = new Coordinates(20, 20);
} else {
    // part 2: in the space 0,0 <= x,y <= 4M,4M where is a free spot?
    $from = new Coordinates(0, 0);
    $to = new Coordinates(4_000_000, 4_000_000);
}

// this only works for a really small search space
if (($argv[1] ?? "") == "test") {
    $locations = [];
    $map->iterate($from, $to, function (Map $m, $x, $y) use (&$locations, $sensors, $distances) {
        $xy = new Coordinates($x, $y);
        // beacon or sensor?
        if ($m->at($xy) == 'B' || $m->at($xy) == 'S') {
            return;
        }
        // if any of the sensors reach this point, not free
        foreach ($sensors as $sensor) {
            if ($sensor->distance($xy) <= $distances[$sensor . '']) {
                return;
            }
        }
        // yay, free
        $locations[] = $xy;
    });

    echo "part 2 test 1: ", $locations[0], " tuning frequency: ", $locations[0]->x * 4_000_000 + $locations[0]->y, PHP_EOL;
}

// ok new plan: cut chunks out of intervals?
// plan:
//  - make 'surface' rectangles, with 4 corners and a distance to center (radius)
//  - find overlapping edges of pairwise rectangles, get lines?
//  - find intersecting lines (use euclidean algebra!)
//    OR: make normalized diff vectors, must be the same as the norm vec of the line (same direction!)

class Diamond
{
    function __construct(
        public Coordinates $center,
        public int $radius,
    ) {
    }
    function on_edge(Coordinates $point): bool {
        return $this->center->distance($point) == $this->radius;
    }
    function within(Coordinates $point): bool {
        return $this->center->distance($point) < $this->radius;
    }
    function circumference(): int {
        return 4 * $this->radius + 2;
    }
    function iterate(callable $cb): void {
        $top = $this->center->add(Coordinates::$north->mul($this->radius));
        $right = $this->center->add(Coordinates::$east->mul($this->radius));
        $bottom = $this->center->add(Coordinates::$south->mul($this->radius));
        $left = $this->center->add(Coordinates::$west->mul($this->radius));

        $SE = Coordinates::$east->sub(Coordinates::$north)->direction();
        $SW = Coordinates::$south->sub(Coordinates::$east)->direction();
        $NW = Coordinates::$west->sub(Coordinates::$south)->direction();
        $NE = Coordinates::$north->sub(Coordinates::$west)->direction();

        // go south east until right corner
        $at = $top->mul(1);
        for (; $at->x != $right->x; $at = $at->add($SE)) {
            $cb($at);
        }
        // go south west until bottom corner
        for (; $at->x != $bottom->x; $at = $at->add($SW)) {
            $cb($at);
        }
        // go north west until left corner
        for (; $at->x != $left->x; $at = $at->add($NW)) {
            $cb($at);
        }
        // go north east until top corner
        for (; $at->x < $top->x; $at = $at->add($NE)) {
            $cb($at);
        }
    }
}

$diamonds = array_map(fn(Coordinates $sensor) => new Diamond($sensor, $distances[$sensor . ''] + 1), $sensors);
$sum = 0;

foreach ($diamonds as $d) {
    $sum += $d->circumference();
}

echo "sum circumferences: ", $sum, PHP_EOL;

/** @var Coordinates[] $possible_locations */
$possible_locations = [];

foreach ($diamonds as $i => $d) {
    $d->iterate(function (Coordinates $c) use ($map, &$possible_locations, $d, $diamonds) {
        $possible = false;
        foreach ($diamonds as $diamond) {
            if ($diamond == $d) continue;
            if ($diamond->on_edge($c)) {
                $possible = true;
                break;
            }
        }
        if (!$possible) {
            return;
        }
        foreach ($diamonds as $diamond) {
            if ($diamond != $d && $diamond->within($c)) {
                $possible = false;
            }
        }
        if ($possible) {
            $possible_locations[] = $c;
        }
    });
}

/** @var ?Coordinates $found */
$found = null;
foreach ($possible_locations as $location) {
    if ($location->x >= $from->x && $location->y >= $from->y &&
        $location->x <= $to->x && $location->y <= $to->y) {
        $found = $location;
        break;
    }
}

if ($found) {
    echo "part 2 test 1: ", $found, " tuning frequency: ", $found->x * 4_000_000 + $found->y, PHP_EOL;
} else {
    echo "not found?\n";
}

