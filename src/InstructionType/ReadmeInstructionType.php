<?php

namespace RebelCode\Mantle\InstructionType;

use RebelCode\Mantle\InstructionType;
use RebelCode\Mantle\Project;

/** Internal instruction type to generate a readme.txt file for plugins that are hosted on wordpress.org */
class ReadmeInstructionType implements InstructionType
{
    public function run(Project\Build $build, array $args): void
    {
        $project = $build->getProject();

        $outPath = $project->getConfig()->tempDir . '/readme.txt';
        $readme = Project\Readme::fromFilesInDir($project->getReadmeDirPath());

        file_put_contents($outPath, $readme->render($project));
    }
}
