<?php

namespace RebelCode\Mantle;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use ZipArchive;

class Utils
{
    /** Constructs a path from a list of parts, sanitizing each part to prevent double separators. */
    public static function path(array $parts): string
    {
        foreach ($parts as $i => $part) {
            $parts[$i] = ltrim($part, '\\/');
        }

        return implode(DIRECTORY_SEPARATOR, array_filter($parts));
    }

    /**
     * Creates a directory if it does not exist yet.
     *
     * @return bool true if the directory was created, false if it already exists.
     */
    public static function ensurePathExists(string $path, int $permissions = 0777): bool
    {
        if (!file_exists($path)) {
            mkdir($path, $permissions, true);
            return true;
        } elseif (!is_dir($path)) {
            throw new RuntimeException("Path \"{$path}\" is not a directory");
        } else {
            return false;
        }
    }

    /** Copies a file and ensures that the destination directory exists. */
    public static function copyFile(string $srcFile, string $destFile): void
    {
        Utils::ensurePathExists(dirname($destFile));

        copy($srcFile, $destFile);
    }

    /** Copies a directory recursively. */
    public static function copyDirRecursive(string $srcPath, string $destPath): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($srcPath, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            if ($fileInfo instanceof SplFileInfo) {
                $filePath = $fileInfo->getPath() . DIRECTORY_SEPARATOR . $fileInfo->getFilename();

                if (strpos($filePath, $srcPath) === 0) {
                    $relPath = substr($filePath, strlen($srcPath));

                    static::copyFile($filePath, $destPath . $relPath);
                }
            }
        }
    }

    /** Removes a directory recursively. */
    public static function rmDirRecursive(string $path)
    {
        $dirIterator = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $fileInfo) {
            if ($fileInfo instanceof SplFileInfo) {
                if ($fileInfo->isDir() && !$fileInfo->isLink()) {
                    static::rmDirRecursive($fileInfo->getPathname());
                } else {
                    unlink($fileInfo->getPathname());
                }
            }
        }

        rmdir($path);
    }

    /** Creates a zip file for a directory. */
    public static function zipDirectory(string $filename, string $dirPath, string $pathPrefix = '')
    {
        $dirPath = rtrim($dirPath, '\\/');

        if (!extension_loaded('zip')) {
            throw new RuntimeException('Cannot build archive because the PHP "zip" extension is not loaded.');
        }

        $archive = new ZipArchive();
        $success = $archive->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if (!$success) {
            throw new RuntimeException('Failed to open stream for zip file: ' . $filename);
        }

        if (!file_exists($dirPath) || !is_dir($dirPath)) {
            return;
        }

        $dirIter = new RecursiveDirectoryIterator($dirPath, FilesystemIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($dirIter, RecursiveIteratorIterator::LEAVES_ONLY);

        foreach ($iterator as $file) {
            if (!$file instanceof SplFileInfo || $file->isDir()) {
                continue;
            }

            $filePath = $file->getPathname();
            $relPath = $pathPrefix . substr($filePath, strlen($dirPath . '/'));

            $archive->addFile($filePath, $relPath);
            $archive->setCompressionName($relPath, ZipArchive::CM_DEFLATE64, 9);
        }

        $archive->close();
    }
}
