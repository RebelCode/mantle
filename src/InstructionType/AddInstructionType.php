<?php

namespace RebelCode\Mantle\InstructionType;

use InvalidArgumentException;
use RebelCode\Mantle\InstructionType;
use RebelCode\Mantle\Project;
use RebelCode\Mantle\Utils;
use RuntimeException;

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
                Utils::copyDirRecursive($srcFile, $destFile);
            } else {
                Utils::copyFile($srcFile, $destFile);
            }

            $project->getIo()->writeInstruction('green', 'Copied %s -> %s', $srcFile, $destFile);
        }
    }
}
