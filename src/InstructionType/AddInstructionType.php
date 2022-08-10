<?php

namespace RebelCode\Mantle\InstructionType;

use FilesystemIterator;
use InvalidArgumentException;
use RebelCode\Mantle\InstructionType;
use RebelCode\Mantle\Project;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

/** Instructions that add files to the build. */
class AddInstructionType implements InstructionType
{
    /** @inheritDoc */
    public function run(Project\Build $build, array $args): void
    {
        if (count($args) === 0) {
            throw new InvalidArgumentException('Missing argument for "ADD" instruction');
        }

        $project = $build->getProject();

        foreach ($args as $file) {
            $srcFile = $project->getPath() . '/' . $file;
            $destFile = $project->getConfig()->tempDir . '/' . $file;

            if (!file_exists($srcFile)) {
                throw new RuntimeException("File $srcFile does not exist");
            } elseif (!is_readable($srcFile)) {
                throw new RuntimeException("File $srcFile is not readable");
            }

            if (is_dir($srcFile)) {
                $this->copyDir($srcFile, $destFile);
            } else {
                $this->copyFile($srcFile, $destFile);
            }

            $project->getIo()->writeInstruction('green', 'Copied %s -> %s', $srcFile, $destFile);
        }
    }

    protected function copyFile(string $srcFile, string $destFile): void
    {
        $destDir = dirname($destFile);
        if (!file_exists($destDir)) {
            mkdir($destDir, 0777, true);
        }

        copy($srcFile, $destFile);
    }

    protected function copyDir(string $srcPath, string $destPath): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($srcPath, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            if ($fileInfo instanceof SplFileInfo) {
                $filePath = $fileInfo->getPath() . DIRECTORY_SEPARATOR . $fileInfo->getFilename();

                if (strpos($filePath, $srcPath) === 0) {
                    $relPath = substr($filePath, strlen($srcPath));

                    $this->copyFile($filePath, $destPath . $relPath);
                }
            }
        }
    }
}
