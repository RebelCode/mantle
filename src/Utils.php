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
