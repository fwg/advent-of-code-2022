<?php
$input = <<<TEST
$ cd /
$ ls
dir a
14848514 b.txt
8504156 c.dat
dir d
$ cd a
$ ls
dir e
29116 f
2557 g
62596 h.lst
$ cd e
$ ls
584 i
$ cd ..
$ cd ..
$ cd d
$ ls
4060174 j
8033020 d.log
5626152 d.ext
7214296 k
TEST;

if ((int)$argc > 1) {
    $input = trim(file_get_contents(__DIR__ . '/../input/day07.txt'));
}

$lines = explode("\n", $input);

abstract class Tree
{
    /** @var Tree[] */
    public $children = [];

    function __construct(
        public string $name,
        public mixed $data,
        public ?Tree  $parent = null,
    )
    {
    }

    public function getChild(string $name): ?Tree
    {
        foreach ($this->children as $child) {
            if ($child->name == $name) {
                return $child;
            }
        }
        return null;
    }

    public function addChild(Tree $child): void
    {
        $this->children[] = $child;
        $child->parent = $this;
    }

    public function traverseDF(callable $cb)
    {
        foreach ($this->children as $child) {
            $child->traverseDF($cb);
        }
        $cb($this);
    }

    public function traverseBF(callable $cb)
    {
        $cb($this);
        foreach ($this->children as $child) {
            $child->traverseBF($cb);
        }
    }

    public function path(): string
    {
        return ($this->parent ? $this->parent->path() . '/' : '') .  $this->name;
    }

    abstract public function print(): string;
}

interface Sizeable
{
    public function getSize(): int;
}

class Dir extends Tree implements Sizeable
{
    public function print(): string
    {
        $s = $this->getSize();
        $out = '- ' . $this->name . ' (dir, size=' . $s . ')' . ($s <= 100000 ? '!!!!' : '') . PHP_EOL;

        foreach ($this->children as /** @var $child Tree */ $child) {
            $childOut = explode(PHP_EOL, rtrim($child->print(), PHP_EOL));
            $out .= '  ' . join(PHP_EOL . '  ', $childOut) . PHP_EOL;
        }

        return $out;
    }

    public function getSize(): int
    {
        $size = 0;
        foreach ($this->children as $child) {
            if ($child instanceof Sizeable) {
                $size += $child->getSize();
            }
        }
        return $this->size = $size;
    }
}

class File extends Tree implements Sizeable
{
    public function print(): string
    {
        return '- ' . $this->name . ' (file, size=' . $this->getSize() . ')';
    }

    public function getSize(): int
    {
        return (int)$this->data;
    }
}

$root = new Dir('/', null);
$cwd = $root;
$i = 0;

while (isset($lines[$i])) {
    $line = $lines[$i];

    if (preg_match('#^\$ cd (.+)$#', $line, $match)) {
        $args = explode(' ', $match[1]);

        if (empty($args)) {
            throw new \Exception('cd without argument at line ' . $i . '!');
        }

        $dir = $args[0];

        if ($dir == '/') {
            $new_wd = $root;
        } elseif ($dir == '..') {
            $new_wd = $cwd->parent;
        } else {
            $new_wd = $cwd->getChild($dir);
        }

        if (!$new_wd) {
            throw new \Exception('try to cd to nonexistent dir at line ' . $i . '!');
        }

        $cwd = $new_wd;
        $i += 1;
    }

    if (preg_match('#^\$ ls$#', $line, $match)) {
        $i += 1;

        if (!isset($lines[$i])) {
            break;
        }

        $line = $lines[$i];

        while (!preg_match('#^\$#', $line)) {
            if (preg_match('#^dir (.+)$#', $line, $match)) {
                $cwd->addChild(new Dir($match[1], null));
            }
            if (preg_match('#^(\d+) (.+)$#', $line, $match)) {
                $cwd->addChild(new File($match[2], (int)$match[1]));
            }

            $i += 1;

            if (!isset($lines[$i])) {
                break;
            }

            $line = $lines[$i];
        }
    }
}

//echo $root->print();
//echo 'root size: ', $root->getSize(), PHP_EOL;

// part 1: find dirs with size at most 10000
$dir_sizes = [];
$root->traverseDF(function (Tree $node) use (&$dir_sizes) {
    if ($node instanceof Dir) {
        $size = $node->getSize();

        if ($size <= 100000) {
            $dir_sizes[$node->path()] = $size;
        }
    }
});

$sum = 0;
foreach ($dir_sizes as $dir => $size) {
    $sum += $size;
}
echo "part 1: ", $sum, PHP_EOL;

// part 2: find directory to delete to reach required free space
$total = 70000000;
$free_required = 30000000;
$free = $total - $root->getSize();
$needed = $free_required - $free;

if ($needed <= 0) {
    echo "part 2: enough space free!", PHP_EOL;
    return;
}

// find smallest directory that is size >= $needed
$min_size = PHP_INT_MAX;
$min_dir = null;

$root->traverseDF(function (Tree $node) use ($needed, &$min_size, &$min_dir) {
    if (!($node instanceof Dir)) {
        return;
    }
    $size = $node->getSize();
    if ($size > $needed && $size < $min_size) {
        $min_dir = $node;
        $min_size = $size;
    }
});

if (!$min_dir) {
    echo "part 2: no such directory found :|", PHP_EOL;
    return;
}

echo "part 2: ", $min_dir->path(), " size ", $min_dir->getSize(), PHP_EOL;