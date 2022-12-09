<?php
$input = <<<TEST
R 4
U 4
L 3
D 1
R 4
D 1
L 5
R 2
TEST;

if ((int)$argc > 1 && $argv[1] == 2) {
    $input = <<<TEST2
R 5
U 8
L 8
D 3
R 17
D 10
L 25
U 20
TEST2;
}

if ((int)$argc > 1 && $argv[1] != 2) {
    $input = trim(file_get_contents(__DIR__ . '/../input/day09.txt'));
}

// rope physics... yay?

class Vec {
    function __construct(
        public int $x = 0,
        public int $y = 0,
    ) {}

    public static function fromString(mixed $str): ?Vec
    {
        if (!preg_match('#^\((-?\d+),(-?\d+)\)$#', $str, $match)) {
            throw new Exception($str . ' is not a Vec:__toString');
        }
        return new Vec((int)$match[1], (int)$match[2]);
    }

    function __toString(): string {
        return "(" . $this->x . "," . $this->y . ")";
    }

    function add(Vec $b): Vec {
        return new Vec(
            $this->x + $b->x,
            $this->y + $b->y,
        );
    }

    function sub(Vec $b): Vec {
        return new Vec(
            $this->x - $b->x,
            $this->y - $b->y,
        );
    }

    function mul(int $f): Vec {
        return new Vec(
            $this->x * $f,
            $this->y * $f,
        );
    }

    function move(Vec $b): Vec {
        $this->x += $b->x;
        $this->y += $b->y;
        return $this;
    }

    function scale(int $f): Vec {
        $this->x *= $f;
        $this->y *= $f;
        return $this;
    }

    function len(): float {
        return sqrt($this->x*$this->x + $this->y*$this->y);
    }

    /**
     * The conversion from float to int makes it so that the diagonal
     * distance in the Euclidian space is 1 for the case we care about,
     * i.e. that they are diagonally adjacent.
     *
     * @param Vec $b
     * @return int
     */
    function dist(Vec $b): int {
        return (int)$b->sub($this)->len();
    }

    /**
     * The normalized vector is also broken by design: we want any kind of
     * off-axis movement to be a diagonal step roughly in the right direction.
     *
     * @return Vec
     */
    function norm(): Vec {
        $l = $this->len();
        if ($l == 0) {
            return new Vec(0, 0);
        }
        $nx = $this->x / $l;
        $ny = $this->y / $l;
        // basically we round up to the next full integer for the absolute
        // value. -0.8 becomes -1, 0.2 becomes 1, etc.
        return new Vec(
            (int)($nx < 0 ? floor($nx) : ceil($nx)),
            (int)($ny < 0 ? floor($ny) : ceil($ny)),
        );
    }
}

// input is movements on a 2D plane
$lines = explode("\n", $input);
// H and T must always be touching, i.e. their distance must be <= 1.
// They start at the same position, overlapping
//$rope = [new Vec(), new Vec()];
//echo "part 1: ", rope_physics($lines, $rope), PHP_EOL;

// part 2: ten knot rope
$rope = array_map(fn() => new Vec(), array_fill(0, 10, 1));
echo "part 2: ", rope_physics($lines, $rope), PHP_EOL;

/**
 * @param $lines string[]
 * @param $rope Vec[]
 * @return int
 */
function rope_physics(array $lines, array $rope): int
{
    $H = $rope[0];
    $T = $rope[count($rope) - 1];
    $length = count($rope);
    $visited = [$T.'' => true];

    $N = new Vec(0, 0);
    $R = new Vec(1, 0);
    $L = new Vec(-1, 0);
    $U = new Vec(0, 1);
    $D = new Vec(0, -1);

    foreach ($lines as $line) {
        if (!preg_match('#^([RLUD]) (\d+)$#', $line, $match)) {
            continue;
        }

        $movement = $N;

        switch ($match[1]) {
            case 'R': $movement = $R; break;
            case 'L': $movement = $L; break;
            case 'U': $movement = $U; break;
            case 'D': $movement = $D; break;
            default: break;
        }

        $m = $movement->mul((int)$match[2]);
        $H->move($m);

        for ($i = 1; $i < $length; $i++) {
            while ($rope[$i - 1]->dist($rope[$i]) > 1) {
                $rope[$i]->move($rope[$i - 1]->sub($rope[$i])->norm());
                print("\33[2J");
                echo '----------', PHP_EOL, $m, PHP_EOL;
                draw(40, 40, $rope, new Vec(20, 20));
                usleep(350000);
                if ($rope[$i] === $T) {
                    $visited[$T.''] = true;
                }
            }
        }

//        print("\33[2J");
//        echo '----------', PHP_EOL, $m, PHP_EOL;
//        draw(40, 40, $rope, new Vec(20, 20));
//        drawVisited(30, 30, array_keys($visited), new Vec(15, 15));
        usleep(2000000);
    }

//    drawVisited(500, 500, array_keys($visited), new Vec(250, 70));

    return count(array_keys($visited));
}

/**
 * @param $w
 * @param $h
 * @param Vec[] $rope
 * @param Vec $offset
 * @return void
 */
function draw($w, $h, array $rope, Vec $offset): void
{
    $grid = array_fill(0, $h, array_fill(0, $w, '.'));
    $l = count($rope) - 1;
    foreach ($rope as $i => $knot) {
        $c = $i;
        if ($i == 0) $c = 'H';
        if ($i == $l) $c = 'T';
        $pos = $knot->add($offset);
        $grid[$pos->y][$pos->x] = $c;
    }
    foreach (array_reverse($grid) as $row) {
        echo join('', $row), PHP_EOL;
    }
}


/**
 * @param $w
 * @param $h
 * @param string[] $visited
 * @param Vec $offset
 * @return void
 * @throws Exception
 */
function drawVisited($w, $h, array $visited, Vec $offset): void
{
    $vectors = array_map(fn($str) => Vec::fromString($str), $visited);
    $grid = array_fill(0, $h, array_fill(0, $w, '.'));
    foreach ($vectors as $vec) {
        $pos = $vec->add($offset);
        $grid[$pos->y][$pos->x] = '#';
    }
    foreach (array_reverse($grid) as $row) {
        echo join('', $row), PHP_EOL;
    }
}
