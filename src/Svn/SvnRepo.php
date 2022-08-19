<?php

namespace RebelCode\Mantle\Svn;

use InvalidArgumentException;
use RebelCode\Mantle\Utils;
use RuntimeException;

class SvnRepo
{
    public const DEPTH_INFINITY = 'infinity';
    public const DEPTH_EMPTY = 'empty';
    public const DEPTH_IMMEDIATES = 'immediates';
    public const DEPTH_FILES = 'files';
    protected const DEPTH_VALUES = [
        self::DEPTH_EMPTY,
        self::DEPTH_FILES,
        self::DEPTH_IMMEDIATES,
        self::DEPTH_INFINITY,
    ];
    /** @var string */
    protected $directory;
    /** @var string */
    protected $url;

    /**
     * Constructor.
     *
     * @param string $directory The path to the local copy of the SVN repository.
     * @param string $url The remote URL of the SVN repository.
     */
    public function __construct(string $directory, string $url)
    {
        if ($directory === '.svn') {
            throw new InvalidArgumentException('Local SVN working copy cannot be named ".svn"');
        }

        $this->directory = $directory;
        $this->url = $url;
    }

    /** Retrieves the path to the local working copy of the repository. */
    public function getLocalDirectory(): string
    {
        return $this->directory;
    }

    /** Retrieves the remote URL of the repository. */
    public function getRemoteUrl(): string
    {
        return $this->url;
    }

    /** Gets the status of a path in the local working copy. */
    public function status(string $path = ''): SvnStatus
    {
        $output = $this->execute('status ' . $path);

        return SvnStatus::fromSvnStatusOutput($path, $output);
    }

    /** Deletes the local working copy. */
    public function clear()
    {
        if (is_dir($this->directory)) {
            Utils::rmDirRecursive($this->directory);
        }
    }

    /** Clones the repository into the local working copy. */
    public function checkout(string $path = '', string $depth = self::DEPTH_EMPTY)
    {
        if (!in_array($depth, static::DEPTH_VALUES)) {
            throw new InvalidArgumentException(
                'Invalid checkout depth; must be one of the following: ' . implode(', ', static::DEPTH_VALUES)
            );
        }

        $path = Utils::path([$path]);
        $path = $path ? '/' . $path : '';

        $this->execute("co --depth {$depth} {$this->url}{$path} {$this->directory}{$path}");
    }

    /** Updates the local copy with changes from the remote repository. */
    public function pull(string $path, string $depth = self::DEPTH_EMPTY)
    {
        if (!in_array($depth, static::DEPTH_VALUES)) {
            throw new InvalidArgumentException(
                'Invalid update depth; must be one of the following: ' . implode(', ', static::DEPTH_VALUES)
            );
        }

        $path = Utils::path([$path]);

        $this->execute("up --depth {$depth} {$path}");
    }

    /** Updates the versioning for a path, adding new files and removing deleted ones. */
    public function update(string $path = '')
    {
        $path = Utils::path([$path]);
        $status = $this->status($path);
        $pathStatus = $status->getEntryForPath($path);

        // Add new files
        if ($pathStatus !== null && $pathStatus->getType() === SvnStatusEntry::UNVERSIONED) {
            $this->execute('add --force * --depth infinity ' . $path);
        } else {
            $this->execute('add --force * --depth infinity', $path);
        }

        // Remove missing files
        foreach ($status->getEntries() as $entry) {
            if ($entry->getType() === SvnStatusEntry::MISSING) {
                $this->execute("rm {$entry->getPath()}", $path);
            }
        }
    }

    /** Commits the local changes to the repository. */
    public function commit(string $message)
    {
        $message = escapeshellarg($message);
        $this->execute("commit -m {$message} --force-interactive", '', true);
    }

    /** Creates a new tag from the trunk. */
    public function createTag(string $name, string $message)
    {
        $created = Utils::ensurePathExists($this->directory . '/tags');
        if ($created) {
            $this->execute('add tags');
        }

        $name = escapeshellarg($name);
        $message = escapeshellarg($message);
        $this->execute("copy ^/trunk ^/tags/{$name} -m {$message}");
    }

    /** Gets the version of the local SVN client. */
    public function getClientVersion(): ?string
    {
        $output = $this->execute('--version');
        $lines = explode("\n", trim($output));

        foreach ($lines as $line) {
            if (preg_match('/^svn, version ([\d.]+)/', $line, $matches) && count($matches) > 1) {
                return $matches[1];
            }
        }

        return null;
    }

    /** Executes an SVN command. */
    protected function execute(string $command, string $relPath = '', bool $passThru = false): string
    {
        $fullCommand = "svn $command";

        $output = '';
        $error = '';
        $code = 1;

        if ($passThru) {
            // Change current directory to the local working copy
            $cwd = getcwd();
            chdir($this->directory);
            // Run the command in pass-through mode
            $result = passthru($fullCommand, $code);
            // Change current directory back to what it was before
            chdir($cwd);

            if ($result === false) {
                throw new RuntimeException('Could not run "svn" command. Is SVN installed?');
            }
        } else {
            $proc = proc_open($fullCommand, [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ], $pipes, Utils::path([$this->directory, $relPath]));

            if (is_resource($proc)) {
                $output = stream_get_contents($pipes[1]);
                $error = stream_get_contents($pipes[2]);
                array_map('fclose', $pipes);

                $code = proc_close($proc);
            } else {
                throw new RuntimeException('Could not run "svn" command. Is SVN installed?');
            }
        }

        if ($code === 0) {
            return $output;
        } else {
            $message = 'SVN exited with code ' . $code;

            if (strlen(trim($error)) > 0) {
                $message .= ' and the following error: ' . $error;
            }

            throw new RuntimeException($message);
        }
    }
}
