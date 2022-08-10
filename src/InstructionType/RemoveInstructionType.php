<?php

namespace RebelCode\Mantle\InstructionType;

use InvalidArgumentException;
use RebelCode\Mantle\InstructionType;
use RebelCode\Mantle\Project;
use RebelCode\Mantle\Utils;
use RuntimeException;

/** Instructions that remove files from the build. */
class RemoveInstructionType implements InstructionType
{
    /** @inheritDoc */
    public function run(Project\Build $build, array $args): void
    {
        if (count($args) === 0) {
            throw new InvalidArgumentException('Missing argument for "REMOVE" instruction');
        }

        $project = $build->getProject();

        foreach ($args as $file) {
            $fullPath = $project->getConfig()->tempDir . '/' . $file;

            if (!file_exists($fullPath)) {
                throw new RuntimeException("File $fullPath does not exist");
            } elseif (!is_readable($fullPath)) {
                throw new RuntimeException("File $fullPath is not readable");
            }

            if (is_dir($fullPath)) {
                Utils::rmDirRecursive($fullPath);
            } else {
                unlink($fullPath);
            }

            $project->getIo()->writeInstruction('bright-red', 'Removed %s', $fullPath);
        }
    }
}
