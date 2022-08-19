<?php

namespace RebelCode\Mantle\Svn;

class SvnStatus
{
    /** @var string */
    protected $path;
    /** @var SvnStatusEntry[] */
    protected $entries;

    /**
     * Constructor.
     *
     * @param string $path The path that the status entries relate to.
     * @param SvnStatusEntry[] $entries The status entries.
     */
    public function __construct(string $path, array $entries)
    {
        $this->path = $path;
        $this->entries = $entries;
    }

    /** Retrieves the path that the status entries relate to. */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Retrieves the status entries.
     *
     * @return SvnStatusEntry[]
     */
    public function getEntries(): array
    {
        return $this->entries;
    }

    /**
     * Retrieves the status entries for a given path.
     *
     * @param string $path The path to retrieve the status entries for.
     */
    public function getEntryForPath(string $path): ?SvnStatusEntry
    {
        foreach ($this->entries as $entry) {
            if ($entry->getPath() === $path) {
                return $entry;
            }
        }

        return null;
    }

    /** Retrieves the number of entries with a given status type. */
    public function getCount(?string $type = null): int
    {
        if ($type === null) {
            return count($this->entries);
        } else {
            $count = 0;
            foreach ($this->entries as $entry) {
                if ($entry->getType() === $type) {
                    $count++;
                }
            }
            return $count;
        }
    }

    /**
     * Creates an instance from the output of the `svn status` command.
     *
     * @param string $path The path that the `svn status` command was executed from.
     * @param string $output The output of the `svn status` command.
     */
    public static function fromSvnStatusOutput(string $path, string $output): self
    {
        $lines = array_map('trim', explode("\n", $output));
        $lines = array_filter($lines);

        $entries = array_map([SvnStatusEntry::class, 'fromSvnStatusLine'], $lines);

        return new self($path, $entries);
    }
}
