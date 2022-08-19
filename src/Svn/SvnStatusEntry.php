<?php

namespace RebelCode\Mantle\Svn;

use InvalidArgumentException;

class SvnStatusEntry
{
    public const NONE = ' ';
    public const ADDED = 'A';
    public const DELETED = 'D';
    public const MODIFIED = 'M';
    public const REPLACED = 'R';
    public const CONFLICT = 'C';
    public const EXTERNAL = 'X';
    public const IGNORED = 'I';
    public const MISSING = '!';
    public const UNVERSIONED = '?';
    public const REPLACED_OBJECT = '~';
    /** @var string */
    protected $type;
    /** @var string */
    protected $path;

    /**
     * Constructor.
     *
     * @param string $type The status type of the file.
     * @param string $path The path to the file.
     */
    public function __construct(string $type, string $path)
    {
        $this->type = $type;
        $this->path = $path;
    }

    /** Retrieves the status type of the file. */
    public function getType(): string
    {
        return $this->type;
    }

    /** Retrieves the path to the file. */
    public function getPath(): string
    {
        return $this->path;
    }

    /** Creates an instance from a status line, as returned from the output of the `svn status` command. */
    public static function fromSvnStatusLine(string $line): self
    {
        $line = trim($line);

        if (strlen($line) === 0) {
            throw new InvalidArgumentException('Status line string cannot be empty');
        }

        $type = $line[0];
        $path = trim(substr($line, 1));

        return new static($type, $path);
    }
}
